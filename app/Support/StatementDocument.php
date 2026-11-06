<?php

namespace App\Support;

use App\Models\Company;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Carbon\Carbon;

/**
 * Statement of accounts for one company. The user picks which invoices of the period
 * go on the statement and marks each one paid or unpaid by hand; the invoice's own
 * status column is deliberately ignored. Totals are computed exactly like the printed
 * tax invoice (InvoiceDocument).
 */
class StatementDocument
{
    /**
     * @param  array<int,string>|null  $selection  invoice id => "paid"|"unpaid" for the invoices to include;
     *                                             null = include every invoice of the period as unpaid
     */
    public static function make(Company $company, ?string $from = null, ?string $to = null, ?array $selection = null): array
    {
        $to = $to ? Carbon::parse($to)->endOfDay() : now()->endOfDay();
        $from = $from ? Carbon::parse($from)->startOfDay() : null;

        $invoices = Invoice::with('company')
            ->where('company_id', $company->id)
            ->when($from, fn ($q) => $q->where('date_of_create', '>=', $from))
            ->where('date_of_create', '<=', $to)
            ->orderBy('date_of_create')
            ->orderBy('id')
            ->get();

        $service = app(InvoiceService::class);
        $empty = ['total' => 0.0, 'vat' => 0.0, 'net' => 0.0];
        $sums = ['overall' => $empty, 'paid' => $empty, 'unpaid' => $empty];
        $rows = [];
        $candidates = [];

        foreach ($invoices as $invoice) {
            $doc = InvoiceDocument::make($invoice, $service->show($invoice->id));
            $included = $selection === null || array_key_exists($invoice->id, $selection);
            $status = ($selection[$invoice->id] ?? 'unpaid') === 'paid' ? 'paid' : 'unpaid';
            $amounts = [
                'total' => round($doc['totals']['total'], 2),
                'vat' => round($doc['totals']['vat'], 2),
                'net' => round($doc['totals']['taxable'], 2),
            ];

            $row = [
                'id' => $invoice->id,
                'number' => (string) $invoice->invoice_number,
                'date' => Carbon::parse($invoice->date_of_create),
                'status' => $status,
                'included' => $included,
            ] + $amounts;
            $candidates[] = $row;

            if (! $included) {
                continue;
            }
            $rows[] = $row;

            foreach (['overall', $status] as $bucket) {
                foreach ($amounts as $key => $value) {
                    $sums[$bucket][$key] += $value;
                }
            }
        }

        // Period starts at the first invoice on the statement when no start date was chosen.
        $first = $rows[0]['date'] ?? $candidates[0]['date'] ?? $to;
        $from ??= $first->copy()->startOfDay();

        return [
            'seller' => config('invoice.seller'),
            'statement' => config('invoice.statement'),
            'currency' => config('invoice.currency', 'AED'),
            'logo' => self::logo(),
            'from' => $from,
            'to' => $to,
            'customer' => [
                'name' => $company->name,
                'address' => $company->address,
                'trn' => $company->trn,
                'phone' => $company->phone,
            ],
            'rows' => $rows,
            'candidates' => $candidates,
            'sums' => $sums,
        ];
    }

    public static function money(float $amount): string
    {
        return number_format($amount, 2);
    }

    private static function logo(): ?string
    {
        $path = public_path('assets/images/mdm.png');

        return is_file($path) ? 'data:image/png;base64,'.base64_encode(file_get_contents($path)) : null;
    }
}
