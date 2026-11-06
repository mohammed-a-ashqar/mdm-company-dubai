@extends('layouts.master')
@section('title')
    Statement of Accounts
@endsection
@section('css')
    @include('statement.styles')
    <style>
        .st-scroll { overflow-x: auto; }
        .st-paper {
            box-sizing: border-box;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 12mm;
            background: #fff;
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
    @php
        $query = request()->query();
    @endphp
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">Statement of Accounts — {{ $company->name }}</h4>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('statement.show', $company->id) }}" id="st-form" class="row g-2 align-items-end mb-3">
                    <input type="hidden" name="pick" value="{{ $range[2] === null ? 0 : 1 }}" id="st-pick">
                    <div class="col-md-3">
                        <label class="form-label">From</label>
                        <input type="date" name="from" class="form-control" value="{{ $range[0] }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To</label>
                        <input type="date" name="to" class="form-control" value="{{ $range[1] ?? now()->toDateString() }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100">Update</button>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="{{ route('statement.pdf', [$company->id] + $query) }}" target="_blank" class="btn btn-primary waves-effect waves-light">
                            <i class="mdi mdi-printer me-1"></i> Open PDF to print
                        </a>
                        <a href="{{ route('statement.download', [$company->id] + $query) }}" class="btn btn-primary waves-effect waves-light">
                            <i class="mdi mdi-download me-1"></i> Download PDF
                        </a>
                        <a href="{{ route('statement.index') }}" class="btn btn-light">Back</a>
                    </div>

                    <div class="col-12 mt-3">
                        <h5 class="mb-2">Invoices on the statement</h5>
                        <p class="text-muted mb-2">Tick the invoices to include and choose Paid / Unpaid for each one. The invoice's own status is not used.</p>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-2" style="max-width: 760px;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40px;"><input type="checkbox" class="form-check-input" id="st-all" title="Select all"></th>
                                        <th>Invoice</th>
                                        <th>Issue Date</th>
                                        <th class="text-end">Total</th>
                                        <th style="width: 150px;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($doc['candidates'] as $inv)
                                        <tr>
                                            <td><input type="checkbox" class="form-check-input st-include" data-id="{{ $inv['id'] }}" {{ $inv['included'] ? 'checked' : '' }}></td>
                                            <td>{{ $inv['number'] }}</td>
                                            <td>{{ $inv['date']->format('d-m-Y') }}</td>
                                            <td class="text-end">{{ \App\Support\StatementDocument::money($inv['total']) }} {{ $doc['currency'] }}</td>
                                            <td>
                                                <select name="inv[{{ $inv['id'] }}]" id="st-status-{{ $inv['id'] }}" class="form-select form-select-sm" {{ $inv['included'] ? '' : 'disabled' }}>
                                                    <option value="unpaid" {{ $inv['status'] === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                                    <option value="paid" {{ $inv['status'] === 'paid' ? 'selected' : '' }}>Paid</option>
                                                </select>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-muted">No invoices for this company in the period.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <button type="submit" class="btn btn-success" onclick="document.getElementById('st-pick').value = 1;">Apply to statement</button>
                    </div>
                </form>
                <div class="st-scroll">
                    <div class="st-paper">
                        @include('statement.document', ['doc' => $doc])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        (function () {
            var boxes = document.querySelectorAll('.st-include');
            var all = document.getElementById('st-all');
            function sync(box) {
                document.getElementById('st-status-' + box.dataset.id).disabled = !box.checked;
            }
            function syncAll() {
                all.checked = boxes.length > 0 && Array.prototype.every.call(boxes, function (b) { return b.checked; });
            }
            boxes.forEach(function (box) {
                box.addEventListener('change', function () { sync(box); syncAll(); });
            });
            all.addEventListener('change', function () {
                boxes.forEach(function (box) { box.checked = all.checked; sync(box); });
            });
            syncAll();
        })();
    </script>
@endsection
