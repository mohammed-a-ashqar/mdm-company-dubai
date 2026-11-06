<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Support\StatementDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StatementController extends Controller
{
    public function index()
    {
        $companies = Company::orderBy('name')->get(['id', 'name']);

        return view('statement.index', compact('companies'));
    }

    public function show(Request $request, Company $company)
    {
        $range = $this->range($request);
        $doc = StatementDocument::make($company, ...$range);

        return view('statement.show', compact('doc', 'company', 'range'));
    }

    public function pdf(Request $request, Company $company)
    {
        return $this->pdfResponse($company, $this->range($request), 'inline');
    }

    public function download(Request $request, Company $company)
    {
        return $this->pdfResponse($company, $this->range($request), 'attachment');
    }

    /**
     * [from, to, selection]. selection is null until the user submits the invoice picker
     * (pick=1); after that only the ticked invoices are included, with the status chosen for each.
     */
    private function range(Request $request): array
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'pick' => ['nullable', 'boolean'],
            'inv' => ['nullable', 'array'],
            'inv.*' => ['in:paid,unpaid'],
        ]);

        $selection = $request->boolean('pick')
            ? collect($data['inv'] ?? [])->mapWithKeys(fn ($s, $id) => [(int) $id => $s])->all()
            : null;

        return [$data['from'] ?? null, $data['to'] ?? null, $selection];
    }

    private function pdfResponse(Company $company, array $range, string $disposition)
    {
        $doc = StatementDocument::make($company, ...$range);

        try {
            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $fontData = (new \Mpdf\Config\FontVariables())->getDefaults()['fontdata'];

            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 12,
                'margin_right' => 12,
                'margin_top' => 12,
                'margin_bottom' => 12,
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
            $mpdf->WriteHTML(view('statement.download', compact('doc'))->render());
            $content = $mpdf->Output('', 'S');
        } catch (\Mpdf\MpdfException $e) {
            Log::error('mPDF Statement Error: '.$e->getMessage());

            return response()->json(['error' => 'PDF generation failed.'], 500);
        }

        $name = trim(preg_replace('/[\\\\\/:*?"<>|\r\n]+/', ' ', $company->name));
        $filename = $name.' - Statement '.$doc['to']->format('d-M-Y').'.pdf';

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition.'; filename="'.$filename.'"',
        ]);
    }
}
