@extends('layouts.master')
@section('title')
    Add Quotation
@endsection
@section('title_page')
    الرئيسية
@endsection
@section('title_page2')
@endsection
@section('content')
    @include('quotation.form', [
        'heading' => 'Add Quotation',
        'action' => route('quotation.store'),
        'quotation' => null,
    ])
@endsection
