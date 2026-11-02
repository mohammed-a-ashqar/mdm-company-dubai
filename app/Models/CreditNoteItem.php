<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CreditNoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'credit_note_id',
        'product_id',
        'item_name',
        'quantity',
        'free_items',
        'unit_price',
        'averaged_unit_price',
        'vat_percent',
        'company_discount_percent',
        'line_note',
    ];

    public function creditNote(): BelongsTo
    {
        return $this->belongsTo(CreditNote::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** True when free units are priced too, at the line's paid value averaged over paid + free units. */
    public function isAveraged(): bool
    {
        return $this->averaged_unit_price !== null;
    }

    public function displayUnitPrice(): float
    {
        return (float) ($this->isAveraged() ? $this->averaged_unit_price : $this->unit_price);
    }

    /** Units that carry a price: paid only, or paid + free on an averaged line. */
    public function pricedUnits(): int
    {
        return (int) $this->quantity + ($this->isAveraged() ? (int) $this->free_items : 0);
    }

    /** Line amount before discount and VAT (positive magnitude). */
    public function grossAmount(): float
    {
        return $this->displayUnitPrice() * $this->pricedUnits();
    }

    /** Line total before VAT, after company discount (positive magnitude). */
    public function priceAfterDiscount(): float
    {
        $gross = $this->grossAmount();

        return (float) ($gross - $gross * ($this->company_discount_percent / 100));
    }

    /** VAT amount on discounted line (positive magnitude). */
    public function vatAmount(): float
    {
        return (float) ($this->priceAfterDiscount() * ($this->vat_percent / 100));
    }

    /** Line total incl. VAT (positive magnitude). */
    public function lineTotal(): float
    {
        return $this->priceAfterDiscount() + $this->vatAmount();
    }
}
