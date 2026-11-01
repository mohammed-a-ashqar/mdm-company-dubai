@extends('layouts.master')
@section('title')
    Invoice {{ $doc['number'] }}
@endsection

@section('css')
    @include('invoice.designs.classic-styles')
    <style>
        @page { size: A4; margin: 12mm 11mm 14mm; }

        .invoice-multi-print {
            display: none;
        }

        @media print {
            body,
            .content-page,
            .content,
            .container-fluid,
            .card,
            .card-body {
                margin: 0 !important;
                padding: 0 !important;
                border: 0 !important;
                box-shadow: none !important;
                background: #fff !important;
            }

            .d-print-none,
            .page-title-box {
                display: none !important;
            }

            body.multi-page-print .invoice-normal-print {
                display: none !important;
            }

            body.multi-page-print .invoice-multi-print {
                display: block !important;
            }

            .print-invoice-page {
                page-break-after: always;
            }

            .print-invoice-page:last-child {
                page-break-after: auto;
            }
        }
    </style>
@endsection

@section('content')
    @php $logoSrc = asset('assets/images/mdm.png'); @endphp
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">Invoice</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="invoice-normal-print">
                            @include('invoice.designs.classic', ['doc' => $doc, 'logoSrc' => $logoSrc])
                        </div>

                        {{-- 3-page print: page 1 = 2 products, page 2 = 1 product, page 3 = last products; each with its own totals --}}
                        <div class="invoice-multi-print">
                            @foreach ($pages as $page)
                                <div class="print-invoice-page">
                                    @include('invoice.designs.classic', ['doc' => $page, 'logoSrc' => $logoSrc])
                                </div>
                            @endforeach
                        </div>

                        <!-- Print Buttons -->
                        <div class="mt-4 mb-1">
                            <div class="text-end d-print-none">
                                <a href="javascript:window.print()" class="btn btn-primary waves-effect waves-light">
                                    <i class="mdi mdi-printer me-1"></i> Print
                                </a>
                                <button type="button" id="btn-print-3page"
                                    class="btn btn-outline-primary waves-effect waves-light ms-1"
                                    title="Page 1: 2 products · Page 2: 1 product · Page 3: last products (each page has its own total)">
                                    <i class="mdi mdi-printer-settings me-1"></i> Print (3-page layout)
                                </button>
                            </div>
                        </div>

                        <!-- Download PDF Button -->
                        <div class="text-end d-print-none">
                            <form action="{{ route('download', $invoiceName->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-primary waves-effect waves-light">
                                    <i class="mdi mdi-download me-1"></i> Download PDF
                                </button>
                            </form>
                        </div>
                    </div> <!-- end card-body -->
                </div> <!-- end card -->
            </div> <!-- end col -->
        </div> <!-- end row -->

    </div> <!-- container -->

    <script>
        (function () {
            var btn = document.getElementById('btn-print-3page');
            if (!btn) return;

            btn.addEventListener('click', function () {
                document.body.classList.add('multi-page-print');
                setTimeout(function () {
                    window.print();
                }, 150);
            });

            window.addEventListener('afterprint', function () {
                document.body.classList.remove('multi-page-print');
            });
        })();
    </script>
@endsection
