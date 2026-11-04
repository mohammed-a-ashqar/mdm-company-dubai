@extends('layouts.master')
@section('title')
    Quotation
@endsection
@section('css')
    @include('quotation.designs.styles')
    <style>
        .q-scroll { overflow-x: auto; }
        .q-paper {
            box-sizing: border-box;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 52mm 6mm 56mm;
            background: #fff url('{{ asset('assets/images/quotation-letterhead.png') }}') top left / 100% 297mm no-repeat;
            box-shadow: 0 0 6px rgba(0, 0, 0, .25);
        }
    </style>
@endsection
@section('title_page')
    الرئيسية
@endsection
@section('title_page2')
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">Quotation</h4>
                </div>
            </div>
        </div>

        @if (session()->has('success'))
            <div class="alert alert-primary alert-dismissible fade show" role="alert">
                {{ session()->get('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="text-end mb-3">
                    <a href="{{ route('quotation.pdf', $quotation->id) }}" target="_blank" class="btn btn-primary waves-effect waves-light">
                        <i class="mdi mdi-printer me-1"></i> Open PDF to print
                    </a>
                    <a href="{{ route('quotation.download', $quotation->id) }}" class="btn btn-primary waves-effect waves-light">
                        <i class="mdi mdi-download me-1"></i> Download PDF
                    </a>
                    <a href="{{ route('quotation.edit', $quotation->id) }}" class="btn btn-secondary">Edit</a>
                    <a href="{{ route('quotation.index') }}" class="btn btn-light">Back</a>
                </div>
                <div class="q-scroll">
                    <div class="q-paper">
                        @include('quotation.designs.document')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
@endsection
