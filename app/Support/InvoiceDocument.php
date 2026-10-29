<?php

namespace App\Support;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Everything a printed tax invoice needs, computed once.
 * Line maths mirrors the existing invoice (discount on gross, VAT on the discounted amount),
 * so totals are identical to the old layout.
 */
class InvoiceDocument
{
    public static function make(Invoice $invoice, Collection $products): array
    {
        $company = $invoice->company;
        $companyDisc = (float) ($company->discount ?? 0);
        $currency = config('invoice.currency', 'AED');

        $lines = [];
        $totals = ['gross' => 0.0, 'discount' => 0.0, 'taxable' => 0.0, 'vat' => 0.0, 'total' => 0.0, 'qty' => 0, 'free' => 0];
        $byRate = [];

        foreach ($products->values() as $i => $p) {
            $qty = (int) $p->quantity;
            $free = (int) ($p->free_items ?? 0);
            $gross = (float) $p->price * $qty;
            $discPct = $p->lineDiscountPercent($companyDisc);
            $taxable = $p->linePriceAfterDiscount($companyDisc);
            $discount = $gross - $taxable;
            $rate = (float) $p->vat;
            $vat = $taxable * $rate / 100;
            $total = $taxable + $vat;

            $lines[] = [
                'no' => $i + 1,
                'name' => $p->name,
                'qty' => $qty,
                'free' => $free,
                'unit' => (float) $p->price,
                'gross' => $gross,
                'disc_pct' => $discPct,
                'discount' => $discount,
                'taxable' => $taxable,
                'vat_rate' => $rate,
                'vat' => $vat,
                'total' => $total,
            ];

            $totals['gross'] += $gross;
            $totals['discount'] += $discount;
            $totals['taxable'] += $taxable;
            $totals['vat'] += $vat;
            $totals['total'] += $total;
            $totals['qty'] += $qty;
            $totals['free'] += $free;

            $key = rtrim(rtrim(number_format($rate, 2, '.', ''), '0'), '.');
            $byRate[$key] = ($byRate[$key] ?? 0) + $taxable;
        }
        ksort($byRate, SORT_NUMERIC);

        $date = self::date($invoice->date_of_create);

        return [
            'seller' => config('invoice.seller'),
            'terms' => config('invoice.terms'),
            'currency' => $currency,
            'logo' => self::logo(),
            'number' => (string) $invoice->invoice_number,
            'barcode' => Code128::dataUri((string) $invoice->invoice_number, 38, 1.25),
            'date' => $date,
            'period_from' => self::date($invoice->from),
            'period_to' => self::date($invoice->to),
            'status' => strtolower((string) $invoice->status),
            'customer' => [
                'name' => $company->name,
                'trn' => $company->trn,
                'address' => $company->address,
                'phone' => $company->phone,
                'code' => 'CUST-'.str_pad((string) $company->id, 4, '0', STR_PAD_LEFT),
                'discount' => $companyDisc,
            ],
            'lines' => $lines,
            'totals' => $totals,
            'by_rate' => $byRate,
            'words' => self::words($totals['total'], $currency),
        ];
    }

    private static function date($value): ?Carbon
    {
        return $value ? Carbon::parse($value) : null;
    }

    private static function logo(): ?string
    {
        $path = public_path('assets/images/mdm.png');

        return is_file($path) ? 'data:image/png;base64,'.base64_encode(file_get_contents($path)) : null;
    }

    /** 1234.5 → "UAE Dirhams One Thousand Two Hundred Thirty-Four and Fils Fifty Only" */
    public static function words(float $amount, string $currency = 'AED'): string
    {
        $amount = round($amount, 2);
        $whole = (int) floor($amount);
        $fils = (int) round(($amount - $whole) * 100);
        $name = $currency === 'AED' ? 'UAE Dirhams' : $currency;

        $text = $name.' '.self::spell($whole);
        if ($fils > 0) {
            $text .= ' and Fils '.self::spell($fils);
        }

        return $text.' Only';
    }

    private static function spell(int $n): string
    {
        $ones = ['Zero', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve',
            'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        if ($n < 20) {
            return $ones[$n];
        }
        if ($n < 100) {
            return $tens[intdiv($n, 10)].($n % 10 ? '-'.$ones[$n % 10] : '');
        }
        if ($n < 1000) {
            return $ones[intdiv($n, 100)].' Hundred'.($n % 100 ? ' '.self::spell($n % 100) : '');
        }
        foreach ([1000000000 => 'Billion', 1000000 => 'Million', 1000 => 'Thousand'] as $value => $label) {
            if ($n >= $value) {
                return self::spell(intdiv($n, $value)).' '.$label.($n % $value ? ' '.self::spell($n % $value) : '');
            }
        }

        return (string) $n;
    }
}
