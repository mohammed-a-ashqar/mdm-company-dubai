@php($columns = $quotation->printedColumns())
<div class="q-title"><b>{{ $quotation->customer_name }} (QUOTATION)</b></div>
<table class="q-table">
    <thead>
        <tr>
            @foreach ($columns as $column)
                <th>{{ $column['label'] }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($quotation->items as $item)
            <tr>
                @foreach ($columns as $column)
                    <td>{{ $item->cell($column) }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
