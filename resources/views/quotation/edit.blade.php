@extends('layouts.master')
@section('title')
    Edit Quotation
@endsection
@section('title_page')
    الرئيسية
@endsection
@section('title_page2')
@endsection
@section('content')
    @include('quotation.form', [
        'heading' => 'Edit Quotation',
        'action' => route('quotation.update', $quotation->id),
    ])
@endsection
