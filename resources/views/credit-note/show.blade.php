@extends('layouts.master')
@section('title')
    Credit Note
@endsection
@section('css')
    @include('invoice.designs.classic-styles')
    <style>
        @page { size: A4; margin: 12mm 11mm 14mm; }

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
        }
    </style>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row d-print-none">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">Credit Note</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        @include('credit-note.designs.classic', ['creditNote' => $creditNote, 'logoSrc' => asset(config('mdm.logo_path'))])

                        <div class="mt-4 mb-1 d-print-none">
                            <div class="text-end">
                                <a href="javascript:window.print()" class="btn btn-primary waves-effect waves-light">
                                    <i class="mdi mdi-printer me-1"></i> Print
                                </a>
                                <form action="{{ route('credit-note.download', $creditNote->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-primary waves-effect waves-light">
                                        <i class="mdi mdi-download me-1"></i> Download PDF
                                    </button>
                                </form>
                                <a href="{{ route('credit-note.index') }}" class="btn btn-light">Back</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
@endsection
