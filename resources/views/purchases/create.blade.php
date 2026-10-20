@extends('layouts.app')

@section('title', 'New Purchase')

@section('content')
    <a href="{{ route('purchases.index') }}" class="text-decoration-none small"><i class="bi bi-arrow-left"></i> Purchases</a>
    <h1 class="h3 fw-bold my-3">New Purchase</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i> Please check the form:
            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @php $rows = old('items', [['ingredient_id' => '', 'quantity' => '', 'unit_cost' => '']]); @endphp

    <form method="POST" action="{{ route('purchases.store') }}" id="purchaseForm">
        @csrf
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body row g-3">
                <div class="col-md-5">
                    <label class="form-label" for="supplier_id">Supplier <span class="text-danger">*</span></label>
                    <select name="supplier_id" id="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror">
                        <option value="">Select supplier…</option>
                        @foreach ($suppliers as $s)
                            <option value="{{ $s->id }}" @selected(old('supplier_id') == $s->id)>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="purchase_date">Date <span class="text-danger">*</span></label>
                    <input type="date" name="purchase_date" id="purchase_date" value="{{ old('purchase_date', today()->toDateString()) }}"
                           max="{{ today()->toDateString() }}" class="form-control @error('purchase_date') is-invalid @enderror">
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr><th style="min-width: 200px;">Ingredient</th><th style="width: 160px;">Quantity</th><th style="width: 170px;">Unit cost (Rs.)</th><th class="text-end">Amount</th><th></th></tr>
                    </thead>
                    <tbody id="rows">
                        @foreach ($rows as $i => $row)
                            <tr class="item-row">
                                <td>
                                    <select name="items[{{ $i }}][ingredient_id]" class="form-select ingredient">
                                        <option value="">Select…</option>
                                        @foreach ($ingredients as $ing)
                                            <option value="{{ $ing->id }}" data-unit="{{ $ing->unit }}" @selected(($row['ingredient_id'] ?? '') == $ing->id)>{{ $ing->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <input type="number" step="0.001" min="0" name="items[{{ $i }}][quantity]" value="{{ $row['quantity'] ?? '' }}" class="form-control qty">
                                        <span class="input-group-text unit">-</span>
                                    </div>
                                </td>
                                <td><input type="number" step="0.01" min="0" name="items[{{ $i }}][unit_cost]" value="{{ $row['unit_cost'] ?? '' }}" class="form-control cost"></td>
                                <td class="text-end amount">0.00</td>
                                <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger remove" aria-label="Remove row"><i class="bi bi-x-lg"></i></button></td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3"><button type="button" id="addRow" class="btn btn-sm btn-outline-secondary"><i class="bi bi-plus-lg"></i> Add another item</button></td>
                            <td class="text-end fw-bold fs-5">Rs. <span id="total">0.00</span></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <button type="submit" class="btn btn-ec btn-lg"><i class="bi bi-save"></i> Save Purchase &amp; Add to Stock</button>
    </form>
@endsection

@push('scripts')
<script>
    const tbody = document.getElementById('rows');
    let nextIndex = {{ count($rows) }};
    const money = n => n.toLocaleString('en-LK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function refresh() {
        let total = 0;
        tbody.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty').value) || 0;
            const cost = parseFloat(row.querySelector('.cost').value) || 0;
            total += qty * cost;
            row.querySelector('.amount').textContent = money(qty * cost);
            const opt = row.querySelector('.ingredient').selectedOptions[0];
            row.querySelector('.unit').textContent = opt && opt.dataset.unit ? opt.dataset.unit : '-';
            row.querySelector('.remove').disabled = tbody.children.length === 1;
        });
        document.getElementById('total').textContent = money(total);
    }

    document.getElementById('addRow').addEventListener('click', () => {
        const row = tbody.querySelector('.item-row').cloneNode(true);
        row.querySelectorAll('select, input').forEach(el => {
            el.name = el.name.replace(/items\[\d+\]/, `items[${nextIndex}]`);
            el.value = '';
        });
        nextIndex++;
        tbody.appendChild(row);
        refresh();
    });

    tbody.addEventListener('click', e => {
        const btn = e.target.closest('.remove');
        if (btn && tbody.children.length > 1) { btn.closest('tr').remove(); refresh(); }
    });
    tbody.addEventListener('input', refresh);
    tbody.addEventListener('change', refresh);
    refresh();
</script>
@endpush
