@php
    $s = $doc['seller'];
    $c = $doc['customer'];
    $t = $doc['totals'];
    $cur = $doc['currency'];
    $m = fn ($v) => number_format((float) $v, 2);
    $pct = fn ($v) => rtrim(rtrim(number_format((float) $v, 2), '0'), '.').'%';
    $hasDisc = $t['discount'] > 0;
    $ink = '#0f172a';
    $accent = '#2f6fd6';
    $paid = $doc['status'] === 'paid';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Tax Invoice {{ $doc['number'] }}</title>
<style>
    @page { size: A4; margin: 14mm 14mm 16mm; }
    @media screen { body { width: 210mm; margin: 0 auto; padding: 14mm; } }
    * { box-sizing: border-box; }
    body { margin: 0; font-family: "Segoe UI", Roboto, Arial, sans-serif; font-size: 9.3pt; color: #334155; }
    table { border-collapse: collapse; width: 100%; }
    td, th { vertical-align: top; }

    .top td { vertical-align: top; }
    .logo { height: 58px; }
    .brand-meta { margin-top: 6px; font-size: 8pt; line-height: 1.5; color: #64748b; }
    .brand-meta b { color: {{ $ink }}; }
    .title { text-align: right; }
    .title .en { font-size: 30pt; font-weight: 300; letter-spacing: 1px; color: {{ $ink }}; line-height: 1; }
    .title .en b { font-weight: 800; }
    .title .ar { font-size: 12pt; font-weight: 700; color: {{ $accent }}; margin-top: 4px; }
    .stamp { display: inline-block; margin-top: 10px; padding: 4px 12px; border: 2px solid {{ $paid ? '#16a34a' : '#dc2626' }}; color: {{ $paid ? '#16a34a' : '#dc2626' }}; border-radius: 4px; font-weight: 800; letter-spacing: 3px; font-size: 10pt; transform: rotate(-4deg); }

    .rule { height: 2px; background: {{ $ink }}; margin: 8mm 0 0; }
    .rule-accent { width: 32mm; height: 2px; background: {{ $accent }}; margin-top: -2px; }

    .info { margin-top: 6mm; }
    .info > tbody > tr > td { padding-right: 6mm; }
    .info > tbody > tr > td:last-child { padding-right: 0; }
    .k { font-size: 7pt; font-weight: 800; letter-spacing: 1.8px; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px; }
    .v { font-size: 9pt; line-height: 1.55; color: {{ $ink }}; }
    .v .big { font-size: 11pt; font-weight: 800; }
    .grid td { padding: 0 0 6px; }

    .items { margin-top: 8mm; }
    .items th { padding: 0 6px 7px; font-size: 7pt; font-weight: 800; letter-spacing: 1.3px; text-transform: uppercase; color: #94a3b8; text-align: right; border-bottom: 1.5px solid {{ $ink }}; }
    .items th.l, .items td.l { text-align: left; }
    .items th.c, .items td.c { text-align: center; }
    .items td { padding: 9px 6px; text-align: right; font-size: 8.8pt; border-bottom: 1px solid #e2e8f0; color: {{ $ink }}; }
    .items .n { color: #94a3b8; font-size: 8pt; }
    .items .desc b { font-size: 9.4pt; }
    .items .desc span { display: block; font-size: 7.6pt; color: {{ $accent }}; font-weight: 700; margin-top: 2px; }
    .items .muted { color: #64748b; }

    .bottom { margin-top: 6mm; }
    .bottom > tbody > tr > td { vertical-align: top; }
    .words { font-size: 8.6pt; line-height: 1.55; color: #475569; }
    .words b { color: {{ $ink }}; }
    .bars { margin-top: 10px; }
    .tot td { padding: 5px 0; font-size: 9pt; }
    .tot td.r { text-align: right; color: {{ $ink }}; font-weight: 600; }
    .tot tr.sep td { border-top: 1px solid #e2e8f0; }
    .due { margin-top: 8px; background: {{ $ink }}; color: #fff; padding: 12px 14px; }
    .due .k { color: #94a3b8; margin: 0; }
    .due .amt { font-size: 21pt; font-weight: 800; letter-spacing: -.5px; line-height: 1.15; }
    .due .amt small { font-size: 10pt; font-weight: 600; color: #94a3b8; }

    .terms { margin-top: 9mm; padding-top: 5mm; border-top: 1px solid #e2e8f0; font-size: 7.6pt; line-height: 1.6; color: #64748b; }
    .terms ol { margin: 4px 0 0; padding-left: 14px; }

    .sign { margin-top: 15mm; }
    .sign td { width: 33.33%; padding-right: 10mm; font-size: 7.4pt; font-weight: 800; letter-spacing: 1.4px; color: #94a3b8; text-transform: uppercase; }
    .sign td:last-child { padding-right: 0; }
    .sign .line { border-top: 1px solid {{ $ink }}; padding-top: 5px; }

    .foot { margin-top: 10mm; font-size: 7.4pt; color: #94a3b8; }
    .foot td:last-child { text-align: right; }
</style>
</head>
<body>

<table class="top">
    <tr>
        <td style="width:55%">
            @if ($doc['logo'])<img class="logo" src="{{ $doc['logo'] }}" alt="MDM">@endif
            <div class="brand-meta">
                <b>{{ $s['name'] }}</b><br>
                {{ $s['address'] }}<br>
                TRN {{ $s['trn'] }} &nbsp;·&nbsp; {{ $s['website'] }}@if ($s['phone']) &nbsp;·&nbsp; {{ $s['phone'] }}@endif
            </div>
        </td>
        <td class="title">
            <div class="en">TAX <b>INVOICE</b></div>
            <div class="ar">فاتورة ضريبية</div>
            <span class="stamp">{{ strtoupper($doc['status'] ?: 'issued') }}</span>
        </td>
    </tr>
</table>

<div class="rule"></div>
<div class="rule-accent"></div>

<table class="info">
    <tr>
        <td style="width:38%">
            <div class="k">Billed to</div>
            <div class="v">
                <span class="big">{{ $c['name'] }}</span><br>
                @if ($c['address']){{ $c['address'] }}<br>@endif
                @if ($c['phone']){{ $c['phone'] }}<br>@endif
                TRN {{ $c['trn'] ?: '—' }}
            </div>
        </td>
        <td style="width:34%">
            <table class="grid">
                <tr><td style="width:50%"><div class="k">Invoice no.</div><div class="v"><b>{{ $doc['number'] }}</b></div></td>
                    <td><div class="k">Date</div><div class="v"><b>{{ $doc['date']?->format('d M Y') }}</b></div></td></tr>
                <tr><td><div class="k">Customer no.</div><div class="v">{{ $c['code'] }}</div></td>
                    <td><div class="k">Supply date</div><div class="v">{{ $doc['period_from']?->format('d M Y') }}</div></td></tr>
            </table>
        </td>
        <td style="width:28%; text-align:right">
            <img src="{{ $doc['barcode'] }}" alt="" style="height:36px"><br>
            <span style="font-size:7.5pt; letter-spacing:2px; color:#64748b">{{ $doc['number'] }}</span>
        </td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th class="l" style="width:5%">No</th>
            <th class="l" style="width:31%">Description</th>
            <th class="c" style="width:7%">Qty</th>
            <th style="width:10%">Unit price</th>
            @if ($hasDisc)<th style="width:11%">Discount</th>@endif
            <th style="width:11%">Taxable</th>
            <th style="width:12%">VAT</th>
            <th style="width:13%">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($doc['lines'] as $l)
            <tr>
                <td class="l n">{{ str_pad($l['no'], 2, '0', STR_PAD_LEFT) }}</td>
                <td class="l desc"><b>{{ $l['name'] }}</b>@if ($l['free'])<span>+ {{ $l['free'] }} units free of charge</span>@endif</td>
                <td class="c">{{ $l['qty'] }}</td>
                <td class="muted">{{ $m($l['unit']) }}</td>
                @if ($hasDisc)<td class="muted">{{ $l['discount'] > 0 ? '-'.$m($l['discount']) : '—' }}</td>@endif
                <td>{{ $m($l['taxable']) }}</td>
                <td class="muted">{{ $m($l['vat']) }} <span style="font-size:7pt">({{ $pct($l['vat_rate']) }})</span></td>
                <td><b>{{ $m($l['total']) }}</b></td>
            </tr>
        @endforeach
    </tbody>
</table>

<table class="bottom">
    <tr>
        <td style="width:52%; padding-right:10mm">
            <div class="k">Amount in words</div>
            <div class="words"><b>{{ $doc['words'] }}</b></div>
            <div class="bars">
                <div class="k" style="margin-top:12px">Summary</div>
                @php
                    $summary = [count($doc['lines']).' item(s)', $t['qty'].' unit(s)'];
                    if ($t['free']) $summary[] = $t['free'].' free unit(s)';
                    if ($hasDisc) $summary[] = 'Customer discount '.$pct($c['discount']);
                @endphp
                <div class="words">{{ implode(' · ', $summary) }}</div>
            </div>
        </td>
        <td style="width:48%">
            <table class="tot">
                <tr><td>Gross amount</td><td class="r">{{ $m($t['gross']) }}</td></tr>
                @if ($hasDisc)<tr><td>Discount</td><td class="r">- {{ $m($t['discount']) }}</td></tr>@endif
                @foreach ($doc['by_rate'] as $rate => $amount)
                    <tr class="sep"><td>{{ $rate }}% rated sales</td><td class="r">{{ $m($amount) }}</td></tr>
                @endforeach
                <tr><td>VAT amount</td><td class="r">{{ $m($t['vat']) }}</td></tr>
            </table>
            <div class="due">
                <div class="k">Total amount due</div>
                <div class="amt"><small>{{ $cur }}</small> {{ $m($t['total']) }}</div>
            </div>
        </td>
    </tr>
</table>

<div class="terms">
    <div class="k">Terms &amp; conditions</div>
    <ol>@foreach ($doc['terms'] as $term)<li>{{ $term }}</li>@endforeach</ol>
</div>

<table class="sign">
    <tr>
        <td><div class="line">Authorised signature</div></td>
        <td><div class="line">Delivered by</div></td>
        <td><div class="line">Received by · sign &amp; stamp</div></td>
    </tr>
</table>

<table class="foot">
    <tr>
        <td>{{ $s['name'] }} · TRN {{ $s['trn'] }} · {{ $s['website'] }}</td>
        <td>Computer-generated tax invoice · {{ $doc['number'] }}</td>
    </tr>
</table>

</body>
</html>
