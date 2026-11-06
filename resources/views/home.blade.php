@extends('layouts.master')
@section('title')
    Dashboard
@endsection
@section('css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .dash {
            --d-ink: #0f172a;
            --d-muted: #64748b;
            --d-faint: #94a3b8;
            --d-line: #e8edf5;
            --d-card: #ffffff;
            --d-blue: #1f5bd6;
            --d-blue-soft: #e8efff;
            --d-teal: #0ea5a4;
            --d-teal-soft: #e0f7f6;
            --d-amber: #f59e0b;
            --d-amber-soft: #fef3e2;
            --d-rose: #e11d48;
            --d-rose-soft: #ffe8ee;
            --d-up: #059669;
            --d-up-soft: #e3f7ef;
            --d-down: #dc2626;
            --d-down-soft: #fdecec;
            font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            color: var(--d-ink);
            padding-top: 18px;
            padding-bottom: 24px;
        }
        .dash .num { font-variant-numeric: tabular-nums; letter-spacing: -.02em; }

        /* Hero */
        .dash-hero {
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            padding: 28px 30px;
            color: #fff;
            background: linear-gradient(120deg, #0b1f4d 0%, #163a8a 52%, #1f5bd6 100%);
            box-shadow: 0 18px 40px -22px rgba(22, 58, 138, .75);
            margin-bottom: 22px;
        }
        .dash-hero::before, .dash-hero::after {
            content: ""; position: absolute; border-radius: 50%; pointer-events: none;
            background: radial-gradient(circle, rgba(255, 255, 255, .16) 0%, rgba(255, 255, 255, 0) 70%);
        }
        .dash-hero::before { width: 420px; height: 420px; right: -120px; top: -200px; }
        .dash-hero::after { width: 300px; height: 300px; right: 260px; bottom: -220px; }
        .dash-hero > * { position: relative; z-index: 1; }
        .dash-eyebrow { font-size: 12px; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; color: rgba(255, 255, 255, .7); }
        .dash-hero h1 { color: #fff; font-size: 26px; font-weight: 700; margin: 6px 0 4px; letter-spacing: -.02em; }
        .dash-hero .sub { color: rgba(255, 255, 255, .75); font-size: 14px; }
        .dash-hero-stats { display: flex; gap: 14px; flex-wrap: wrap; justify-content: flex-end; }
        .dash-hero-stat {
            min-width: 170px; padding: 14px 18px; border-radius: 14px;
            background: rgba(255, 255, 255, .1); border: 1px solid rgba(255, 255, 255, .16);
            backdrop-filter: blur(6px);
        }
        .dash-hero-stat .k { font-size: 12px; color: rgba(255, 255, 255, .72); font-weight: 500; }
        .dash-hero-stat .v { font-size: 22px; font-weight: 700; margin-top: 2px; white-space: nowrap; }
        .dash-hero-stat .v small { font-size: 12px; font-weight: 600; opacity: .75; margin-right: 3px; }
        .dash-hero .chip { background: rgba(255, 255, 255, .16); color: #fff; }

        /* Cards */
        .d-card {
            background: var(--d-card); border: 1px solid var(--d-line); border-radius: 16px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04), 0 8px 24px -18px rgba(15, 23, 42, .25);
            height: calc(100% - 22px); margin-bottom: 22px;
        }
        .d-card-body { padding: 20px 22px; }
        .d-card-title { font-size: 15px; font-weight: 700; margin: 0; color: var(--d-ink); }
        .d-card-sub { font-size: 12.5px; color: var(--d-muted); margin-top: 2px; }

        .kpi { position: relative; padding: 18px 20px 6px; overflow: hidden; }
        .kpi-top { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
        .kpi-icon { width: 40px; height: 40px; border-radius: 12px; display: grid; place-items: center; font-size: 19px; flex-shrink: 0; }
        .kpi-label { font-size: 13px; color: var(--d-muted); font-weight: 500; margin-top: 14px; }
        .kpi-value { font-size: 24px; font-weight: 800; margin-top: 2px; white-space: nowrap; }
        .kpi-value small { font-size: 13px; color: var(--d-faint); font-weight: 600; margin-right: 4px; }
        .kpi-foot { font-size: 12.5px; color: var(--d-muted); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .kpi-spark { margin: 4px -20px 0; height: 56px; }
        .t-blue { background: var(--d-blue-soft); color: var(--d-blue); }
        .t-teal { background: var(--d-teal-soft); color: var(--d-teal); }
        .t-amber { background: var(--d-amber-soft); color: var(--d-amber); }
        .t-rose { background: var(--d-rose-soft); color: var(--d-rose); }

        .chip { display: inline-flex; align-items: center; gap: 3px; font-size: 12px; font-weight: 600; padding: 3px 9px; border-radius: 999px; white-space: nowrap; }
        .chip-up { background: var(--d-up-soft); color: var(--d-up); }
        .chip-down { background: var(--d-down-soft); color: var(--d-down); }
        .chip-flat { background: #f1f5f9; color: var(--d-muted); }

        .legend { display: flex; gap: 16px; flex-wrap: wrap; font-size: 12.5px; color: var(--d-muted); }
        .legend i { display: inline-block; width: 10px; height: 10px; border-radius: 3px; margin-right: 6px; vertical-align: -1px; }

        .mini-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin: 16px 0 4px; }
        .mini-stat { background: #f8fafc; border: 1px solid var(--d-line); border-radius: 12px; padding: 10px 14px; }
        .mini-stat .k { font-size: 12px; color: var(--d-muted); }
        .mini-stat .v { font-size: 17px; font-weight: 700; margin-top: 2px; white-space: nowrap; }

        /* Share list */
        .share-row { display: flex; align-items: center; gap: 10px; padding: 7px 0; font-size: 13px; }
        .share-row .dot { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }
        .share-row .name { flex: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #334155; }
        .share-row .pct { font-weight: 700; color: var(--d-ink); }

        /* Avatars */
        .avatar { width: 36px; height: 36px; border-radius: 10px; display: grid; place-items: center; font-size: 13px; font-weight: 700; flex-shrink: 0; }

        /* Ranking */
        .rank-row { display: flex; align-items: center; gap: 12px; padding: 11px 0; border-bottom: 1px dashed var(--d-line); }
        .rank-row:last-child { border-bottom: 0; }
        .rank-row .info { flex: 1; min-width: 0; }
        .rank-row .name { font-size: 13.5px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .rank-row .meta { font-size: 12px; color: var(--d-muted); }
        .rank-row .amt { font-size: 13.5px; font-weight: 700; white-space: nowrap; text-align: right; }
        .bar { height: 5px; border-radius: 99px; background: #eef2f7; margin-top: 6px; overflow: hidden; }
        .bar > span { display: block; height: 100%; border-radius: 99px; background: linear-gradient(90deg, #1f5bd6, #5b8cff); }

        /* Table */
        .d-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .d-table th { font-size: 11.5px; text-transform: uppercase; letter-spacing: .06em; color: var(--d-faint); font-weight: 600; padding: 0 10px 10px; border-bottom: 1px solid var(--d-line); white-space: nowrap; }
        .d-table td { padding: 11px 10px; border-bottom: 1px solid var(--d-line); font-size: 13.5px; vertical-align: middle; white-space: nowrap; }
        .d-table tbody tr:last-child td { border-bottom: 0; }
        .d-table tbody tr { transition: background .15s; }
        .d-table tbody tr:hover { background: #f8fafc; }
        .d-table .inv { font-weight: 600; color: var(--d-blue); }
        .d-table .co { display: flex; align-items: center; gap: 10px; max-width: 230px; }
        .d-table .co span { overflow: hidden; text-overflow: ellipsis; }
        .d-btn { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; color: var(--d-blue); background: var(--d-blue-soft); border-radius: 10px; padding: 7px 12px; text-decoration: none; }
        .d-btn:hover { background: #dbe6ff; color: var(--d-blue); }
        .d-empty { color: var(--d-muted); font-size: 13.5px; padding: 18px 0; text-align: center; }

        @media (max-width: 991.98px) {
            .dash-hero { padding: 22px 20px; }
            .dash-hero-stats { justify-content: flex-start; margin-top: 18px; }
            .dash-hero-stat { flex: 1 1 140px; min-width: 0; }
            .mini-stats { grid-template-columns: 1fr; }
        }
    </style>
@endsection
@section('title_page')
@endsection
@section('title_page2')
@endsection
@section('content')
    @php
        $cur = $dash['currency'];
        $m = fn ($v) => number_format($v, 2);
        $whole = fn ($v) => number_format($v, 0);
        $short = function ($v) {
            $a = abs($v);
            return $a >= 1000000 ? number_format($v / 1000000, 2).'M' : ($a >= 1000 ? number_format($v / 1000, 1).'k' : number_format($v, 0));
        };
        $chip = function (?float $pct, string $suffix = '') {
            if ($pct === null) {
                return '<span class="chip chip-flat">No comparison</span>';
            }
            $up = $pct >= 0;
            return '<span class="chip '.($up ? 'chip-up' : 'chip-down').'"><i class="mdi mdi-arrow-'.($up ? 'up' : 'down').'"></i>'
                .number_format(abs($pct), 1).'%'.($suffix ? ' <span style="font-weight:500;opacity:.8">'.$suffix.'</span>' : '').'</span>';
        };
        $palette = [['#e8efff', '#1f5bd6'], ['#e0f7f6', '#0b8f8e'], ['#fef3e2', '#b45309'], ['#ffe8ee', '#be123c'], ['#efe9ff', '#6d28d9'], ['#e6f4ea', '#15803d']];
        $avatar = function (string $name) use ($palette) {
            $clean = trim(preg_replace('/[^A-Za-z0-9 ]+/', ' ', $name));
            $parts = array_values(array_filter(explode(' ', $clean)));
            $ini = strtoupper(substr($parts[0] ?? '?', 0, 1).substr($parts[1] ?? '', 0, 1));
            [$bg, $fg] = $palette[crc32($name) % count($palette)];
            return '<span class="avatar" style="background:'.$bg.';color:'.$fg.'">'.e($ini).'</span>';
        };
        $year = $dash['year'];
        $month = $dash['month'];
        $hour = (int) $dash['now']->format('G');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
        $shareColors = ['#1f5bd6', '#0ea5a4', '#f59e0b', '#e11d48', '#7c3aed', '#16a34a', '#cbd5e1'];
        $share = $dash['top_companies']->map(fn ($c) => ['name' => $c['name'], 'total' => round($c['total'], 2)])->values()->all();
        if ($dash['others_total'] > 0) {
            $share[] = ['name' => 'Other companies', 'total' => round($dash['others_total'], 2)];
        }
        $maxTop = max(1, $dash['top_companies']->max('total') ?? 1);
    @endphp

    <div class="container-fluid dash">
        {{-- Hero --}}
        <div class="dash-hero">
            <div class="row align-items-center">
                <div class="col-lg-5">
                    <div class="dash-eyebrow">MDM Derma · Sales overview</div>
                    <h1>{{ $greeting }}{{ auth()->user() ? ', '.\Illuminate\Support\Str::of(auth()->user()->name)->before(' ') : '' }}</h1>
                    <div class="sub">{{ $dash['now']->format('l, d F Y') }} &nbsp;·&nbsp; figures from {{ $year['count'] }} invoices this year</div>
                </div>
                <div class="col-lg-7">
                    <div class="dash-hero-stats">
                        <div class="dash-hero-stat">
                            <div class="k">Sales {{ $dash['now']->year }} (incl. VAT)</div>
                            <div class="v num"><small>{{ $cur }}</small>{{ $whole($year['total']) }}</div>
                            <div class="mt-1">{!! $chip($year['change'], 'vs last year') !!}</div>
                        </div>
                        <div class="dash-hero-stat">
                            <div class="k">Average invoice</div>
                            <div class="v num"><small>{{ $cur }}</small>{{ $whole($year['average']) }}</div>
                            <div class="mt-1"><span class="chip">{{ $dash['active_companies'] }} active companies</span></div>
                        </div>
                        <div class="dash-hero-stat">
                            <div class="k">After credit notes</div>
                            <div class="v num"><small>{{ $cur }}</small>{{ $whole($year['after_credits']) }}</div>
                            <div class="mt-1"><span class="chip">{{ $year['credits_count'] }} credit notes</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KPI cards --}}
        <div class="row">
            @php
                $kpis = [
                    ['id' => 'spark-month', 'label' => 'Sales this month', 'value' => $month['total'], 'icon' => 'mdi-calendar-month-outline', 'tone' => 't-blue', 'chip' => $chip($month['change'], 'vs last month'), 'foot' => $month['count'].' invoices in '.$dash['now']->format('F')],
                    ['id' => 'spark-net', 'label' => 'Sales without VAT · '.$dash['now']->year, 'value' => $year['net'], 'icon' => 'mdi-cash-multiple', 'tone' => 't-teal', 'chip' => '<span class="chip chip-flat">'.$year['count'].' invoices</span>', 'foot' => 'Average '.$cur.' '.$whole($year['average']).' per invoice'],
                    ['id' => 'spark-vat', 'label' => 'VAT collected · '.$dash['now']->year, 'value' => $year['vat'], 'icon' => 'mdi-percent-outline', 'tone' => 't-amber', 'chip' => '<span class="chip chip-flat">5% rated</span>', 'foot' => 'Due to FTA on this year\'s sales'],
                    ['id' => 'spark-credits', 'label' => 'Credit notes · '.$dash['now']->year, 'value' => $year['credits'], 'icon' => 'mdi-file-undo-outline', 'tone' => 't-rose', 'chip' => '<span class="chip chip-flat">'.$year['credits_count'].' notes</span>', 'foot' => $year['total'] > 0 ? number_format($year['credits'] / $year['total'] * 100, 1).'% of sales returned' : '—'],
                ];
            @endphp
            @foreach ($kpis as $k)
                <div class="col-md-6 col-xl-3">
                    <div class="d-card kpi">
                        <div class="kpi-top">
                            <div class="kpi-icon {{ $k['tone'] }}"><i class="mdi {{ $k['icon'] }}"></i></div>
                            {!! $k['chip'] !!}
                        </div>
                        <div class="kpi-label">{{ $k['label'] }}</div>
                        <div class="kpi-value num"><small>{{ $cur }}</small>{{ $m($k['value']) }}</div>
                        <div class="kpi-foot">{{ $k['foot'] }}</div>
                        <div class="kpi-spark" id="{{ $k['id'] }}"></div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Chart + share --}}
        <div class="row">
            <div class="col-xl-8">
                <div class="d-card">
                    <div class="d-card-body">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                            <div>
                                <h4 class="d-card-title">Monthly sales</h4>
                                <div class="d-card-sub">Last 12 months · invoices incl. VAT, credit notes deducted</div>
                            </div>
                            <div class="legend mt-1">
                                <span><i style="background:#1f5bd6"></i>Without VAT</span>
                                <span><i style="background:#7fb0ff"></i>VAT</span>
                                <span><i style="background:#f43f5e"></i>Credit notes</span>
                            </div>
                        </div>
                        <div class="mini-stats">
                            <div class="mini-stat">
                                <div class="k">This month</div>
                                <div class="v num">{{ $cur }} {{ $short($month['total']) }}</div>
                            </div>
                            <div class="mini-stat">
                                <div class="k">Last month</div>
                                <div class="v num">{{ $cur }} {{ $short($month['last_total']) }}</div>
                            </div>
                            <div class="mini-stat">
                                <div class="k">Best month</div>
                                @php $best = $dash['months']->sortByDesc('total')->first(); @endphp
                                <div class="v num">{{ $best && $best['total'] > 0 ? $best['label'] : '—' }}</div>
                            </div>
                        </div>
                        <div id="sales-chart" dir="ltr" style="min-height: 400px;"></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="d-card">
                    <div class="d-card-body">
                        <h4 class="d-card-title">Sales by company</h4>
                        <div class="d-card-sub">Share of {{ $dash['now']->year }} sales</div>
                        @if (count($share))
                            <div id="share-chart" dir="ltr" class="my-2" style="min-height: 240px;"></div>
                            @foreach ($share as $i => $s)
                                <div class="share-row">
                                    <span class="dot" style="background: {{ $shareColors[$i] ?? '#cbd5e1' }}"></span>
                                    <span class="name" title="{{ $s['name'] }}">{{ $s['name'] }}</span>
                                    <span class="pct num">{{ $year['total'] > 0 ? number_format($s['total'] / $year['total'] * 100, 1) : 0 }}%</span>
                                </div>
                            @endforeach
                        @else
                            <div class="d-empty">No invoices this year yet.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Latest invoices + ranking --}}
        <div class="row">
            <div class="col-xl-8">
                <div class="d-card">
                    <div class="d-card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h4 class="d-card-title">Latest invoices</h4>
                                <div class="d-card-sub">The 8 most recent tax invoices</div>
                            </div>
                            <a href="{{ route('invoice.index') }}" class="d-btn">All invoices <i class="mdi mdi-arrow-right"></i></a>
                        </div>
                        <div class="table-responsive">
                            <table class="d-table">
                                <thead>
                                    <tr>
                                        <th>Invoice</th>
                                        <th>Company</th>
                                        <th>Date</th>
                                        <th class="text-end">VAT</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($dash['latest'] as $inv)
                                        <tr>
                                            <td><a class="inv" href="{{ route('invoice.show', $inv['id']) }}">{{ $inv['number'] }}</a></td>
                                            <td><div class="co">{!! $avatar($inv['company']) !!}<span title="{{ $inv['company'] }}">{{ $inv['company'] }}</span></div></td>
                                            <td class="text-muted">{{ $inv['date']->format('d M Y') }}</td>
                                            <td class="text-end text-muted num">{{ $m($inv['vat']) }}</td>
                                            <td class="text-end num" style="font-weight:700">{{ $cur }} {{ $m($inv['total']) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="d-empty">No invoices yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="d-card">
                    <div class="d-card-body">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <h4 class="d-card-title">Top companies</h4>
                                <div class="d-card-sub">{{ $dash['now']->year }} · {{ $dash['companies'] }} companies in total</div>
                            </div>
                            <a href="{{ route('statement.index') }}" class="d-btn" title="Statement of Accounts"><i class="mdi mdi-file-document-outline"></i></a>
                        </div>
                        @forelse ($dash['top_companies'] as $c)
                            <div class="rank-row">
                                {!! $avatar($c['name']) !!}
                                <div class="info">
                                    <div class="name" title="{{ $c['name'] }}">{{ $c['name'] }}</div>
                                    <div class="bar"><span style="width: {{ max(3, round($c['total'] / $maxTop * 100)) }}%"></span></div>
                                    <div class="meta mt-1">{{ $c['count'] }} invoices</div>
                                </div>
                                <div class="amt num">{{ $cur }} {{ $short($c['total']) }}</div>
                            </div>
                        @empty
                            <div class="d-empty">No invoices this year yet.</div>
                        @endforelse
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
            var share = @json($share);
            var shareColors = @json($shareColors);
            var cur = @json($cur);
            var font = 'Inter, system-ui, sans-serif';
            var money = function (v) {
                return cur + ' ' + Number(v).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            };
            var compact = function (v) {
                var a = Math.abs(v);
                return a >= 1e6 ? (v / 1e6).toFixed(1) + 'M' : a >= 1e3 ? (v / 1e3).toFixed(0) + 'k' : Math.round(v);
            };
            var labels = months.map(function (m) { return m.label; });

            function spark(id, data, color) {
                var el = document.getElementById(id);
                if (!el) {
                    return;
                }
                new ApexCharts(el, {
                    chart: { type: 'area', height: 56, sparkline: { enabled: true }, animations: { speed: 500 } },
                    series: [{ name: 'Amount', data: data }],
                    stroke: { curve: 'smooth', width: 2.2 },
                    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: .35, opacityTo: 0, stops: [0, 95] } },
                    colors: [color],
                    tooltip: {
                        fixed: { enabled: false },
                        x: { formatter: function (v, o) { return labels[o.dataPointIndex]; } },
                        y: { title: { formatter: function () { return ''; } }, formatter: money },
                        marker: { show: false }
                    }
                }).render();
            }
            spark('spark-month', months.map(function (m) { return m.total; }), '#1f5bd6');
            spark('spark-net', months.map(function (m) { return m.net; }), '#0ea5a4');
            spark('spark-vat', months.map(function (m) { return m.vat; }), '#f59e0b');
            spark('spark-credits', months.map(function (m) { return m.credits; }), '#e11d48');

            new ApexCharts(document.querySelector('#sales-chart'), {
                chart: { type: 'bar', height: 400, stacked: true, toolbar: { show: false }, fontFamily: font, animations: { speed: 600 } },
                series: [
                    { name: 'Without VAT', data: months.map(function (m) { return m.net; }) },
                    { name: 'VAT', data: months.map(function (m) { return m.vat; }) },
                    { name: 'Credit notes', data: months.map(function (m) { return -m.credits; }) }
                ],
                colors: ['#1f5bd6', '#7fb0ff', '#f43f5e'],
                plotOptions: { bar: { columnWidth: '46%', borderRadius: 6, borderRadiusApplication: 'end', borderRadiusWhenStacked: 'last' } },
                dataLabels: { enabled: false },
                legend: { show: false },
                states: { hover: { filter: { type: 'darken', value: .9 } } },
                xaxis: {
                    categories: labels.map(function (l) { return l.split(' ')[0]; }),
                    axisBorder: { show: false }, axisTicks: { show: false },
                    labels: { style: { colors: '#94a3b8', fontSize: '12px' } }
                },
                yaxis: { labels: { formatter: compact, style: { colors: '#94a3b8', fontSize: '12px' } } },
                grid: { borderColor: '#eef2f7', strokeDashArray: 4, padding: { left: 4, right: 4 } },
                tooltip: {
                    shared: true, intersect: false,
                    y: { formatter: function (v) { return money(Math.abs(v)); } },
                    x: {
                        formatter: function (v, o) {
                            var mo = months[o.dataPointIndex];
                            return mo.label + ' · ' + mo.count + ' invoices · ' + money(mo.total);
                        }
                    }
                }
            }).render();

            var shareEl = document.querySelector('#share-chart');
            if (shareEl && share.length) {
                var total = share.reduce(function (s, x) { return s + x.total; }, 0);
                new ApexCharts(shareEl, {
                    chart: { type: 'donut', height: 240, fontFamily: font },
                    series: share.map(function (s) { return s.total; }),
                    labels: share.map(function (s) { return s.name; }),
                    colors: shareColors.slice(0, share.length),
                    legend: { show: false },
                    dataLabels: { enabled: false },
                    stroke: { width: 3, colors: ['#fff'] },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '74%',
                                labels: {
                                    show: true,
                                    value: { fontSize: '20px', fontWeight: 700, color: '#0f172a', offsetY: 4, formatter: function (v) { return cur + ' ' + compact(Number(v)); } },
                                    name: { fontSize: '12px', color: '#64748b', offsetY: -6 },
                                    total: { show: true, label: 'Total sales', color: '#64748b', fontSize: '12px', formatter: function () { return cur + ' ' + compact(total); } }
                                }
                            }
                        }
                    },
                    tooltip: { y: { formatter: money } }
                }).render();
            }
        })();
    </script>
@endsection
