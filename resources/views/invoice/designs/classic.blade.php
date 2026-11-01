@php
    /**
     * @var array $doc  built by App\Support\InvoiceDocument::make()
     * @var string|null $logoSrc  optional URL used instead of the embedded logo (browser pages)
     */
    $s = $doc['seller'];
    $c = $doc['customer'];
    $t = $doc['totals'];
    $cur = $doc['currency'];
    $logo = $logoSrc ?? $doc['logo'];
    $m = fn ($v) => number_format((float) $v, 2);
    $pct = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.').'%';
    $hasDisc = $t['discount'] > 0;
    $lastLine = count($doc['lines']) - 1;
@endphp
<div class="inv-doc">

    <table class="head">
        <tr>
            <td class="seller">
                @if ($logo)<img src="{{ $logo }}" alt="MDM" style="height:62px; width:auto"><br>@endif
                <b style="font-size:11pt; color:#000">{{ $s['name'] }}</b><br>
                {{ $s['address'] }}<br>
                @if ($s['phone'])Tel: {{ $s['phone'] }} | @endif
                @if ($s['email'])E-Mail: {{ $s['email'] }} | @endif
                {{ $s['website'] }}<br>
                <b style="font-size:9.5pt; color:#000">TRN: {{ $s['trn'] }}</b>
            </td>
        </tr>
    </table>

    <table class="titlebar">
        <tr>
            <td style="width:33%" class="barcode">
                <img src="{{ $doc['barcode'] }}" alt="" style="height:34px"><br>{{ $doc['number'] }}
            </td>
            <td style="width:34%" class="title">TAX INVOICE<br><span style="font-size:10pt; letter-spacing:0">فاتورة ضريبية</span></td>
            <td style="width:33%"></td>
        </tr>
    </table>

    <table class="parties">
        <tr>
            <td class="col">
                <b style="font-size:9pt; text-decoration:underline">BILL TO:</b><br>
                Customer Code: {{ $c['code'] }}<br>
                <b>{{ $c['name'] }}</b><br>
                @if ($c['address']){{ $c['address'] }}<br>@endif
                @if ($c['phone'])Tel: {{ $c['phone'] }}<br>@endif
                @if ($c['trn'])<b>TRN: {{ $c['trn'] }}</b><br>@endif
                @if ($doc['notes'])<b>{!! nl2br(e($doc['notes'])) !!}</b>@endif
            </td>
            <td class="col">
                <b style="font-size:9pt; text-decoration:underline">SHIP TO:</b><br>
                <b>{{ $c['name'] }}</b><br>
                @if ($c['address']){{ $c['address'] }}<br>@endif
                @if ($c['phone'])Tel: {{ $c['phone'] }}@endif
            </td>
            <td class="col">
                <table class="meta">
                    <tr><td class="k">Invoice Number</td><td>: {{ $doc['number'] }}</td></tr>
                    <tr><td class="k">Invoice Date</td><td>: {{ $doc['date']?->format('d/m/Y') }}</td></tr>
                    <tr><td class="k">Supply Period</td><td>: {{ $doc['period_from']?->format('d/m/Y') }}@if ($doc['period_from'] && $doc['period_to'] && ! $doc['period_to']->isSameDay($doc['period_from'])) – {{ $doc['period_to']->format('d/m/Y') }}@endif</td></tr>
                    <tr><td class="k">Currency</td><td>: {{ $cur }}</td></tr>
                    @if ($hasDisc)<tr><td class="k">Customer Discount</td><td>: {{ $pct($c['discount']) }}</td></tr>@endif
                    <tr><td class="k">Payment Status</td><td>: {{ ucfirst($doc['status']) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width:5%; white-space:nowrap">S.No</th>
                <th style="width:{{ $hasDisc ? 26 : 40 }}%">ITEM DESCRIPTION</th>
                <th style="width:7%">Qty</th>
                <th style="width:8%">Unit Rate<br>({{ $cur }})</th>
                <th style="width:9%">Gross<br>Amount</th>
                @if ($hasDisc)
                    <th style="width:6%">Disc<br>%</th>
                    <th style="width:8%">Disc<br>Amount</th>
                @endif
                <th style="width:9%">Taxable<br>Amount</th>
                <th style="width:5%">VAT<br>%</th>
                <th style="width:8%">VAT<br>Amount</th>
                <th style="width:10%">TOTAL<br>({{ $cur }})</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($doc['lines'] as $i => $l)
                @php $edge = $i === $lastLine ? ' last' : ''; @endphp
                <tr>
                    <td class="c{{ $edge }}">{{ $l['no'] }}</td>
                    <td class="l{{ $edge }}">
                        <b style="font-size:8.8pt">{{ $l['name'] }}</b>
                        @if ($l['free'])<br><span style="font-size:7.4pt; color:#444">Free of charge: {{ $l['free'] }} unit(s)</span>@endif
                    </td>
                    <td class="c{{ $edge }}">{{ $l['qty'] }}@if ($l['free']) + {{ $l['free'] }}@endif</td>
                    <td class="{{ $edge }}">{{ $m($l['unit']) }}</td>
                    <td class="{{ $edge }}">{{ $m($l['gross']) }}</td>
                    @if ($hasDisc)
                        <td class="c{{ $edge }}">{{ $pct($l['disc_pct']) }}</td>
                        <td class="{{ $edge }}">{{ $m($l['discount']) }}</td>
                    @endif
                    <td class="{{ $edge }}">{{ $m($l['taxable']) }}</td>
                    <td class="c{{ $edge }}">{{ $pct($l['vat_rate']) }}</td>
                    <td class="{{ $edge }}">{{ $m($l['vat']) }}</td>
                    <td class="{{ $edge }}"><b>{{ $m($l['total']) }}</b></td>
                </tr>
            @empty
                <tr><td class="c last" colspan="{{ $hasDisc ? 11 : 9 }}"><em>No line items for this invoice period.</em></td></tr>
            @endforelse
        </tbody>
    </table>

    <table>
        <tr>
            <td style="width:52%" class="counts">
                Total Quantity : {{ $t['qty'] }}@if ($t['free']) + {{ $t['free'] }} FOC @endif<br>
                No. of Items : {{ count($doc['lines']) }}
            </td>
            <td style="width:48%">
                <table class="tot">
                    <tr><td class="k">Gross Amount ({{ $cur }})</td><td style="width:34%">{{ $m($t['gross']) }}</td></tr>
                    @if ($hasDisc)<tr><td class="k">Discount ({{ $cur }})</td><td>- {{ $m($t['discount']) }}</td></tr>@endif
                    @foreach ($doc['by_rate'] as $rate => $amount)
                        <tr><td class="k">{{ $rate }}% Rated Sales ({{ $cur }})</td><td>{{ $m($amount) }}</td></tr>
                    @endforeach
                    <tr><td class="k">VAT Amount ({{ $cur }})</td><td>{{ $m($t['vat']) }}</td></tr>
                    <tr><td class="grand">Total ({{ $cur }})</td><td class="grand">{{ $m($t['total']) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="words">Amount in words: <b>{{ $doc['words'] }}</b></div>

    <table class="sign">
        <tr>
            <td class="line">Prepared by</td><td class="gap"></td>
            <td class="line">Checked by</td><td class="gap"></td>
            <td class="line">Delivered by</td><td class="gap"></td>
            <td class="line">Customer Signature &amp; Stamp</td>
        </tr>
    </table>

    <div class="foot">This is a computer-generated tax invoice. &nbsp;|&nbsp; {{ $s['name'] }} &nbsp;|&nbsp; TRN {{ $s['trn'] }} &nbsp;|&nbsp; {{ $s['website'] }}</div>
</div>
