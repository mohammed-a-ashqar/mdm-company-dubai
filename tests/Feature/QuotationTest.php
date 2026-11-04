<?php

namespace Tests\Feature;

use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class QuotationTest extends TestCase
{
    // phpunit.xml points at the real database, so never use RefreshDatabase here.
    use DatabaseTransactions;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'SkinMed Medical Center L.L.C',
            'quotation_date' => '2026-11-04',
            'visible_columns' => array_keys(Quotation::STANDARD_COLUMNS),
            'custom_columns' => [['key' => 'cexpiry', 'label' => 'Expiry']],
            'items' => [
                ['name' => 'Oge Bright Plus Cream (30mL)', 'price' => 650, 'vat_rate' => 5, 'discount' => 30, 'quantity' => 3, 'bonus' => 1, 'custom' => ['cexpiry' => '12/2028', 'cforged' => 'x']],
                ['name' => 'Oge Tone Cream', 'price' => 500, 'vat_rate' => 5, 'discount' => 30, 'quantity' => 3, 'bonus' => 1],
            ],
        ], $overrides);
    }

    public function test_quotation_is_stored_with_correct_amounts_and_custom_column(): void
    {
        $this->actingAs(User::factory()->create())->post(route('quotation.store'), $this->payload())->assertRedirect();

        $quotation = Quotation::with('items')->latest('id')->firstOrFail();
        $columns = collect($quotation->printedColumns())->keyBy('key');
        $bright = $quotation->items[0];
        $tone = $quotation->items[1];

        $this->assertSame('32.5', $bright->cell($columns['vat']));
        $this->assertSame('30%', $bright->cell($columns['discount']));
        $this->assertSame('455', $bright->cell($columns['price_after']));
        $this->assertSame('22.75', $bright->cell($columns['vat_after']));
        $this->assertSame('4', $bright->cell($columns['total_qty']));
        $this->assertSame('1,365', $bright->cell($columns['total']));
        $this->assertSame('1,433.25', $bright->cell($columns['total_vat']));
        $this->assertSame('1,102.5', $tone->cell($columns['total_vat']));

        $this->assertSame('12/2028', $bright->cell($columns['cexpiry']));
        $this->assertArrayNotHasKey('cforged', $bright->custom_values);

        // The added column prints right before the totals.
        $keys = array_column($quotation->printedColumns(), 'key');
        $this->assertSame(['total_qty', 'cexpiry', 'total', 'total_vat'], array_slice($keys, -4));
    }

    public function test_hidden_columns_are_not_printed(): void
    {
        $this->actingAs(User::factory()->create())->post(route('quotation.store'), $this->payload([
            'visible_columns' => ['price', 'quantity', 'total'],
            'custom_columns' => [],
        ]))->assertRedirect();

        $quotation = Quotation::latest('id')->firstOrFail();

        $this->assertSame(['name', 'price', 'quantity', 'total'], array_column($quotation->printedColumns(), 'key'));
    }

    public function test_pages_and_pdf_render_including_arabic_names(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('quotation.store'), $this->payload(['customer_name' => 'شركة الاختبار']))->assertRedirect();
        $id = Quotation::latest('id')->value('id');

        $this->actingAs($user)->get(route('quotation.index'))->assertOk();
        $this->actingAs($user)->get(route('quotation.create'))->assertOk();
        $this->actingAs($user)->get(route('quotation.edit', $id))->assertOk();
        $this->actingAs($user)->get(route('quotation.show', $id))->assertOk()->assertSee('Expiry');

        $pdf = $this->actingAs($user)->get(route('quotation.download', $id))->assertOk();
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
        $this->assertStringContainsString('attachment', $pdf->headers->get('Content-Disposition'));
    }

    public function test_update_replaces_items_and_guests_are_redirected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('quotation.store'), $this->payload())->assertRedirect();
        $quotation = Quotation::latest('id')->firstOrFail();

        $payload = $this->payload();
        unset($payload['items'][1]);
        $this->actingAs($user)->post(route('quotation.update', $quotation->id), $payload)->assertRedirect();
        $this->assertCount(1, $quotation->fresh()->items);

        auth()->logout();
        $this->get(route('quotation.index'))->assertRedirect(route('login'));
    }

    public function test_invalid_input_is_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('quotation.store'), $this->payload(['customer_name' => '', 'items' => []]))
            ->assertSessionHasErrors(['customer_name', 'items']);
    }
}
