@php
    /**
     * Classic credit note: same layout and styles as invoice/designs/classic.
     *
     * @var \App\Models\CreditNote $creditNote
     * @var string|null $logoSrc  optional URL used instead of the embedded logo (browser pages)
     */
    $invoice = $creditNote->invoice;
    $company = $invoice->company ?? null;
    $s = config('invoice.seller');
    $cur = config('invoice.currency', 'AED');
    $number = (string) $creditNote->credit_note_number;

    $logoFile = public_path(config('mdm.logo_path'));
    $logo = $logoSrc ?? (is_file($logoFile) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoFile)) : null);

    $m = fn ($v) => number_format((float) $v, 2);
    $neg = fn ($v) => '-'.number_format((float) $v, 2);
    $pct = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.').'%';

    $grossOf = fn ($i) => $i->grossAmount();
    $items = $creditNote->items;
    $hasAveraged = $items->contains(fn ($i) => $i->isAveraged());
    $focUnits = $items->sum(fn ($i) => $i->isAveraged() ? 0 : (int) ($i->free_items ?? 0));

    $totalGross = $items->sum($grossOf);
    $totalTaxable = $items->sum(fn ($i) => $i->priceAfterDiscount());
    $totalDiscount = max(0, $totalGross - $totalTaxable);
    $totalVat = $items->sum(fn ($i) => $i->vatAmount());
    $grandTotal = $items->sum(fn ($i) => $i->lineTotal());

    $byRate = $items
        ->groupBy(fn ($i) => rtrim(rtrim(number_format((float) $i->vat_percent, 2, '.', ''), '0'), '.'))
        ->map(fn ($group) => $group->sum(fn ($i) => $i->priceAfterDiscount()))
        ->sortKeys();

    $discounts = $items->pluck('company_discount_percent')->map(fn ($d) => (float) $d)->unique();
    $hasDisc = $items->contains(fn ($i) => (float) $i->company_discount_percent > 0);
    $lastLine = $items->count() - 1;
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
            <td style="width:30%" class="barcode">
                <img src="{{ \App\Support\Code128::dataUri($number, 38, 1.25) }}" alt="" style="height:34px"><br>{{ $number }}
            </td>
            <td style="width:40%" class="title">TAX CREDIT NOTE<br><span lang="ar" dir="rtl" style="font-family:xbriyaz, Arial, sans-serif; font-size:10pt; letter-spacing:0">إشعار دائن ضريبي</span></td>
            <td style="width:30%"></td>
        </tr>
    </table>

    <table class="parties">
        <tr>
            <td class="col">
                <b style="font-size:9pt; text-decoration:underline">CREDIT TO:</b><br>
                @if ($company)
                    <b>{{ $company->name }}</b><br>
                    @if ($company->address){{ $company->address }}<br>@endif
                    @if ($company->phone)Tel: {{ $company->phone }}<br>@endif
                    @if ($company->trn)<b>TRN: {{ $company->trn }}</b>@endif
                @endif
            </td>
            <td class="col">
                <b style="font-size:9pt; text-decoration:underline">REASON FOR CREDIT:</b><br>
                {{ $creditNote->reason ?: '—' }}
            </td>
            <td class="col">
                <table class="meta">
                    <tr><td class="k">Credit Note No</td><td>: {{ $number }}</td></tr>
                    <tr><td class="k">Credit Note Date</td><td>: {{ $creditNote->note_date->format('d/m/Y') }}</td></tr>
                    <tr><td class="k">Original Invoice</td><td>: {{ $invoice->invoice_number ?? '' }}</td></tr>
                    @if ($invoice?->date_of_create)
                        <tr><td class="k">Invoice Date</td><td>: {{ \Carbon\Carbon::parse($invoice->date_of_create)->format('d/m/Y') }}</td></tr>
                    @endif
                    <tr><td class="k">Currency</td><td>: {{ $cur }}</td></tr>
                    @if ($hasDisc && $discounts->count() === 1)
                        <tr><td class="k">Customer Discount</td><td>: {{ $pct($discounts->first()) }}</td></tr>
                    @endif
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
            @forelse ($items->values() as $i => $item)
                @php
                    $edge = $i === $lastLine ? ' last' : '';
                    $free = $item->isAveraged() ? 0 : (int) ($item->free_items ?? 0);
                @endphp
                <tr>
                    <td class="c{{ $edge }}">{{ $i + 1 }}</td>
                    <td class="l{{ $edge }}">
                        <b style="font-size:8.8pt">{{ $item->item_name }}</b>
                        @if ($free)<br><span style="font-size:7.4pt; color:#444">Free of charge: {{ $free }} unit(s)</span>@endif
                        @if ($item->line_note)<br><span style="font-size:7.4pt; color:#444">Note: {{ $item->line_note }}</span>@endif
                    </td>
                    <td class="c{{ $edge }}">{{ $item->pricedUnits() }}@if ($free) + {{ $free }}@endif</td>
                    <td class="{{ $edge }}">{{ $m($item->displayUnitPrice()) }}</td>
                    <td class="{{ $edge }}">{{ $neg($grossOf($item)) }}</td>
                    @if ($hasDisc)
                        <td class="c{{ $edge }}">{{ $pct($item->company_discount_percent) }}</td>
                        <td class="{{ $edge }}">{{ $neg($grossOf($item) - $item->priceAfterDiscount()) }}</td>
                    @endif
                    <td class="{{ $edge }}">{{ $neg($item->priceAfterDiscount()) }}</td>
                    <td class="c{{ $edge }}">{{ $pct($item->vat_percent) }}</td>
                    <td class="{{ $edge }}">{{ $neg($item->vatAmount()) }}</td>
                    <td class="{{ $edge }}"><b>{{ $neg($item->lineTotal()) }}</b></td>
                </tr>
            @empty
                <tr><td class="c last" colspan="{{ $hasDisc ? 11 : 9 }}"><em>No line items on this credit note.</em></td></tr>
            @endforelse
        </tbody>
    </table>

    <table>
        <tr>
            <td style="width:52%" class="counts">
                Total Quantity : {{ $items->sum(fn ($i) => $i->pricedUnits()) }}@if ($focUnits) + {{ $focUnits }} FOC @endif<br>
                No. of Items : {{ $items->count() }}
                @if ($hasAveraged)<br>Unit rate averaged over paid and free units.@endif
            </td>
            <td style="width:48%">
                <table class="tot">
                    <tr><td class="k">Gross Amount ({{ $cur }})</td><td style="width:34%">{{ $neg($totalGross) }}</td></tr>
                    @if ($hasDisc)<tr><td class="k">Discount ({{ $cur }})</td><td>{{ $neg($totalDiscount) }}</td></tr>@endif
                    @foreach ($byRate as $rate => $amount)
                        <tr><td class="k">{{ $rate }}% Rated Sales ({{ $cur }})</td><td>{{ $neg($amount) }}</td></tr>
                    @endforeach
                    <tr><td class="k">VAT Amount ({{ $cur }})</td><td>{{ $neg($totalVat) }}</td></tr>
                    <tr><td class="grand">Total Credit ({{ $cur }})</td><td class="grand">{{ $neg($grandTotal) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="words">Credit amount in words: <b>{{ \App\Support\InvoiceDocument::words((float) $grandTotal, $cur) }}</b></div>

    <table class="sign">
        <tr>
            <td class="line">Prepared by</td><td class="gap"></td>
            <td class="line">Checked by</td><td class="gap"></td>
            <td class="line">Received by</td><td class="gap"></td>
            <td class="line">Customer Signature &amp; Stamp</td>
        </tr>
    </table>

    <div class="foot">This is a computer-generated tax credit note. &nbsp;|&nbsp; {{ $s['name'] }} &nbsp;|&nbsp; TRN {{ $s['trn'] }} &nbsp;|&nbsp; {{ $s['website'] }}</div>
</div>
