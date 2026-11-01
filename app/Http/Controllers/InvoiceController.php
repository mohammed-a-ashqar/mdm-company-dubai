<?php

namespace App\Http\Controllers;

use Mpdf\Mpdf;
use Dompdf\Dompdf;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Product;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\Request;

use App\Services\CompanyService;
use App\Services\InvoiceService;
use App\Support\InvoiceDocument;
use App\Http\Requests\InvoiceRequest;
use Barryvdh\Snappy\Facades\SnappyPdf;
use App\Http\Requests\UpdateInvoiceRequest;
use Log;

class InvoiceController extends Controller
{
    protected $invoiceService;
    protected $companyService;

    public function __construct(InvoiceService $invoiceService, CompanyService $companyService)
    {
        $this->invoiceService = $invoiceService;
        $this->companyService = $companyService;
    }

    public function index()
    {
        $paginate = request()->paginate ?? 10;
        $invoices = $this->invoiceService->index([], [], ['*'], $paginate);
        $companies = Company::orderBy('name')->get();

        $years = Invoice::query()
            ->selectRaw('YEAR(`from`) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        if ($years->isEmpty()) {
            $years = collect([(int) date('Y')]);
        }

        return view('invoice.index', [
            'invoices' => $invoices,
            'companies' => $companies,
            'years' => $years,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $companies = $this->invoiceService->create();

        return view(
            'invoice.create',
            ['companies' => $companies]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(InvoiceRequest $request)
    {
        $this->invoiceService->store($request);
        return redirect()->route('invoice.index')->with('success', 'Added Sccesfully ');
    }


    public function edit($id)
    {
        $invoice = $this->invoiceService->find($id, ['*']);
        $companies = Company::all();

        return view('invoice.edit', [
            'invoice' => $invoice,
            'companies' => $companies
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function update($id, InvoiceRequest $request)
    {

        $this->invoiceService->update($id, $request);

        return redirect()->route('invoice.index')->with('success', 'Edited Sccesfully ');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $invoice = $this->invoiceService->delete($id);
        return redirect()->back()->with('success', 'Deleted Successfully');
    }

    public function show($id)

            {


            $products  = $this->invoiceService->show($id);
            $invoiceName = Invoice::where('id' , $id)->with(['company'])->first();

        return view('invoice.show', [
            'invoiceName' => $invoiceName,
            'doc' => InvoiceDocument::make($invoiceName, $products),
            'pages' => InvoiceDocument::pages($invoiceName, $products),
        ]);
    }


    public function printByCompany(Request $request, $companyId)
    {
        $month = $request->filled('month') ? (int) $request->month : null;
        $year = $request->filled('year') ? (int) $request->year : null;

        $companyIdInt = ($companyId === 'all') ? null : (int) $companyId;

        $data = $this->invoiceService->getCompanyInvoicesPrintData($companyIdInt, $month, $year);

        if (empty($data['invoices'])) {
            return redirect()
                ->route('invoice.index')
                ->with('error', 'No invoices found for the selected filters.');
        }

        return view('invoice.print-by-company', $data);
    }

    public function downloadInvoice($id)
    {
        \Log::info("Download Invoice Process Started");

        $products = $this->invoiceService->show($id);
        $invoiceName = Invoice::where('id', $id)->with(['company'])->first();

        $html = view('invoice.download', [
            'doc' => InvoiceDocument::make($invoiceName, $products),
        ])->render();

        // Check if HTML is generated correctly
        \Log::info("HTML content generated");

        // Same page geometry as the browser print of the preview page (A4).
        try {
            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $fontData = (new \Mpdf\Config\FontVariables())->getDefaults()['fontdata'];

            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 11,
                'margin_right' => 11,
                'margin_top' => 12,
                'margin_bottom' => 14,
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
            $mpdf->WriteHTML($html);

            \Log::info("PDF generated and download triggered.");

            return response($mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="invoice_' . $invoiceName->id . '.pdf"',
            ]);
        } catch (\Mpdf\MpdfException $e) {
            \Log::error('mPDF Error: ' . $e->getMessage());
            return response()->json(['error' => 'PDF generation failed!'], 500);
        }
    }

}
