<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    protected $fillable = ['sort_order', 'name', 'price', 'vat_rate', 'discount', 'quantity', 'bonus', 'custom_values'];

    protected $casts = [
        'custom_values' => 'array',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    /** Printed text for a column; amounts follow the quotation's look: 3,150 / 3,307.5 / 15.75. */
    public function cell(array $column): string
    {
        if ($column['custom']) {
            return (string) ($this->custom_values[$column['key']] ?? '');
        }

        $price = (float) $this->price;
        $rate = (float) $this->vat_rate / 100;
        $after = $price * (1 - (float) $this->discount / 100);
        $total = $after * (int) $this->quantity;

        return match ($column['key']) {
            'name' => $this->name,
            'price' => self::amount($price),
            'vat' => self::amount($price * $rate),
            'discount' => self::amount((float) $this->discount) . '%',
            'price_after' => self::amount($after),
            'vat_after' => self::amount($after * $rate),
            'quantity' => (string) $this->quantity,
            'bonus' => (string) $this->bonus,
            'total_qty' => (string) ($this->quantity + $this->bonus),
            'total' => self::amount($total),
            'total_vat' => self::amount($total * (1 + $rate)),
        };
    }

    private static function amount(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2), '0'), '.');
    }
}
