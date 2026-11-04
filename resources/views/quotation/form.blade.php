@php
    $standardColumns = \App\Models\Quotation::STANDARD_COLUMNS;
    $hasOld = session()->hasOldInput();

    $hidden = $hasOld
        ? array_values(array_diff(array_keys($standardColumns), old('visible_columns', [])))
        : ($quotation?->hidden_columns ?? []);
    $customColumns = $hasOld ? array_values(old('custom_columns', [])) : ($quotation?->custom_columns ?? []);

    if ($hasOld) {
        $items = array_values(old('items', []));
    } elseif ($quotation) {
        $items = $quotation->items->map(fn ($item) => [
            'name' => $item->name,
            'price' => (float) $item->price,
            'vat_rate' => (float) $item->vat_rate,
            'discount' => (float) $item->discount,
            'quantity' => $item->quantity,
            'bonus' => $item->bonus,
            'custom' => $item->custom_values ?? [],
        ])->all();
    } else {
        $items = [];
    }
@endphp

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">{{ $heading }}</h4>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ $action }}" method="POST" id="quotation-form">
                @csrf

                <div class="row">
                    <div class="col-lg-4 mb-3">
                        <label class="form-label">Saved company (optional)</label>
                        <select name="company_id" id="q-company" class="form-control">
                            <option value="">â€” Not in the list â€”</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}" data-name="{{ $company->name }}" data-discount="{{ (float) $company->discount }}"
                                    @selected((string) old('company_id', $quotation?->company_id) === (string) $company->id)>{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-5 mb-3">
                        <label class="form-label">Company name on the quotation</label>
                        <input type="text" name="customer_name" id="q-customer" class="form-control" maxlength="255" required
                            value="{{ old('customer_name', $quotation?->customer_name) }}">
                    </div>
                    <div class="col-lg-3 mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="quotation_date" class="form-control" required
                            value="{{ old('quotation_date', $quotation?->quotation_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label d-block">Columns printed on the quotation</label>
                    @foreach ($standardColumns as $key => $label)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input q-visible" type="checkbox" name="visible_columns[]" value="{{ $key }}" id="q-col-{{ $key }}" @checked(! in_array($key, $hidden, true))>
                            <label class="form-check-label" for="q-col-{{ $key }}">{{ $label }}@if (in_array($key, ['vat', 'vat_after'])) ({{ $key === 'vat' ? 'original' : 'after discount' }})@endif</label>
                        </div>
                    @endforeach
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-2">
                        <thead class="table-light" id="q-head"></thead>
                        <tbody id="q-body"></tbody>
                    </table>
                </div>

                <button type="button" class="btn btn-light btn-sm" id="q-add-item"><i class="mdi mdi-plus"></i> Add item</button>
                <button type="button" class="btn btn-light btn-sm" id="q-add-column"><i class="mdi mdi-table-column-plus-after"></i> Add column</button>
                <p class="text-muted font-13 mt-2 mb-3">Added columns print before the totals. A new quotation for the same company starts with that company's latest columns.</p>

                <button type="submit" class="btn btn-primary waves-effect waves-light">Save</button>
                <a href="{{ route('quotation.index') }}" class="btn btn-light">Back</a>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var isNew = @json($quotation === null && ! $hasOld);
    var memory = @json($columnMemory);
    var columns = @json($customColumns);
    var items = @json($items);

    var head = document.getElementById('q-head');
    var body = document.getElementById('q-body');
    var company = document.getElementById('q-company');
    var customer = document.getElementById('q-customer');

    var fields = [
        { key: 'name', label: 'Items Description', type: 'text', min: '180px' },
        { key: 'price', label: 'Price', type: 'number', step: '0.01', min: '90px' },
        { key: 'vat_rate', label: 'VAT %', type: 'number', step: '0.01', min: '80px' },
        { key: 'discount', label: 'Discount %', type: 'number', step: '0.01', min: '90px' },
        { key: 'quantity', label: 'Qty', type: 'number', step: '1', min: '80px' },
        { key: 'bonus', label: 'Bonus', type: 'number', step: '1', min: '80px' }
    ];

    function selectedDiscount() {
        var option = company.options[company.selectedIndex];
        return option && option.dataset.discount ? option.dataset.discount : 0;
    }

    function blankItem() {
        return { name: '', price: '', vat_rate: 5, discount: selectedDiscount(), quantity: 1, bonus: 0, custom: {} };
    }

    function input(type, name, value, minWidth, step) {
        var el = document.createElement('input');
        el.type = type;
        el.name = name;
        el.value = value === null || value === undefined ? '' : value;
        el.className = 'form-control form-control-sm';
        el.required = type === 'number' || name.indexOf('[name]') !== -1;
        if (type === 'number') { el.min = 0; el.step = step; }
        if (minWidth) { el.style.minWidth = minWidth; }
        return el;
    }

    function removeButton(title, onClick) {
        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-sm btn-link text-danger p-0 ms-1';
        button.title = title;
        button.innerHTML = '<i class="mdi mdi-close"></i>';
        button.addEventListener('click', onClick);
        return button;
    }

    // Keep what was typed before redrawing the table.
    function readInputs() {
        columns.forEach(function (column, i) {
            var el = head.querySelector('[name="custom_columns[' + i + '][label]"]');
            if (el) { column.label = el.value; }
        });
        Array.prototype.forEach.call(body.rows, function (row, r) {
            fields.forEach(function (field) {
                items[r][field.key] = row.querySelector('[name="items[' + r + '][' + field.key + ']"]').value;
            });
            columns.forEach(function (column) {
                var el = row.querySelector('[name="items[' + r + '][custom][' + column.key + ']"]');
                items[r].custom = items[r].custom || {};
                if (el) { items[r].custom[column.key] = el.value; }
            });
        });
    }

    function draw() {
        head.innerHTML = '';
        body.innerHTML = '';

        var headRow = head.insertRow();
        fields.forEach(function (field) {
            var th = document.createElement('th');
            th.textContent = field.label;
            headRow.appendChild(th);
        });
        columns.forEach(function (column, i) {
            var th = document.createElement('th');
            var wrap = document.createElement('div');
            wrap.className = 'd-flex align-items-center';
            var key = document.createElement('input');
            key.type = 'hidden';
            key.name = 'custom_columns[' + i + '][key]';
            key.value = column.key;
            var label = input('text', 'custom_columns[' + i + '][label]', column.label, '120px');
            label.required = true;
            label.maxLength = 60;
            label.placeholder = 'Column name';
            wrap.appendChild(key);
            wrap.appendChild(label);
            wrap.appendChild(removeButton('Remove column', function () {
                readInputs();
                columns.splice(i, 1);
                draw();
            }));
            th.appendChild(wrap);
            headRow.appendChild(th);
        });
        headRow.appendChild(document.createElement('th'));

        items.forEach(function (item, r) {
            var row = body.insertRow();
            fields.forEach(function (field) {
                row.insertCell().appendChild(input(field.type, 'items[' + r + '][' + field.key + ']', item[field.key], field.min, field.step));
            });
            columns.forEach(function (column) {
                var value = item.custom ? item.custom[column.key] : '';
                var el = input('text', 'items[' + r + '][custom][' + column.key + ']', value, '120px');
                el.maxLength = 255;
                row.insertCell().appendChild(el);
            });
            var cell = row.insertCell();
            cell.className = 'text-center';
            if (items.length > 1) {
                cell.appendChild(removeButton('Remove item', function () {
                    readInputs();
                    items.splice(r, 1);
                    draw();
                }));
            }
        });
    }

    function applyMemory() {
        if (!isNew) { return; }
        var saved = memory[customer.value.trim().toLowerCase()];
        if (!saved) { return; }
        readInputs();
        columns = JSON.parse(JSON.stringify(saved.custom));
        document.querySelectorAll('.q-visible').forEach(function (box) {
            box.checked = saved.hidden.indexOf(box.value) === -1;
        });
        draw();
    }

    company.addEventListener('change', function () {
        var option = company.options[company.selectedIndex];
        if (option.value) {
            customer.value = option.dataset.name;
            readInputs();
            items.forEach(function (item) {
                if (!parseFloat(item.discount)) { item.discount = option.dataset.discount; }
            });
            draw();
            applyMemory();
        }
    });
    customer.addEventListener('change', applyMemory);

    document.getElementById('q-add-item').addEventListener('click', function () {
        readInputs();
        items.push(blankItem());
        draw();
    });
    document.getElementById('q-add-column').addEventListener('click', function () {
        if (columns.length >= 8) { return; }
        readInputs();
        columns.push({ key: 'c' + Date.now().toString(36), label: '' });
        draw();
        head.querySelector('[name="custom_columns[' + (columns.length - 1) + '][label]"]').focus();
    });

    if (!items.length) { items.push(blankItem()); }
    draw();
});
</script>
