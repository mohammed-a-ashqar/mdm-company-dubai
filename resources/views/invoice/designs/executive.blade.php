@php
    $s = $doc['seller'];
    $c = $doc['customer'];
    $t = $doc['totals'];
    $cur = $doc['currency'];
    $m = fn ($v) => number_format((float) $v, 2);
    $pct = fn ($v) => rtrim(rtrim(number_format((float) $v, 2), '0'), '.').'%';
    $hasDisc = $t['discount'] > 0;
    $navy = '#0b1f45';
    $blue = '#2f6fd6';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Tax Invoice {{ $doc['number'] }}</title>
<style>
    @page { size: A4; margin: 0; }
    * { box-sizing: border-box; }
    body { margin: 0; font-family: "Segoe UI", Roboto, Arial, sans-serif; font-size: 9.4pt; color: #1b2433; }
    table { border-collapse: collapse; width: 100%; }
    td, th { vertical-align: top; }

    .band { background: {{ $navy }}; color: #fff; }
    .band td { padding: 0; }
    .band-inner { padding: 9mm 12mm 7mm; }
    .logo-box { background: #fff; padding: 8px 12px; border-radius: 6px; display: inline-block; }
    .logo { height: 52px; display: block; }
    .doc-title { text-align: right; }
    .doc-title .en { font-size: 25pt; font-weight: 800; letter-spacing: 3px; line-height: 1; }
    .doc-title .ar { font-size: 13pt; font-weight: 700; color: #9cc0ff; margin-top: 4px; }
    .doc-title .no { margin-top: 8px; font-size: 9.5pt; color: #c9d6f0; }
    .doc-title .no b { color: #fff; font-size: 11pt; }
    .stripe { height: 5px; background: {{ $blue }}; }

    .wrap { padding: 6mm 12mm 0; }
    .seller-line { font-size: 8.3pt; color: #4a5568; padding-bottom: 6px; border-bottom: 1px solid #dfe5ef; }
    .seller-line b { color: {{ $navy }}; }

    .cards { margin-top: 6mm; }
    .cards > tbody > tr > td { width: 33.33%; padding: 0 4px; }
    .cards > tbody > tr > td:first-child { padding-left: 0; }
    .cards > tbody > tr > td:last-child { padding-right: 0; }
    .card { border: 1px solid #dfe5ef; border-top: 3px solid {{ $blue }}; padding: 8px 10px; min-height: 31mm; font-size: 8.6pt; line-height: 1.5; }
    .card .h { font-size: 7.4pt; font-weight: 800; letter-spacing: 1.6px; color: {{ $blue }}; margin-bottom: 4px; }
    .card .name { font-size: 10pt; font-weight: 800; color: {{ $navy }}; }
    .kv td { padding: 1.4px 0; font-size: 8.5pt; }
    .kv td.k { color: #6b778c; width: 46%; }
    .kv td.v { font-weight: 700; color: {{ $navy }}; text-align: right; }

    .items { margin-top: 6mm; }
    .items th { background: {{ $navy }}; color: #fff; padding: 7px 5px; font-size: 7.5pt; font-weight: 700; text-align: right; letter-spacing: .3px; }
    .items th.l, .items td.l { text-align: left; }
    .items th.c, .items td.c { text-align: center; }
    .items td { padding: 7px 5px; font-size: 8.5pt; text-align: right; border-bottom: 1px solid #e6ebf3; }
    .items tbody tr:nth-child(even) td { background: #f6f8fc; }
    .items .desc b { color: {{ $navy }}; font-size: 8.9pt; }
    .items .foc { display: inline-block; margin-top: 2px; padding: 1px 6px; border-radius: 8px; background: #e6f0ff; color: {{ $blue }}; font-size: 7pt; font-weight: 700; }

    .after { margin-top: 5mm; }
    .after > tbody > tr > td { vertical-align: top; }
    .words { background: #f6f8fc; border-left: 3px solid {{ $blue }}; padding: 8px 11px; font-size: 8.6pt; line-height: 1.5; }
    .words .k { font-size: 7.3pt; font-weight: 800; letter-spacing: 1.4px; color: #6b778c; }
    .words b { color: {{ $navy }}; }
    .stats { margin-top: 8px; font-size: 8.4pt; color: #4a5568; }
    .tot td { padding: 5px 9px; font-size: 8.9pt; border-bottom: 1px solid #e6ebf3; }
    .tot td.v { text-align: right; font-weight: 700; color: {{ $navy }}; }
    .tot tr.grand td { background: {{ $navy }}; color: #fff; font-size: 11.5pt; font-weight: 800; padding: 9px; border: 0; }
    .tot tr.grand td.v { color: #fff; }

    .terms { margin-top: 6mm; font-size: 7.5pt; color: #4a5568; line-height: 1.55; }
    .terms .h { font-size: 7.4pt; font-weight: 800; letter-spacing: 1.4px; color: {{ $navy }}; margin-bottom: 2px; }
    .terms ol { margin: 0; padding-left: 14px; }

    .sign { margin-top: 15mm; }
    .sign td { width: 25%; padding: 0 7px; text-align: center; font-size: 8pt; color: #4a5568; font-weight: 700; }
    .sign .line { border-top: 1.2px solid {{ $navy }}; padding-top: 5px; }

    .foot { margin-top: 8mm; background: {{ $navy }}; color: #c9d6f0; font-size: 7.6pt; text-align: center; padding: 7px 12mm; }
    .foot b { color: #fff; }
</style>
</head>
<body>

<div class="band">
    <div class="band-inner">
        <table>
            <tr>
                <td style="width:50%; vertical-align:middle">
                    @if ($doc['logo'])<span class="logo-box"><img class="logo" src="{{ $doc['logo'] }}" alt="MDM"></span>@endif
                </td>
                <td class="doc-title">
                    <div class="en">TAX INVOICE</div>
                    <div class="ar">فاتورة ضريبية</div>
                    <div class="no">No. <b>{{ $doc['number'] }}</b></div>
                </td>
            </tr>
        </table>
    </div>
    <div class="stripe"></div>
</div>

<div class="wrap">
    <table class="seller-line">
        <tr>
            <td><b>{{ $s['name'] }}</b> &nbsp;·&nbsp; {{ $s['address'] }}@if ($s['phone']) &nbsp;·&nbsp; {{ $s['phone'] }}@endif @if ($s['email']) &nbsp;·&nbsp; {{ $s['email'] }}@endif</td>
            <td style="text-align:right; white-space:nowrap; width:36%"><b>TRN {{ $s['trn'] }}</b><br><img src="{{ $doc['barcode'] }}" alt="" style="height:26px; margin-top:3px"></td>
        </tr>
    </table>

    <table class="cards">
        <tr>
            <td>
                <div class="card">
                    <div class="h">BILL TO</div>
                    <div class="name">{{ $c['name'] }}</div>
                    @if ($c['address']){{ $c['address'] }}<br>@endif
                    @if ($c['phone'])Tel: {{ $c['phone'] }}<br>@endif
                    <b>TRN:</b> {{ $c['trn'] ?: '—' }}
                </div>
            </td>
            <td>
                <div class="card">
                    <div class="h">SHIP TO</div>
                    <div class="name">{{ $c['name'] }}</div>
                    {{ $c['address'] ?: 'Same as billing address' }}<br>
                    <b>Customer No:</b> {{ $c['code'] }}
                </div>
            </td>
            <td>
                <div class="card">
                    <div class="h">INVOICE DETAILS</div>
                    <table class="kv">
                        <tr><td class="k">Invoice Date</td><td class="v">{{ $doc['date']?->format('d M Y') }}</td></tr>
                        <tr><td class="k">Supply Date</td><td class="v">{{ $doc['period_from']?->format('d M Y') }}</td></tr>
                        <tr><td class="k">Currency</td><td class="v">{{ $cur }}</td></tr>
                        @if ($hasDisc)<tr><td class="k">Discount</td><td class="v">{{ $pct($c['discount']) }}</td></tr>@endif
                        <tr><td class="k">Status</td><td class="v">{{ strtoupper($doc['status']) }}</td></tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th class="c" style="width:5%">#</th>
                <th class="l" style="width:29%">Item Description</th>
                <th class="c" style="width:8%">Qty</th>
                <th style="width:9%">Unit Price</th>
                <th style="width:10%">Gross</th>
                @if ($hasDisc)<th style="width:10%">Discount</th>@endif
                <th style="width:10%">Taxable</th>
                <th class="c" style="width:6%">VAT</th>
                <th style="width:9%">VAT Amt</th>
                <th style="width:11%">Total {{ $cur }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($doc['lines'] as $l)
                <tr>
                    <td class="c">{{ str_pad($l['no'], 2, '0', STR_PAD_LEFT) }}</td>
                    <td class="l desc"><b>{{ $l['name'] }}</b>@if ($l['free'])<br><span class="foc">+{{ $l['free'] }} FREE</span>@endif</td>
                    <td class="c">{{ $l['qty'] }}</td>
                    <td>{{ $m($l['unit']) }}</td>
                    <td>{{ $m($l['gross']) }}</td>
                    @if ($hasDisc)<td>{{ $l['discount'] > 0 ? '-'.$m($l['discount']) : '—' }}<br><span style="font-size:7pt;color:#6b778c">{{ $pct($l['disc_pct']) }}</span></td>@endif
                    <td>{{ $m($l['taxable']) }}</td>
                    <td class="c">{{ $pct($l['vat_rate']) }}</td>
                    <td>{{ $m($l['vat']) }}</td>
                    <td><b style="color:{{ $navy }}">{{ $m($l['total']) }}</b></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="after">
        <tr>
            <td style="width:54%; padding-right:8mm">
                <div class="words"><span class="k">AMOUNT IN WORDS</span><br><b>{{ $doc['words'] }}</b></div>
                <div class="stats">Items: <b>{{ count($doc['lines']) }}</b> &nbsp;·&nbsp; Quantity: <b>{{ $t['qty'] }}</b>@if ($t['free']) &nbsp;·&nbsp; Free units: <b>{{ $t['free'] }}</b>@endif</div>
            </td>
            <td style="width:46%">
                <table class="tot">
                    <tr><td>Gross Amount</td><td class="v">{{ $m($t['gross']) }}</td></tr>
                    @if ($hasDisc)<tr><td>Discount</td><td class="v">- {{ $m($t['discount']) }}</td></tr>@endif
                    <tr><td>Taxable Amount</td><td class="v">{{ $m($t['taxable']) }}</td></tr>
                    @foreach ($doc['by_rate'] as $rate => $amount)
                        @if ((float) $rate > 0)<tr><td>VAT {{ $rate }}%</td><td class="v">{{ $m($amount * (float) $rate / 100) }}</td></tr>@endif
                    @endforeach
                    <tr class="grand"><td>TOTAL DUE ({{ $cur }})</td><td class="v">{{ $m($t['total']) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="terms">
        <div class="h">TERMS &amp; CONDITIONS</div>
        <ol>@foreach ($doc['terms'] as $term)<li>{{ $term }}</li>@endforeach</ol>
    </div>

    <table class="sign">
        <tr>
            <td><div class="line">Prepared by</div></td>
            <td><div class="line">Checked by</div></td>
            <td><div class="line">Delivered by</div></td>
            <td><div class="line">Received by (Sign &amp; Stamp)</div></td>
        </tr>
    </table>
</div>

<div class="foot">Thank you for your business &nbsp;·&nbsp; <b>{{ $s['name'] }}</b> &nbsp;·&nbsp; TRN {{ $s['trn'] }} &nbsp;·&nbsp; {{ $s['website'] }} &nbsp;·&nbsp; This is a computer-generated tax invoice.</div>

</body>
</html>
