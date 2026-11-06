@extends('layouts.master')
@section('title')
    Statement of Accounts
@endsection
@section('css')
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
                    <h4 class="page-title">Statement of Accounts</h4>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form class="row g-2 align-items-end" onsubmit="event.preventDefault(); openStatement();">
                    <div class="col-md-4">
                        <label for="st_company" class="form-label">Company</label>
                        <select id="st_company" class="form-select" required>
                            <option value="" selected disabled>— Choose a company —</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="st_from" class="form-label">From <small class="text-muted">(empty = first invoice)</small></label>
                        <input type="date" id="st_from" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="st_to" class="form-label">To</label>
                        <input type="date" id="st_to" class="form-control" value="{{ now()->toDateString() }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Show statement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openStatement() {
            var company = document.getElementById('st_company').value;
            if (!company) {
                return;
            }
            var url = @json(route('statement.show', ['company' => '__ID__'])).replace('__ID__', company);
            var params = new URLSearchParams();
            var from = document.getElementById('st_from').value;
            var to = document.getElementById('st_to').value;
            if (from) {
                params.set('from', from);
            }
            if (to) {
                params.set('to', to);
            }
            var query = params.toString();
            window.location.href = query ? url + '?' + query : url;
        }
    </script>
@endsection
@section('scripts')
@endsection
