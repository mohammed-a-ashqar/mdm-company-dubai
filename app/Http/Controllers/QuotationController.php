<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuotationRequest;
use App\Models\Company;
use App\Models\Quotation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\HeaderUtils;

class QuotationController extends Controller
{
    public function index()
    {
        return view('quotation.index');
    }

    public function create()
    {
        return view('quotation.create', $this->formData());
    }

    public function store(QuotationRequest $request)
    {
        $quotation = DB::transaction(function () use ($request) {
            $quotation = new Quotation();
            $this->persist($quotation, $request);

            return $quotation;
        });

        return redirect()->route('quotation.show', $quotation->id)->with('success', 'Quotation created successfully.');
    }

    public function show(int $id)
    {
        $quotation = Quotation::with('items')->findOrFail($id);

        return view('quotation.show', compact('quotation'));
    }

    public function edit(int $id)
    {
        $quotation = Quotation::with('items')->findOrFail($id);

        return view('quotation.edit', $this->formData() + compact('quotation'));
    }

    public function update(QuotationRequest $request, int $id)
    {
        $quotation = Quotation::findOrFail($id);

        DB::transaction(fn () => $this->persist($quotation, $request));

        return redirect()->route('quotation.show', $quotation->id)->with('success', 'Quotation updated successfully.');
    }

    public function destroy(int $id)
    {
        Quotation::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Quotation deleted successfully.');
    }

    public function pdf(int $id)
    {
        return $this->pdfResponse($id, HeaderUtils::DISPOSITION_INLINE);
    }

    public function download(int $id)
    {
        return $this->pdfResponse($id, HeaderUtils::DISPOSITION_ATTACHMENT);
    }

    private function formData(): array
    {
        // Each customer's newest quotation decides the columns its next quotation starts with.
        $columnMemory = [];
        foreach (Quotation::orderByDesc('id')->get(['customer_name', 'hidden_columns', 'custom_columns']) as $previous) {
            $columnMemory[mb_strtolower(trim($previous->customer_name))] ??= [
                'hidden' => $previous->hidden_columns ?? [],
                'custom' => $previous->custom_columns ?? [],
            ];
        }

        return [
            'companies' => Company::orderBy('name')->get(['id', 'name', 'discount']),
            'columnMemory' => $columnMemory,
        ];
    }

    private function persist(Quotation $quotation, QuotationRequest $request): void
    {
        $data = $request->validated();
        $customColumns = array_values($data['custom_columns'] ?? []);
        $customKeys = array_column($customColumns, 'key');

        $quotation->fill([
            'company_id' => $data['company_id'] ?? null,
            'customer_name' => $data['customer_name'],
            'quotation_date' => $data['quotation_date'],
            'hidden_columns' => array_values(array_diff(array_keys(Quotation::STANDARD_COLUMNS), $data['visible_columns'] ?? [])),
            'custom_columns' => $customColumns,
        ])->save();

        $quotation->items()->delete();

        foreach (array_values($data['items']) as $index => $item) {
            $quotation->items()->create([
                'sort_order' => $index,
                'name' => $item['name'],
                'price' => $item['price'],
                'vat_rate' => $item['vat_rate'],
                'discount' => $item['discount'],
                'quantity' => $item['quantity'],
                'bonus' => $item['bonus'],
                'custom_values' => array_intersect_key($item['custom'] ?? [], array_flip($customKeys)),
            ]);
        }
    }

    private function pdfResponse(int $id, string $disposition)
    {
        $quotation = Quotation::with('items')->findOrFail($id);

        try {
            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $fontData = (new \Mpdf\Config\FontVariables())->getDefaults()['fontdata'];

            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 6,
                'margin_right' => 6,
                'margin_top' => 52,
                'margin_bottom' => 56,
                'autoScriptToLang' => true,
                'autoLangToFont' => true,
                'fontDir' => array_merge($defaultConfig['fontDir'], [storage_path('fonts')]),
                'fontdata' => $fontData + [
                    'roboto' => [
                        'R' => 'Roboto-Regular.ttf',
                        'B' => 'Roboto-Bold.ttf',
                    ],
                ],
                'default_font' => 'roboto',
                'tempDir' => storage_path('app/mpdf'),
            ]);
            $mpdf->showImageErrors = false;
            $mpdf->shrink_tables_to_fit = 1;
            $mpdf->SetDefaultBodyCSS('background', "url('" . str_replace('\\', '/', public_path('assets/images/quotation-letterhead.png')) . "')");
            $mpdf->SetDefaultBodyCSS('background-image-resize', 6);
            $mpdf->WriteHTML(view('quotation.download', compact('quotation'))->render());
            $content = $mpdf->Output('', 'S');
        } catch (\Mpdf\MpdfException $e) {
            Log::error('mPDF Quotation Error: ' . $e->getMessage());

            return response()->json(['error' => 'PDF generation failed.'], 500);
        }

        $filename = trim(preg_replace('/[\\\\\/:*?"<>|\r\n]+/', ' ', $quotation->customer_name)) . ' (Quotation).pdf';

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => HeaderUtils::makeDisposition($disposition, $filename, 'quotation-' . $quotation->id . '.pdf'),
        ]);
    }
}
