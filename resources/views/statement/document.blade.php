@php
    $money = fn ($v) => \App\Support\StatementDocument::money($v).' '.$doc['currency'];
    $sums = $doc['sums'];
    $st = $doc['statement'];
@endphp
<div class="st-doc">
    <table>
        <tr>
            <td class="logo">
                @if ($doc['logo'])
                    <img src="{{ $doc['logo'] }}" alt="MDM" style="width: 105px; height: auto;">
                @endif
            </td>
        </tr>
    </table>
    <div class="rule"></div>

    <table style="margin-top: 12px;">
        <tr>
            <td style="width: 58%;">
                <span style="font-size: 15pt;">&nbsp;&nbsp;&nbsp;Statement To</span><br>
                <span style="font-size: 11pt; line-height: 1.6;">
                    <b>{{ $doc['customer']['name'] }}</b><br>
                    @if ($doc['customer']['address'])
                        {!! nl2br(e($doc['customer']['address'])) !!}<br>
                    @endif
                    @if ($doc['customer']['trn'])
                        TRN: {{ $doc['customer']['trn'] }}<br>
                    @endif
                    @if ($doc['customer']['phone'])
                        Phone : {{ $doc['customer']['phone'] }}
                    @endif
                </span>
            </td>
            <td class="period">
                <b>period</b> :{{ $doc['from']->format('d-M-Y') }} - {{ $doc['to']->format('d-M-Y') }}
            </td>
        </tr>
    </table>

    <div class="doc-title">Statement Of Accounts</div>
    <div class="rule"></div>

    <table class="rows">
        <thead>
            <tr>
                <th class="c-type">Type</th>
                <th>Issue Date</th>
                <th>Status</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($doc['rows'] as $row)
                <tr>
                    <td class="c-type">{{ $row['number'] }}</td>
                    <td>{{ $row['date']->format('d-m-Y') }}</td>
                    <td><span class="{{ $row['status'] }}">{{ ucfirst($row['status']) }}</span></td>
                    <td>{{ $money($row['total']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="empty">No invoices in this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="rule" style="margin-top: 4px;"></div>

    <table class="sums" style="margin-top: 6px;">
        <tr>
            <td class="s1">Overall:{{ $money($sums['overall']['total']) }}</td>
            <td class="s2">Vat:{{ $money($sums['overall']['vat']) }}</td>
            <td>Without Vat:{{ $money($sums['overall']['net']) }}</td>
        </tr>
        <tr>
            <td class="s1">Unpaid :{{ $money($sums['unpaid']['total']) }}</td>
            <td class="s2">Vat :{{ $money($sums['unpaid']['vat']) }}</td>
            <td>Without Vat:{{ $money($sums['unpaid']['net']) }}</td>
        </tr>
    </table>
    <div class="rule-double" style="margin-top: 10px;"></div>

    <table class="footer">
        <tr>
            <td class="legal">
                {{ $st['legal_name'] }}<br><br>
                <span style="font-weight: normal;">TRN: {{ $st['trn'] }}</span>
            </td>
            <td class="address">Address: {{ $st['address'] }}</td>
            <td class="bank">
                Account No : {{ $st['account_no'] }}<br>
                IBAN No : {{ $st['iban'] }}<br>
                Company name: {{ $st['account_name'] }}<br>
                Bank : {{ $st['bank'] }}
            </td>
        </tr>
    </table>
</div>
