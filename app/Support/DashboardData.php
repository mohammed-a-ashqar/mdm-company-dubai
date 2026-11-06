<?php

namespace App\Support;

use App\Models\Company;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\Product;
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
    public static function make(?int $year = null): array
    {
        $now = now();
        $invoices = self::invoiceTotals();
        $credits = self::creditNoteTotals();

        $years = $invoices->map(fn ($r) => $r['date']->year)->push($now->year)->unique()->sortDesc()->values();
        $year = $year && $years->contains($year) ? $year : $now->year;
        $isCurrent = $year === $now->year;

        $inYear = fn (Collection $c, int $y) => $c->filter(fn ($r) => $r['date']->year === $y);
        $inMonth = fn (Collection $c, Carbon $m) => $c->filter(fn ($r) => $r['date']->isSameMonth($m));

        $yearInv = $inYear($invoices, $year);
        $yearCred = $inYear($credits, $year);

        // Previous year over the same stretch (1 Jan → same day) while the year is still running.
        $prevEnd = $isCurrent ? $now->copy()->subYearNoOverflow()->endOfDay() : Carbon::create($year - 1, 12, 31)->endOfDay();
        $prevInv = $inYear($invoices, $year - 1)->filter(fn ($r) => $r['date']->lte($prevEnd));

        $months = collect(range(1, 12))->map(function ($n) use ($year, $yearInv, $yearCred, $inMonth, $now, $isCurrent) {
            $m = Carbon::create($year, $n, 1);
            $inv = $inMonth($yearInv, $m);
            $cred = $inMonth($yearCred, $m)->sum('total');
            $total = $inv->sum('total');

            return [
                'n' => $n,
                'label' => $m->format('M'),
                'name' => $m->format('F'),
                'count' => $inv->count(),
                'net' => round($inv->sum('net'), 2),
                'vat' => round($inv->sum('vat'), 2),
                'total' => round($total, 2),
                'credits' => round($cred, 2),
                'after' => round($total - $cred, 2),
                'future' => $isCurrent && $n > $now->month,
                'current' => $isCurrent && $n === $now->month,
            ];
        });

        $yearTotal = $yearInv->sum('total');
        $prevTotal = $prevInv->sum('total');

        $companies = $yearInv->groupBy('company')
            ->map(fn ($rows, $name) => ['name' => $name, 'total' => $rows->sum('total'), 'count' => $rows->count(), 'last' => $rows->max('date')])
            ->sortByDesc('total')->values();

        $thisMonth = $inMonth($invoices, $now);
        $lastMonth = $inMonth($invoices, $now->copy()->startOfMonth()->subMonthNoOverflow());

        return [
            'currency' => config('invoice.currency', 'AED'),
            'now' => $now,
            'year' => $year,
            'years' => $years,
            'is_current' => $isCurrent,
            'prev_label' => $isCurrent ? 'same period '.($year - 1) : (string) ($year - 1),
            'totals' => [
                'total' => $yearTotal,
                'net' => $yearInv->sum('net'),
                'vat' => $yearInv->sum('vat'),
                'count' => $yearInv->count(),
                'credits' => $yearCred->sum('total'),
                'credits_count' => $yearCred->count(),
                'after' => $yearTotal - $yearCred->sum('total'),
                'average' => $yearInv->count() ? $yearTotal / $yearInv->count() : 0,
                'prev' => $prevTotal,
                'change' => $prevTotal > 0 ? ($yearTotal - $prevTotal) / $prevTotal * 100 : null,
            ],
            'month' => [
                'name' => $now->format('F Y'),
                'total' => $thisMonth->sum('total'),
                'count' => $thisMonth->count(),
                'last' => $lastMonth->sum('total'),
                'last_name' => $now->copy()->startOfMonth()->subMonthNoOverflow()->format('F'),
                'companies' => $thisMonth->pluck('company')->unique()->count(),
            ],
            'months' => $months,
            'companies' => $companies->take(8)->values(),
            'companies_active' => $companies->count(),
            'companies_all' => Company::count(),
            'latest' => $invoices->sortByDesc(fn ($r) => $r['date']->timestamp * 100000 + $r['id'])->take(10)->values(),
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
