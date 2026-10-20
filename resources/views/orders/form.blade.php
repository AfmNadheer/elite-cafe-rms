@extends('layouts.app')

@section('title', $order ? 'Add Items' : 'New Order')

@push('styles')
<style>
    .menu-row + .menu-row { border-top: 1px solid #eee; }
    .qty-input { width: 60px; text-align: center; }
    .summary-bar { position: sticky; bottom: 0; z-index: 10; }
</style>
@endpush

@section('content')
    <a href="{{ $order ? route('orders.show', $order) : route('tables.map') }}" class="text-decoration-none small">
        <i class="bi bi-arrow-left"></i> Back
    </a>
    <h1 class="h3 fw-bold my-3">
        @if ($order)
            Add items to {{ $order->table?->table_no ?? 'Takeaway' }} <span class="text-muted fs-5">(Order #{{ $order->id }})</span>
        @else
            New Order
        @endif
    </h1>

    @error('qty')
        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> {{ $message }}</div>
    @enderror

    <form method="POST" id="orderForm" action="{{ $order ? route('orders.items.store', $order) : route('orders.store') }}">
        @csrf

        {{-- Table and order type (new orders only) --}}
        @unless ($order)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label d-block">Order type</label>
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="order_type" id="dineIn" value="dine_in" @checked(old('order_type', 'dine_in') === 'dine_in')>
                            <label class="btn btn-outline-secondary" for="dineIn"><i class="bi bi-shop"></i> Dine-in</label>
                            <input type="radio" class="btn-check" name="order_type" id="takeaway" value="takeaway" @checked(old('order_type') === 'takeaway')>
                            <label class="btn btn-outline-secondary" for="takeaway"><i class="bi bi-bag"></i> Takeaway</label>
                        </div>
                    </div>
                    <div class="col-md-4" id="tableField">
                        <label class="form-label" for="table_id">Table</label>
                        <select name="table_id" id="table_id" class="form-select @error('table_id') is-invalid @enderror">
                            <option value="">Select table…</option>
                            @foreach ($tables as $table)
                                <option value="{{ $table->id }}" @selected(old('table_id', $selectedTable?->id) == $table->id)>
                                    {{ $table->table_no }} ({{ $table->capacity }} seats{{ $table->status === 'reserved' ? ', reserved' : '' }})
                                </option>
                            @endforeach
                        </select>
                        @error('table_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        @endunless

        {{-- Menu by category --}}
        <ul class="nav nav-pills mb-3 flex-nowrap overflow-auto" role="tablist">
            @foreach ($categories as $category)
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-nowrap {{ $loop->first ? 'active' : '' }}" data-bs-toggle="pill"
                            data-bs-target="#cat{{ $category->id }}" type="button" role="tab">
                        {{ $category->name }}
                    </button>
                </li>
            @endforeach
        </ul>

        <div class="tab-content mb-3">
            @forelse ($categories as $category)
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="cat{{ $category->id }}" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body py-1">
                            @foreach ($category->menuItems as $item)
                                <div class="menu-row py-2 d-flex flex-wrap align-items-center gap-2 {{ $item->is_available ? '' : 'opacity-50' }}">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">{{ $item->name }}</div>
                                        <div class="small text-muted">
                                            Rs. {{ number_format($item->price, 2) }}
                                            @unless ($item->is_available) · <span class="text-danger">Not available</span> @endunless
                                        </div>
                                    </div>
                                    @if ($item->is_available)
                                        <input type="text" name="notes[{{ $item->id }}]" value="{{ old("notes.$item->id") }}" maxlength="255"
                                               class="form-control form-control-sm" style="max-width: 180px;" placeholder="Note (e.g. less spicy)">
                                        <div class="input-group input-group-sm" style="width: auto;">
                                            <button class="btn btn-outline-secondary qty-btn" type="button" data-step="-1" aria-label="Less">−</button>
                                            <input type="number" name="qty[{{ $item->id }}]" value="{{ old("qty.$item->id", 0) }}" min="0" max="50"
                                                   class="form-control qty-input" data-price="{{ $item->price }}" aria-label="Quantity of {{ $item->name }}">
                                            <button class="btn btn-outline-secondary qty-btn" type="button" data-step="1" aria-label="More">+</button>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted">No menu items yet. Ask the Admin to add the menu first.</p>
            @endforelse
        </div>

        {{-- Summary --}}
        <div class="summary-bar card border-0 shadow">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div><span id="itemCount">0</span> item(s) · <strong>Rs. <span id="total">0.00</span></strong></div>
                <button type="submit" class="btn btn-ec btn-lg" id="sendBtn" disabled>
                    <i class="bi bi-send"></i> Send to Kitchen
                </button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    const inputs = document.querySelectorAll('.qty-input');

    function updateSummary() {
        let count = 0, total = 0;
        inputs.forEach(input => {
            const q = Math.max(0, parseInt(input.value) || 0);
            count += q;
            total += q * parseFloat(input.dataset.price);
        });
        document.getElementById('itemCount').textContent = count;
        document.getElementById('total').textContent = total.toLocaleString('en-LK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('sendBtn').disabled = count === 0;
    }

    // + and − buttons
    document.querySelectorAll('.qty-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.parentElement.querySelector('.qty-input');
            input.value = Math.min(50, Math.max(0, (parseInt(input.value) || 0) + parseInt(btn.dataset.step)));
            updateSummary();
        });
    });
    inputs.forEach(input => input.addEventListener('input', updateSummary));

    // Hide the table box for takeaway orders
    const tableField = document.getElementById('tableField');
    document.querySelectorAll('input[name="order_type"]').forEach(radio => {
        radio.addEventListener('change', () => {
            tableField.style.display = document.getElementById('takeaway').checked ? 'none' : '';
        });
    });
    if (document.getElementById('takeaway')?.checked) tableField.style.display = 'none';

    // Stop double submission
    document.getElementById('orderForm').addEventListener('submit', () => {
        document.getElementById('sendBtn').disabled = true;
    });

    updateSummary();
</script>
@endpush
