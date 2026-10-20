@extends('layouts.app')

@section('title', 'Bill – Order #'.$order->id)

@section('content')
    @php $isAdmin = auth()->user()->hasRole('admin'); @endphp

    <a href="{{ route('billing.index') }}" class="text-decoration-none small"><i class="bi bi-arrow-left"></i> Billing</a>
    <h1 class="h3 fw-bold my-3">
        Bill for {{ $order->table?->table_no ?? 'Takeaway' }}
        <span class="text-muted fs-5">· Order #{{ $order->id }} · {{ $order->waiter?->name ?? 'Online' }}</span>
    </h1>

    @error('order')
        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> {{ $message }}</div>
    @enderror
    @if ($pendingOnline)
        <div class="alert alert-info d-flex flex-wrap align-items-center gap-2">
            <i class="bi bi-credit-card-2-front"></i>
            An online payment of Rs. {{ number_format($pendingOnline->amount, 2) }} ({{ $pendingOnline->order_ref }}) is waiting for PayHere.
            <a href="{{ route('payhere.return', $pendingOnline) }}" class="alert-link ms-auto">Check status</a>
        </div>
    @endif
    @if ($unfinished)
        <div class="alert alert-warning">
            <i class="bi bi-hourglass-split"></i>
            The kitchen has <strong>{{ $unfinished }}</strong> item(s) not ready yet. The bill can be saved when they are ready or cancelled.
        </div>
    @endif

    <div class="row g-3">
        {{-- Items (taken from the order, FR-23) --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Item</th><th class="text-center">Qty</th><th class="text-end">Price</th><th class="text-end">Amount</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr>
                                    <td>
                                        {{ $item->menuItem->name }}
                                        @if (in_array($item->status, ['pending', 'preparing']))
                                            <span class="badge text-bg-warning">{{ ucfirst($item->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end">{{ number_format($item->lineTotal(), 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Totals, discount and payment --}}
        <div class="col-lg-5">
            <form method="POST" action="{{ route('billing.store', $order) }}" id="billForm" class="card border-0 shadow-sm">
                @csrf
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-2">
                        <tr><td>Subtotal</td><td class="text-end" id="subtotal">0.00</td></tr>
                        <tr><td>Service charge ({{ $serviceRate }}%)</td><td class="text-end" id="service">0.00</td></tr>
                        <tr class="text-danger"><td>Discount</td><td class="text-end" id="discount">0.00</td></tr>
                        <tr class="fs-4 fw-bold border-top"><td>Total (Rs.)</td><td class="text-end" id="total">0.00</td></tr>
                    </table>

                    {{-- Discount (FR-25) --}}
                    <div class="border rounded p-2 mb-3">
                        <label class="form-label fw-semibold mb-1">Discount</label>
                        <div class="input-group">
                            <select name="discount_type" id="discount_type" class="form-select" style="max-width: 150px;">
                                <option value="none" @selected(old('discount_type', 'none') === 'none')>No discount</option>
                                <option value="percent" @selected(old('discount_type') === 'percent')>Percent (%)</option>
                                <option value="fixed" @selected(old('discount_type') === 'fixed')>Amount (Rs.)</option>
                            </select>
                            <input type="number" step="0.01" min="0" name="discount_value" id="discount_value" value="{{ old('discount_value') }}"
                                   class="form-control @error('discount_value') is-invalid @enderror" placeholder="0">
                            @error('discount_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        @unless ($isAdmin)
                            <div id="approvalBox" class="mt-2">
                                <div class="small text-muted mb-1"><i class="bi bi-shield-lock"></i> Admin approval needed for a discount</div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="text" name="approver_username" value="{{ old('approver_username') }}" autocomplete="off"
                                               class="form-control form-control-sm @error('approver_username') is-invalid @enderror" placeholder="Admin username">
                                    </div>
                                    <div class="col-6">
                                        <input type="password" name="approver_password" autocomplete="new-password"
                                               class="form-control form-control-sm @error('approver_username') is-invalid @enderror" placeholder="Admin password">
                                    </div>
                                </div>
                                @error('approver_username')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        @endunless
                    </div>

                    {{-- Payment (FR-26) --}}
                    <label class="form-label fw-semibold mb-1">Payment</label>
                    <div class="btn-group w-100 mb-2" role="group">
                        <input type="radio" class="btn-check" name="method" id="cash" value="cash" @checked(old('method', 'cash') === 'cash')>
                        <label class="btn btn-outline-success" for="cash"><i class="bi bi-cash"></i> Cash</label>
                        <input type="radio" class="btn-check" name="method" id="card" value="card" @checked(old('method') === 'card')>
                        <label class="btn btn-outline-primary" for="card"><i class="bi bi-credit-card"></i> Card machine</label>
                        @if ($onlineReady)
                            <input type="radio" class="btn-check" name="method" id="online" value="online" @checked(old('method') === 'online')>
                            <label class="btn btn-outline-dark" for="online"><i class="bi bi-globe"></i> Online</label>
                        @endif
                    </div>

                    {{-- Card machine slip details --}}
                    <div id="cardBox" class="border rounded p-2 mb-2">
                        <div class="small text-muted mb-1"><i class="bi bi-credit-card"></i> From the card machine slip</div>
                        <div class="row g-2">
                            <div class="col-12">
                                <select name="card_type" class="form-select form-select-sm @error('card_type') is-invalid @enderror" aria-label="Card type">
                                    <option value="">Card type…</option>
                                    @foreach (\App\Models\Payment::CARD_TYPES as $key => $label)
                                        <option value="{{ $key }}" @selected(old('card_type') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('card_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6">
                                <input type="text" name="card_last4" value="{{ old('card_last4') }}" maxlength="4" inputmode="numeric" pattern="[0-9]{4}"
                                       class="form-control form-control-sm @error('card_last4') is-invalid @enderror" placeholder="Last 4 digits" aria-label="Last 4 digits">
                                @error('card_last4')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6">
                                <input type="text" name="approval_code" value="{{ old('approval_code') }}" maxlength="12"
                                       class="form-control form-control-sm text-uppercase @error('approval_code') is-invalid @enderror" placeholder="Approval code" aria-label="Approval code">
                                @error('approval_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- Online card payment through PayHere --}}
                    <div id="onlineBox" class="border rounded p-2 mb-2">
                        <div class="small text-muted mb-1"><i class="bi bi-globe"></i> Customer details for PayHere (receipt is emailed)</div>
                        <input type="text" name="customer_name" value="{{ old('customer_name') }}" maxlength="100"
                               class="form-control form-control-sm mb-2 @error('customer_name') is-invalid @enderror" placeholder="Customer name" aria-label="Customer name">
                        @error('customer_name')<div class="invalid-feedback mb-2">{{ $message }}</div>@enderror
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="tel" name="customer_phone" value="{{ old('customer_phone') }}" maxlength="20"
                                       class="form-control form-control-sm @error('customer_phone') is-invalid @enderror" placeholder="Phone" aria-label="Phone">
                                @error('customer_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6">
                                <input type="email" name="customer_email" value="{{ old('customer_email') }}" maxlength="100"
                                       class="form-control form-control-sm @error('customer_email') is-invalid @enderror" placeholder="Email" aria-label="Email">
                                @error('customer_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div id="cashBox">
                        <label class="form-label small mb-1" for="amount_paid">Cash received (Rs.)</label>
                        <input type="number" step="0.01" min="0" name="amount_paid" id="amount_paid" value="{{ old('amount_paid') }}"
                               class="form-control form-control-lg @error('amount_paid') is-invalid @enderror">
                        @error('amount_paid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="d-flex justify-content-between mt-2 fs-5">
                            <span>Balance to return</span><strong id="balance">0.00</strong>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 pb-3">
                    <button type="submit" class="btn btn-ec btn-lg w-100" id="payBtn" @disabled($unfinished)>
                        <i class="bi bi-check2-circle"></i> Save Payment &amp; Print Bill
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Same calculation as BillingService (the server checks it again)
    const SUBTOTAL = {{ $order->subtotal() }};
    const SERVICE_RATE = {{ $serviceRate }};
    const $ = id => document.getElementById(id);
    const money = n => n.toLocaleString('en-LK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const round2 = n => Math.round(n * 100) / 100;

    function update() {
        const type = $('discount_type').value;
        const value = parseFloat($('discount_value').value) || 0;
        const service = round2(SUBTOTAL * SERVICE_RATE / 100);
        let discount = type === 'percent' ? round2(SUBTOTAL * value / 100) : (type === 'fixed' ? round2(value) : 0);
        const total = round2(SUBTOTAL + service - discount);

        $('subtotal').textContent = money(SUBTOTAL);
        $('service').textContent = money(service);
        $('discount').textContent = '- ' + money(discount);
        $('total').textContent = money(total);

        $('discount_value').disabled = type === 'none';
        if ($('approvalBox')) $('approvalBox').style.display = (type !== 'none' && value > 0) ? '' : 'none';

        const isCash = $('cash').checked;
        $('cashBox').style.display = isCash ? '' : 'none';
        const method = document.querySelector('input[name="method"]:checked')?.value;
        $('cardBox').style.display = method === 'card' ? '' : 'none';
        $('onlineBox').style.display = method === 'online' ? '' : 'none';
        $('payBtn').innerHTML = method === 'online'
            ? '<i class="bi bi-globe"></i> Continue to Online Payment'
            : '<i class="bi bi-check2-circle"></i> Save Payment &amp; Print Bill';
        const paid = parseFloat($('amount_paid').value) || 0;
        const balance = round2(paid - total);
        $('balance').textContent = paid ? money(balance) : '0.00';
        $('balance').className = balance < 0 && paid ? 'text-danger' : 'text-success';
    }

    ['discount_type', 'discount_value', 'amount_paid', 'cash', 'card', 'online'].filter(id => $(id)).forEach(id => {
        $(id).addEventListener('input', update);
        $(id).addEventListener('change', update);
    });
    $('billForm').addEventListener('submit', () => { $('payBtn').disabled = true; });
    update();
</script>
@endpush
