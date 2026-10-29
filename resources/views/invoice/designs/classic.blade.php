@php
    /** @var array $doc  built by App\Support\InvoiceDocument::make() */
    $s = $doc['seller'];
    $c = $doc['customer'];
    $t = $doc['totals'];
    $cur = $doc['currency'];
    $m = fn ($v) => number_format((float) $v, 2);
    $hasDisc = $t['discount'] > 0;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Tax Invoice {{ $doc['number'] }}</title>
<style>
    @page { size: A4; margin: 12mm 11mm 14mm; }
    * { box-sizing: border-box; }
    body { margin: 0; font-family: "Segoe UI", Roboto, Arial, sans-serif; font-size: 9.5pt; color: #111; }
    .page { width: 188mm; margin: 0 auto; }
    table { border-collapse: collapse; width: 100%; }
    td, th { vertical-align: top; }

    .head td { padding: 0; }
    .logo { height: 62px; }
    .seller { text-align: right; font-size: 8.6pt; line-height: 1.45; color: #222; }
    .seller b { font-size: 11pt; color: #000; }
    .seller .ar { font-size: 10pt; font-weight: bold; }

    .titlebar { margin-top: 10px; border-top: 2.5px solid #000; border-bottom: 1px solid #000; }
    .titlebar td { padding: 6px 0; vertical-align: middle; }
    .title { font-size: 18pt; font-weight: 800; letter-spacing: 1px; text-align: center; }
    .title small { display: block; font-size: 10pt; font-weight: 700; letter-spacing: 0; }
    .barcode { text-align: center; font-size: 7.5pt; letter-spacing: 2px; }
    .pageno { text-align: right; font-weight: bold; font-size: 9pt; }

    .parties { margin-top: 8px; border: 1px solid #000; }
    .parties td { padding: 7px 9px; font-size: 8.8pt; line-height: 1.5; }
    .parties .col { width: 33%; border-right: 1px solid #000; }
    .parties .col:last-child { border-right: 0; }
    .lbl { font-weight: 800; text-decoration: underline; font-size: 9pt; display: block; margin-bottom: 3px; }
    .meta td { padding: 1.5px 0; font-size: 8.6pt; }
    .meta td.k { font-weight: 700; width: 46%; }

    .items { margin-top: 8px; }
    .items th { background: #f0f0f0; border: 1px solid #000; padding: 5px 4px; font-size: 7.6pt; text-align: center; font-weight: 800; line-height: 1.25; }
    .items td { border-left: 1px solid #000; border-right: 1px solid #000; padding: 5px 4px; font-size: 8.4pt; text-align: right; }
    .items tbody tr:last-child td { border-bottom: 1px solid #000; }
    .items td.l { text-align: left; }
    .items td.c { text-align: center; }
    .items .desc b { display: block; font-size: 8.8pt; }
    .items .desc span { font-size: 7.4pt; color: #444; }
    .items tfoot td { border: 1px solid #000; font-weight: 800; background: #fafafa; }

    .summary { margin-top: 0; }
    .summary td { vertical-align: top; }
    .counts { font-size: 8.4pt; padding: 6px 4px; font-style: italic; }
    .tot { border-collapse: collapse; }
    .tot td { border: 1px solid #000; padding: 4px 7px; font-size: 8.8pt; text-align: right; }
    .tot td.k { font-weight: 700; }
    .tot tr.grand td { font-size: 10.5pt; font-weight: 800; background: #000; color: #fff; }

    .words { margin-top: 6px; border: 1px solid #000; padding: 6px 9px; font-size: 8.8pt; }
    .words b { text-transform: uppercase; }
    .terms { margin-top: 6px; border: 1px solid #000; padding: 6px 9px; font-size: 7.6pt; line-height: 1.5; }
    .terms ol { margin: 2px 0 0; padding-left: 14px; }

    .sign { margin-top: 38px; }
    .sign td { width: 25%; text-align: center; font-size: 8.4pt; font-weight: 700; padding: 0 8px; }
    .sign .line { border-top: 1px solid #000; padding-top: 4px; }
    .foot { margin-top: 14px; border-top: 1px solid #000; padding-top: 5px; font-size: 7.4pt; color: #444; text-align: center; }
</style>
</head>
<body>
<div class="page">

    <table class="head">
        <tr>
            <td style="width:45%">
                @if ($doc['logo'])<img class="logo" src="{{ $doc['logo'] }}" alt="MDM">@endif
            </td>
            <td class="seller">
                <b>{{ $s['name'] }}</b><br>
                <span class="ar">{{ $s['name_ar'] }}</span><br>
                {{ $s['address'] }}<br>
                @if ($s['phone'])Tel: {{ $s['phone'] }} | @endif
                @if ($s['email'])E-Mail: {{ $s['email'] }} | @endif
                {{ $s['website'] }}<br>
                <b style="font-size:9.5pt">TRN: {{ $s['trn'] }}</b>
            </td>
        </tr>
    </table>

    <table class="titlebar">
        <tr>
            <td style="width:33%" class="barcode">
                <img src="{{ $doc['barcode'] }}" alt="" style="height:34px"><br>{{ $doc['number'] }}
            </td>
            <td style="width:34%" class="title">TAX INVOICE<small>فاتورة ضريبية</small></td>
            <td style="width:33%" class="pageno">Page: 1 of 1</td>
        </tr>
    </table>

    <table class="parties">
        <tr>
            <td class="col">
                <span class="lbl">BILL TO:</span>
                Customer Code: {{ $c['code'] }}<br>
                <b>{{ $c['name'] }}</b><br>
                @if ($c['address']){{ $c['address'] }}<br>@endif
                Tel: {{ $c['phone'] ?: '—' }}<br>
                <b>TRN: {{ $c['trn'] ?: '—' }}</b>
            </td>
            <td class="col">
                <span class="lbl">SHIP TO:</span>
                <b>{{ $c['name'] }}</b><br>
                {{ $c['address'] ?: '—' }}<br>
                Tel: {{ $c['phone'] ?: '—' }}
            </td>
            <td class="col">
                <table class="meta">
                    <tr><td class="k">Invoice Number</td><td>: {{ $doc['number'] }}</td></tr>
                    <tr><td class="k">Invoice Date</td><td>: {{ $doc['date']?->format('d/m/Y') }}</td></tr>
                    <tr><td class="k">Supply Period</td><td>: {{ $doc['period_from']?->format('d/m/Y') }}@if ($doc['period_to'] && ! $doc['period_to']->isSameDay($doc['period_from'])) – {{ $doc['period_to']->format('d/m/Y') }}@endif</td></tr>
                    <tr><td class="k">Currency</td><td>: {{ $cur }}</td></tr>
                    @if ($hasDisc)<tr><td class="k">Customer Discount</td><td>: {{ rtrim(rtrim(number_format($c['discount'], 2), '0'), '.') }}%</td></tr>@endif
                    <tr><td class="k">Payment Status</td><td>: {{ ucfirst($doc['status']) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width:4%">S.No</th>
                <th style="width:27%">ITEM DESCRIPTION</th>
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
            @foreach ($doc['lines'] as $l)
                <tr>
                    <td class="c">{{ $l['no'] }}</td>
                    <td class="l desc"><b>{{ $l['name'] }}</b>@if ($l['free'])<span>Free of charge: {{ $l['free'] }} unit(s)</span>@endif</td>
                    <td class="c">{{ $l['qty'] }}@if ($l['free']) + {{ $l['free'] }}@endif</td>
                    <td>{{ $m($l['unit']) }}</td>
                    <td>{{ $m($l['gross']) }}</td>
                    @if ($hasDisc)
                        <td class="c">{{ rtrim(rtrim(number_format($l['disc_pct'], 2), '0'), '.') }}%</td>
                        <td>{{ $m($l['discount']) }}</td>
                    @endif
                    <td>{{ $m($l['taxable']) }}</td>
                    <td class="c">{{ rtrim(rtrim(number_format($l['vat_rate'], 2), '0'), '.') }}%</td>
                    <td>{{ $m($l['vat']) }}</td>
                    <td><b>{{ $m($l['total']) }}</b></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary">
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
                    <tr class="grand"><td>Total ({{ $cur }})</td><td>{{ $m($t['total']) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="words">Amount in words: <b>{{ $doc['words'] }}</b></div>

    <div class="terms">
        <b>Terms &amp; Conditions:</b>
        <ol>@foreach ($doc['terms'] as $term)<li>{{ $term }}</li>@endforeach</ol>
    </div>

    <table class="sign">
        <tr>
            <td><div class="line">Prepared by</div></td>
            <td><div class="line">Checked by</div></td>
            <td><div class="line">Delivered by</div></td>
            <td><div class="line">Customer Signature &amp; Stamp</div></td>
        </tr>
    </table>

    <div class="foot">This is a computer-generated tax invoice. &nbsp;|&nbsp; {{ $s['name'] }} &nbsp;|&nbsp; TRN {{ $s['trn'] }} &nbsp;|&nbsp; {{ $s['website'] }}</div>
</div>
</body>
</html>
