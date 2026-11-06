@extends('layouts.master')
@section('title')
    Dashboard
@endsection
@section('css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .ov {
            --ink: #16191d;
            --ink-2: #3d434b;
            --mute: #6b7280;
            --line: #e2e5e9;
            --line-2: #eef0f2;
            --panel: #fff;
            --accent: #1c4fa1;
            --accent-2: #9db6dd;
            --pos: #13795b;
            --neg: #b42318;
            font-family: "IBM Plex Sans", "Segoe UI", Roboto, Arial, sans-serif;
            color: var(--ink);
            font-size: 14px;
            padding: 22px 0 28px;
        }
        .ov .fig { font-variant-numeric: tabular-nums; }
        .ov a { color: var(--accent); }

        .ov-head { display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; flex-wrap: wrap; margin-bottom: 18px; }
        .ov-head h1 { font-size: 21px; font-weight: 600; margin: 0; color: var(--ink); }
        .ov-head p { margin: 4px 0 0; color: var(--mute); }
        .ov-tools { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .ov-years { display: inline-flex; border: 1px solid var(--line); border-radius: 6px; overflow: hidden; background: var(--panel); }
        .ov-years a { padding: 6px 14px; color: var(--ink-2); text-decoration: none; border-left: 1px solid var(--line); font-weight: 500; }
        .ov-years a:first-child { border-left: 0; }
        .ov-years a.on { background: var(--ink); color: #fff; }
        .ov-link { padding: 6px 12px; border: 1px solid var(--line); border-radius: 6px; background: var(--panel); color: var(--ink-2) !important; text-decoration: none; font-weight: 500; }
        .ov-link:hover { border-color: #c9ced4; }

        .ov-panel { background: var(--panel); border: 1px solid var(--line); border-radius: 6px; margin-bottom: 20px; }
        .ov-panel-h { display: flex; justify-content: space-between; align-items: baseline; gap: 12px; padding: 14px 18px; border-bottom: 1px solid var(--line-2); }
        .ov-panel-h h2 { font-size: 14.5px; font-weight: 600; margin: 0; color: var(--ink); }
        .ov-panel-h span { color: var(--mute); font-size: 13px; }
        .ov-panel-b { padding: 16px 18px; }
        .ov-row { display: flex; flex-wrap: wrap; margin: 0 -10px; }
        .ov-row > div { padding: 0 10px; }
        .ov-row > div > .ov-panel { height: calc(100% - 20px); }

        .ov-sum { display: grid; grid-template-columns: 1.35fr repeat(4, 1fr); }
        .ov-sum > div { padding: 16px 18px; border-left: 1px solid var(--line-2); }
        .ov-sum > div:first-child { border-left: 0; }
        .ov-sum .k { color: var(--mute); font-size: 13px; }
        .ov-sum .v { font-size: 22px; font-weight: 600; margin-top: 4px; white-space: nowrap; letter-spacing: -.01em; }
        .ov-sum > div:first-child .v { font-size: 28px; }
        .ov-sum .v .c { font-size: 13px; color: var(--mute); font-weight: 500; margin-left: 4px; letter-spacing: 0; }
        .ov-sum .n { font-size: 12.5px; color: var(--mute); margin-top: 4px; }
        .pos { color: var(--pos); }
        .neg { color: var(--neg); }

        .ov-dl { margin: 0; }
        .ov-dl > div { display: flex; justify-content: space-between; gap: 12px; padding: 9px 0; border-top: 1px solid var(--line-2); }
        .ov-dl > div:first-child { border-top: 0; }
        .ov-dl dt { font-weight: 400; color: var(--mute); }
        .ov-dl dd { margin: 0; font-weight: 600; text-align: right; }
        .ov-big { font-size: 30px; font-weight: 600; letter-spacing: -.01em; line-height: 1.1; }
        .ov-sub { color: var(--mute); margin-top: 4px; }
        .ov-sep { border: 0; border-top: 1px solid var(--line); margin: 14px 0 6px; }
        .ov-cap { font-size: 12px; color: var(--mute); font-weight: 600; margin-top: 4px; }

        .ov-t { width: 100%; border-collapse: collapse; }
        .ov-t th { font-weight: 500; color: var(--mute); font-size: 12.5px; text-align: left; padding: 9px 12px; border-bottom: 1px solid var(--line); white-space: nowrap; background: #fafbfc; }
        .ov-t td { padding: 9px 12px; border-bottom: 1px solid var(--line-2); white-space: nowrap; }
        .ov-t tbody tr:hover td { background: #f8f9fb; }
        .ov-t .r { text-align: right; }
        .ov-t tfoot td { font-weight: 600; border-top: 1px solid var(--line); border-bottom: 0; background: #fafbfc; }
        .ov-t tr.dim td { color: #b3b8bf; }
        .ov-t tr.now td:first-child { box-shadow: inset 3px 0 0 var(--accent); font-weight: 600; }
        .ov-t .co { max-width: 210px; overflow: hidden; text-overflow: ellipsis; }
        .ov-share { display: inline-block; width: 36px; height: 4px; background: var(--line-2); vertical-align: middle; margin-left: 8px; }
        .ov-share i { display: block; height: 100%; background: var(--accent); }
        .ov-empty { color: var(--mute); text-align: center; padding: 22px 0; }

        @media (max-width: 1199.98px) {
            .ov-sum { grid-template-columns: repeat(2, 1fr); }
            .ov-sum > div { border-left: 0; border-top: 1px solid var(--line-2); }
            .ov-sum > div:first-child { grid-column: 1 / -1; border-top: 0; }
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
        $t = $dash['totals'];
        $mo = $dash['month'];
        $f2 = fn ($v) => number_format($v, 2);
        $f0 = fn ($v) => number_format($v, 0);
        $pct = fn ($a, $b) => $b > 0 ? ($a - $b) / $b * 100 : null;
        $moChange = $pct($mo['total'], $mo['last']);
        $year = $dash['year'];
        $past = $dash['months']->reject(fn ($m) => $m['future']);
    @endphp

    <div class="container-fluid ov">
        <div class="ov-head">
            <div>
                <h1>Sales overview</h1>
                <p>Tax invoices issued in {{ $year }} · amounts in {{ $cur }} incl. 5% VAT unless stated</p>
            </div>
            <div class="ov-tools">
                <div class="ov-years">
                    @foreach ($dash['years']->take(4)->sort() as $y)
                        <a href="{{ route('/', ['year' => $y]) }}" class="{{ $y === $year ? 'on' : '' }}">{{ $y }}</a>
                    @endforeach
                </div>
                <a class="ov-link" href="{{ route('statement.index') }}">Statement of accounts</a>
                <a class="ov-link" href="{{ route('invoice.create') }}">New invoice</a>
            </div>
        </div>

        {{-- Year summary --}}
        <div class="ov-panel">
            <div class="ov-sum">
                <div>
                    <div class="k">Sales {{ $year }}</div>
                    <div class="v fig">{{ $f2($t['total']) }}<span class="c">{{ $cur }}</span></div>
                    <div class="n">
                        @if ($t['change'] === null)
                            No sales in {{ $dash['prev_label'] }} to compare
                        @else
                            <span class="{{ $t['change'] >= 0 ? 'pos' : 'neg' }}">{{ $t['change'] >= 0 ? '+' : '−' }}{{ number_format(abs($t['change']), 1) }}%</span>
                            on {{ $dash['prev_label'] }} ({{ $f0($t['prev']) }})
                        @endif
                    </div>
                </div>
                <div>
                    <div class="k">Without VAT</div>
                    <div class="v fig">{{ $f2($t['net']) }}</div>
                    <div class="n">{{ $t['count'] }} invoices</div>
                </div>
                <div>
                    <div class="k">VAT</div>
                    <div class="v fig">{{ $f2($t['vat']) }}</div>
                    <div class="n">Output tax for the year</div>
                </div>
                <div>
                    <div class="k">Credit notes</div>
                    <div class="v fig">{{ $t['credits'] > 0 ? '−' : '' }}{{ $f2($t['credits']) }}</div>
                    <div class="n">{{ $t['credits_count'] }} {{ \Illuminate\Support\Str::plural('note', $t['credits_count']) }}</div>
                </div>
                <div>
                    <div class="k">After credit notes</div>
                    <div class="v fig">{{ $f2($t['after']) }}</div>
                    <div class="n">Average invoice {{ $f0($t['average']) }}</div>
                </div>
            </div>
        </div>

        <div class="ov-row">
            <div class="col-xl-8">
                <div class="ov-panel">
                    <div class="ov-panel-h">
                        <h2>Monthly sales, {{ $year }}</h2>
                        <span>Incl. VAT</span>
                    </div>
                    <div class="ov-panel-b" style="padding-top: 6px;">
                        <div id="ov-chart" dir="ltr" style="min-height: 370px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="ov-panel">
                    <div class="ov-panel-h">
                        <h2>{{ $mo['name'] }}</h2>
                        <span>Month to date</span>
                    </div>
                    <div class="ov-panel-b">
                        <div class="ov-big fig">{{ $f2($mo['total']) }} <span style="font-size: 14px; color: var(--mute); font-weight: 500;">{{ $cur }}</span></div>
                        <div class="ov-sub">
                            @if ($moChange === null)
                                Nothing invoiced in {{ $mo['last_name'] }}
                            @else
                                <span class="{{ $moChange >= 0 ? 'pos' : 'neg' }}">{{ $moChange >= 0 ? '+' : '−' }}{{ number_format(abs($moChange), 1) }}%</span> on {{ $mo['last_name'] }}
                            @endif
                        </div>
                        <dl class="ov-dl mt-3">
                            <div><dt>Invoices</dt><dd class="fig">{{ $mo['count'] }}</dd></div>
                            <div><dt>Companies invoiced</dt><dd class="fig">{{ $mo['companies'] }}</dd></div>
                            <div><dt>{{ $mo['last_name'] }} total</dt><dd class="fig">{{ $f2($mo['last']) }}</dd></div>
                        </dl>
                        <hr class="ov-sep">
                        <div class="ov-cap">{{ $year }} so far</div>
                        <dl class="ov-dl">
                            <div><dt>Active companies</dt><dd class="fig">{{ $dash['companies_active'] }} <span style="color: var(--mute); font-weight: 400;">of {{ $dash['companies_all'] }}</span></dd></div>
                            <div><dt>Average invoice</dt><dd class="fig">{{ $f2($t['average']) }}</dd></div>
                            @php $best = $past->sortByDesc('total')->first(); @endphp
                            <div><dt>Best month</dt><dd>{{ $best && $best['total'] > 0 ? $best['name'].' · '.$f0($best['total']) : '—' }}</dd></div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="ov-row">
            <div class="col-xl-7">
                <div class="ov-panel">
                    <div class="ov-panel-h">
                        <h2>Month by month</h2>
                        <span>{{ $year }}</span>
                    </div>
                    <div class="table-responsive">
                        <table class="ov-t fig">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th class="r">Invoices</th>
                                    <th class="r">Without VAT</th>
                                    <th class="r">VAT</th>
                                    <th class="r">Total</th>
                                    <th class="r">Credit notes</th>
                                    <th class="r">Net</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dash['months'] as $m)
                                    <tr class="{{ $m['future'] ? 'dim' : '' }} {{ $m['current'] ? 'now' : '' }}">
                                        <td>{{ $m['name'] }}</td>
                                        @if ($m['future'])
                                            <td class="r">—</td><td class="r">—</td><td class="r">—</td><td class="r">—</td><td class="r">—</td><td class="r">—</td>
                                        @else
                                            <td class="r">{{ $m['count'] }}</td>
                                            <td class="r">{{ $f2($m['net']) }}</td>
                                            <td class="r">{{ $f2($m['vat']) }}</td>
                                            <td class="r">{{ $f2($m['total']) }}</td>
                                            <td class="r {{ $m['credits'] > 0 ? 'neg' : '' }}">{{ $m['credits'] > 0 ? '−'.$f2($m['credits']) : '0.00' }}</td>
                                            <td class="r" style="font-weight: 600;">{{ $f2($m['after']) }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>Total</td>
                                    <td class="r">{{ $t['count'] }}</td>
                                    <td class="r">{{ $f2($t['net']) }}</td>
                                    <td class="r">{{ $f2($t['vat']) }}</td>
                                    <td class="r">{{ $f2($t['total']) }}</td>
                                    <td class="r {{ $t['credits'] > 0 ? 'neg' : '' }}">{{ $t['credits'] > 0 ? '−'.$f2($t['credits']) : '0.00' }}</td>
                                    <td class="r">{{ $f2($t['after']) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-xl-5">
                <div class="ov-panel">
                    <div class="ov-panel-h">
                        <h2>Top companies</h2>
                        <span>{{ $year }} · by sales</span>
                    </div>
                    <div class="table-responsive">
                        <table class="ov-t fig">
                            <thead>
                                <tr>
                                    <th>Company</th>
                                    <th class="r">Inv.</th>
                                    <th class="r">Sales</th>
                                    <th class="r">Share</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($dash['companies'] as $c)
                                    @php $share = $t['total'] > 0 ? $c['total'] / $t['total'] * 100 : 0; @endphp
                                    <tr>
                                        <td class="co" title="{{ $c['name'] }}">{{ $c['name'] }}</td>
                                        <td class="r">{{ $c['count'] }}</td>
                                        <td class="r">{{ $f2($c['total']) }}</td>
                                        <td class="r">{{ number_format($share, 1) }}%<span class="ov-share"><i style="width: {{ min(100, round($share)) }}%"></i></span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="ov-empty">No invoices in {{ $year }}.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="ov-panel">
            <div class="ov-panel-h">
                <h2>Latest invoices</h2>
                <a href="{{ route('invoice.index') }}">All invoices</a>
            </div>
            <div class="table-responsive">
                <table class="ov-t fig">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Date</th>
                            <th>Company</th>
                            <th class="r">Without VAT</th>
                            <th class="r">VAT</th>
                            <th class="r">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dash['latest'] as $inv)
                            <tr>
                                <td><a href="{{ route('invoice.show', $inv['id']) }}">{{ $inv['number'] }}</a></td>
                                <td>{{ $inv['date']->format('d/m/Y') }}</td>
                                <td class="co" title="{{ $inv['company'] }}">{{ $inv['company'] }}</td>
                                <td class="r">{{ $f2($inv['net']) }}</td>
                                <td class="r">{{ $f2($inv['vat']) }}</td>
                                <td class="r" style="font-weight: 600;">{{ $f2($inv['total']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="ov-empty">No invoices yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        (function () {
            var el = document.getElementById('ov-chart');
            if (!el || typeof ApexCharts === 'undefined') {
                return;
            }
            var months = @json($dash['months']);
            var cur = @json($cur);
            var money = function (v) {
                return Number(v).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ' + cur;
            };
            new ApexCharts(el, {
                chart: { type: 'bar', height: 370, toolbar: { show: false }, fontFamily: '"IBM Plex Sans", "Segoe UI", sans-serif', animations: { enabled: false } },
                series: [{ name: 'Sales', data: months.map(function (m) { return m.future ? null : m.total; }) }],
                colors: [function (o) { return months[o.dataPointIndex].current ? '#1c4fa1' : '#9db6dd'; }],
                plotOptions: { bar: { columnWidth: '52%', borderRadius: 0 } },
                dataLabels: { enabled: false },
                legend: { show: false },
                states: { hover: { filter: { type: 'darken', value: .85 } }, active: { filter: { type: 'none' } } },
                xaxis: {
                    categories: months.map(function (m) { return m.label; }),
                    axisBorder: { color: '#d5d9de' }, axisTicks: { show: false },
                    labels: { style: { colors: '#6b7280', fontSize: '12px' } }
                },
                yaxis: {
                    tickAmount: 4,
                    labels: {
                        style: { colors: '#6b7280', fontSize: '12px' },
                        formatter: function (v) { return v >= 1000 ? Math.round(v / 1000) + 'k' : Math.round(v); }
                    }
                },
                grid: { borderColor: '#eef0f2', padding: { left: 0, right: 0 } },
                tooltip: {
                    y: { formatter: function (v) { return money(v); } },
                    x: {
                        formatter: function (v, o) {
                            var m = months[o.dataPointIndex];
                            return m.name + ' · ' + m.count + ' invoices';
                        }
                    }
                }
            }).render();
        })();
    </script>
@endsection
