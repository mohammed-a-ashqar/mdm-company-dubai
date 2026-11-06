@extends('layouts.master')
@section('title')
    Dashboard
@endsection
@section('css')
@endsection
@section('title_page')
@endsection
@section('title_page2')
@endsection
@section('content')
    @php
        $cur = $dash['currency'];
        $m = fn ($v) => number_format($v, 2);
        $short = function ($v) {
            $a = abs($v);
            return $a >= 1000000 ? number_format($v / 1000000, 2).'M' : ($a >= 1000 ? number_format($v / 1000, 1).'k' : number_format($v, 0));
        };
        $change = $dash['month']['change'];
        $kpis = [
            ['label' => 'Sales this month', 'value' => $cur.' '.$m($dash['month']['total']), 'sub' => $dash['month']['count'].' invoices · '.$dash['now']->format('F Y'), 'icon' => 'dripicons-wallet', 'tone' => 'primary'],
            ['label' => 'Sales this year', 'value' => $cur.' '.$m($dash['year']['total']), 'sub' => $dash['year']['count'].' invoices in '.$dash['now']->year.' · incl. VAT', 'icon' => 'dripicons-graph-bar', 'tone' => 'success'],
            ['label' => 'VAT this year', 'value' => $cur.' '.$m($dash['year']['vat']), 'sub' => 'Without VAT: '.$cur.' '.$m($dash['year']['net']), 'icon' => 'dripicons-document', 'tone' => 'info'],
            ['label' => 'Credit notes this year', 'value' => $cur.' '.$m($dash['year']['credits']), 'sub' => $dash['year']['credits_count'].' notes · After credits: '.$cur.' '.$m($dash['year']['after_credits']), 'icon' => 'dripicons-return', 'tone' => 'danger'],
        ];
    @endphp
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right text-muted">Updated {{ $dash['now']->format('d M Y, H:i') }}</div>
                    <h4 class="page-title">Dashboard</h4>
                </div>
            </div>
        </div>

        <div class="row">
            @foreach ($kpis as $k)
                <div class="col-md-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar-md rounded bg-soft-{{ $k['tone'] }} flex-shrink-0">
                                    <i class="{{ $k['icon'] }} font-22 avatar-title text-{{ $k['tone'] }}"></i>
                                </div>
                                <div class="ms-3 text-truncate">
                                    <p class="text-muted mb-1 text-truncate">{{ $k['label'] }}</p>
                                    <h4 class="text-dark my-0">{{ $k['value'] }}</h4>
                                </div>
                            </div>
                            <p class="text-muted font-13 mb-0 mt-2 text-truncate">{{ $k['sub'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-body pb-2">
                        <h4 class="header-title mb-3">Sales — last 12 months</h4>
                        <div class="row text-center">
                            <div class="col-md-4">
                                <p class="text-muted mb-0 mt-2">This month</p>
                                <h3 class="fw-normal mb-2">{{ $cur }} {{ $short($dash['month']['total']) }}</h3>
                            </div>
                            <div class="col-md-4">
                                <p class="text-muted mb-0 mt-2">Last month</p>
                                <h3 class="fw-normal mb-2">{{ $cur }} {{ $short($dash['month']['last_total']) }}</h3>
                            </div>
                            <div class="col-md-4">
                                <p class="text-muted mb-0 mt-2">Change</p>
                                <h3 class="fw-normal mb-2">
                                    @if ($change === null)
                                        —
                                    @else
                                        <i class="fe-arrow-{{ $change >= 0 ? 'up text-success' : 'down text-danger' }} me-1"></i>{{ number_format(abs($change), 1) }}%
                                    @endif
                                </h3>
                            </div>
                        </div>
                        <div id="sales-chart" class="mt-2" dir="ltr" style="min-height: 320px;"></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title mb-3">Top companies — {{ $dash['now']->year }}</h4>
                        @php $max = max(1, $dash['top_companies']->max('total') ?? 1); @endphp
                        @forelse ($dash['top_companies'] as $c)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span class="text-truncate me-2" title="{{ $c['name'] }}">{{ $c['name'] }}</span>
                                    <span class="text-nowrap fw-semibold">{{ $cur }} {{ $short($c['total']) }}</span>
                                </div>
                                <div class="progress mt-1" style="height: 6px;">
                                    <div class="progress-bar" role="progressbar" style="width: {{ round($c['total'] / $max * 100) }}%"></div>
                                </div>
                                <small class="text-muted">{{ $c['count'] }} invoices</small>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No invoices this year yet.</p>
                        @endforelse
                        <hr>
                        <div class="row text-center">
                            <div class="col-4">
                                <p class="text-muted mb-1 font-13">Companies</p>
                                <h4 class="my-0">{{ $dash['companies'] }}</h4>
                            </div>
                            <div class="col-4">
                                <p class="text-muted mb-1 font-13">Active {{ $dash['now']->year }}</p>
                                <h4 class="my-0">{{ $dash['active_companies'] }}</h4>
                            </div>
                            <div class="col-4">
                                <p class="text-muted mb-1 font-13">Quotations</p>
                                <h4 class="my-0">{{ $dash['year']['quotations'] }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="header-title mb-0">Latest invoices</h4>
                            <a href="{{ route('invoice.index') }}" class="btn btn-sm btn-light">All invoices</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-centered table-nowrap table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Invoice</th>
                                        <th>Company</th>
                                        <th>Date</th>
                                        <th class="text-end">Without VAT</th>
                                        <th class="text-end">VAT</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($dash['latest'] as $inv)
                                        <tr>
                                            <td><a href="{{ route('invoice.show', $inv['id']) }}">{{ $inv['number'] }}</a></td>
                                            <td>{{ $inv['company'] }}</td>
                                            <td>{{ $inv['date']->format('d-m-Y') }}</td>
                                            <td class="text-end">{{ $m($inv['net']) }}</td>
                                            <td class="text-end">{{ $m($inv['vat']) }}</td>
                                            <td class="text-end fw-semibold">{{ $cur }} {{ $m($inv['total']) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-muted">No invoices yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        (function () {
            if (typeof ApexCharts === 'undefined') {
                return;
            }
            var months = @json($dash['months']);
            var cur = @json($cur);
            var fmt = function (v) {
                return cur + ' ' + Number(v).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            };
            new ApexCharts(document.querySelector('#sales-chart'), {
                chart: { type: 'bar', height: 320, stacked: true, toolbar: { show: false } },
                series: [
                    { name: 'Without VAT', data: months.map(function (m) { return m.net; }) },
                    { name: 'VAT', data: months.map(function (m) { return m.vat; }) },
                    { name: 'Credit notes', data: months.map(function (m) { return -m.credits; }) }
                ],
                colors: ['#6658dd', '#1abc9c', '#f1556c'],
                plotOptions: { bar: { columnWidth: '55%', borderRadius: 2 } },
                dataLabels: { enabled: false },
                xaxis: { categories: months.map(function (m) { return m.label; }) },
                yaxis: { labels: { formatter: function (v) { return Math.abs(v) >= 1000 ? (v / 1000).toFixed(0) + 'k' : v.toFixed(0); } } },
                tooltip: {
                    shared: true,
                    intersect: false,
                    y: { formatter: function (v) { return fmt(v); } },
                    x: {
                        formatter: function (label, opts) {
                            var mo = months[opts.dataPointIndex];
                            return label + ' — ' + mo.count + ' invoices · ' + fmt(mo.net + mo.vat);
                        }
                    }
                },
                legend: { position: 'top' },
                grid: { borderColor: '#f1f3fa' }
            }).render();
        })();
    </script>
@endsection
