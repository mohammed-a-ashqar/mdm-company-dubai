<?php

namespace App\Http\Requests;

use App\Models\Quotation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'quotation_date' => ['required', 'date'],
            'visible_columns' => ['nullable', 'array'],
            'visible_columns.*' => [Rule::in(array_keys(Quotation::STANDARD_COLUMNS))],
            'custom_columns' => ['nullable', 'array', 'max:8'],
            'custom_columns.*.key' => ['required', 'string', 'regex:/^c[a-z0-9]{1,20}$/', 'distinct'],
            'custom_columns.*.label' => ['required', 'string', 'max:60'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.price' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'items.*.vat_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.discount' => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'items.*.bonus' => ['required', 'integer', 'min:0', 'max:1000000'],
            'items.*.custom' => ['nullable', 'array'],
            'items.*.custom.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'custom_columns.*.label' => 'column name',
            'items.*.name' => 'item description',
            'items.*.price' => 'price',
            'items.*.vat_rate' => 'VAT',
            'items.*.discount' => 'discount',
            'items.*.quantity' => 'quantity',
            'items.*.bonus' => 'bonus',
        ];
    }
}
