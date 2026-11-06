<?php

namespace App\Support;

use App\Models\Company;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Quotation;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Real figures for the home dashboard. Invoice totals use the same line maths as the
 * printed tax invoice (InvoiceDocument): discount on gross, VAT on the discounted amount,
 * products of the company dated inside the invoice period.
 * Paid/unpaid is left out on purpose: the invoice status column is not reliable.
 */
class DashboardData
{
    public static function make(): array
    {
        $now = now();
        $invoices = self::invoiceTotals();
        $credits = self::creditNoteTotals();

        $inMonth = fn (Collection $c, Carbon $m) => $c->filter(fn ($r) => $r['date']->isSameMonth($m));
        $inYear = fn (Collection $c, int $y) => $c->filter(fn ($r) => $r['date']->year === $y);

        $thisMonth = $inMonth($invoices, $now);
        $lastMonth = $inMonth($invoices, $now->copy()->subMonthNoOverflow());
        $thisYear = $inYear($invoices, $now->year);
        $creditsYear = $inYear($credits, $now->year);

        $months = collect(range(11, 0))->map(function ($back) use ($now, $invoices, $credits, $inMonth) {
            $m = $now->copy()->startOfMonth()->subMonthsNoOverflow($back);
            $inv = $inMonth($invoices, $m);

            return [
                'label' => $m->format('M Y'),
                'net' => round($inv->sum('net'), 2),
                'vat' => round($inv->sum('vat'), 2),
                'credits' => round($inMonth($credits, $m)->sum('total'), 2),
                'count' => $inv->count(),
            ];
        })->values();

        $topCompanies = $thisYear->groupBy('company')
            ->map(fn ($rows, $name) => ['name' => $name, 'total' => $rows->sum('total'), 'count' => $rows->count()])
            ->sortByDesc('total')->take(6)->values();

        $monthTotal = $thisMonth->sum('total');
        $lastTotal = $lastMonth->sum('total');

        return [
            'currency' => config('invoice.currency', 'AED'),
            'now' => $now,
            'month' => [
                'total' => $monthTotal,
                'count' => $thisMonth->count(),
                'change' => $lastTotal > 0 ? ($monthTotal - $lastTotal) / $lastTotal * 100 : null,
                'last_total' => $lastTotal,
            ],
            'year' => [
                'total' => $thisYear->sum('total'),
                'net' => $thisYear->sum('net'),
                'vat' => $thisYear->sum('vat'),
                'count' => $thisYear->count(),
                'credits' => $creditsYear->sum('total'),
                'credits_count' => $creditsYear->count(),
                'after_credits' => $thisYear->sum('total') - $creditsYear->sum('total'),
                'quotations' => Quotation::whereYear('quotation_date', $now->year)->count(),
            ],
            'companies' => Company::count(),
            'active_companies' => $thisYear->pluck('company')->unique()->count(),
            'months' => $months,
            'top_companies' => $topCompanies,
            'latest' => $invoices->sortByDesc(fn ($r) => $r['date']->timestamp * 100000 + $r['id'])->take(8)->values(),
        ];
    }

    /** One row per invoice: id, number, company, date, net, vat, total. */
    private static function invoiceTotals(): Collection
    {
        $invoices = Invoice::with('company')->get();
        $products = Product::whereIn('company_id', $invoices->pluck('company_id')->unique())->get()->groupBy('company_id');

        return $invoices->filter(fn ($i) => $i->company)->map(function (Invoice $invoice) use ($products) {
            $from = Carbon::parse($invoice->from);
            $to = Carbon::parse($invoice->to);
            $disc = (float) ($invoice->company->discount ?? 0);
            $net = 0.0;
            $vat = 0.0;

            foreach ($products->get($invoice->company_id, collect()) as $p) {
                $d = Carbon::parse($p->date_of_create);
                if ($d->lt($from) || $d->gt($to)) {
                    continue;
                }
                $taxable = $p->linePriceAfterDiscount($disc);
                $net += $taxable;
                $vat += $taxable * (float) $p->vat / 100;
            }

            return [
                'id' => $invoice->id,
                'number' => (string) $invoice->invoice_number,
                'company' => $invoice->company->name,
                'date' => Carbon::parse($invoice->date_of_create),
                'net' => round($net, 2),
                'vat' => round($vat, 2),
                'total' => round($net + $vat, 2),
            ];
        })->values();
    }

    /** One row per credit note: date and total incl. VAT (positive). */
    private static function creditNoteTotals(): Collection
    {
        return CreditNote::with('items')->get()->map(fn (CreditNote $n) => [
            'date' => Carbon::parse($n->note_date ?? $n->created_at),
            'total' => round($n->items->sum(fn ($i) => $i->lineTotal()), 2),
        ]);
    }
}
