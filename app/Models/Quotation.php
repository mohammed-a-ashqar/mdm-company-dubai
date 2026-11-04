<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    /** Standard columns that can be shown or hidden, in print order. */
    public const STANDARD_COLUMNS = [
        'price' => 'Original Price',
        'vat' => 'vat',
        'discount' => 'Discount',
        'price_after' => 'Price After Discount',
        'vat_after' => 'vat',
        'quantity' => 'Qty',
        'bonus' => 'Bonus',
        'total_qty' => 'Total Qty',
        'total' => 'Total Price with discount',
        'total_vat' => 'Total Price with discount and vat',
    ];

    /** Custom columns print just before these. */
    private const TOTAL_COLUMNS = ['total', 'total_vat'];

    protected $fillable = ['company_id', 'customer_name', 'quotation_date', 'hidden_columns', 'custom_columns'];

    protected $casts = [
        'quotation_date' => 'date',
        'hidden_columns' => 'array',
        'custom_columns' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order')->orderBy('id');
    }

    /** @return array<int, array{key: string, label: string, custom: bool}> */
    public function printedColumns(): array
    {
        $hidden = $this->hidden_columns ?? [];
        $columns = [['key' => 'name', 'label' => 'Items Description', 'custom' => false]];
        $customAdded = false;

        $addCustom = function () use (&$columns, &$customAdded) {
            if ($customAdded) {
                return;
            }
            foreach ($this->custom_columns ?? [] as $column) {
                $columns[] = ['key' => $column['key'], 'label' => $column['label'], 'custom' => true];
            }
            $customAdded = true;
        };

        foreach (self::STANDARD_COLUMNS as $key => $label) {
            if (in_array($key, self::TOTAL_COLUMNS, true)) {
                $addCustom();
            }
            if (! in_array($key, $hidden, true)) {
                $columns[] = ['key' => $key, 'label' => $label, 'custom' => false];
            }
        }

        return $columns;
    }
}
