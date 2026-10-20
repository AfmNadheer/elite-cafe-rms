@extends('layouts.app')

@section('title', 'Booking '.$booking->booking_no)

@section('content')
    @php
        $colors = ['booked' => 'warning', 'checked_in' => 'danger', 'checked_out' => 'success', 'cancelled' => 'secondary', 'no_show' => 'dark'];
        $isAdmin = auth()->user()->hasRole('admin');
    @endphp

    <a href="{{ route('room-bookings.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Bookings</a>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 my-3">
        <div>
            <h1 class="h3 fw-bold mb-0">Room {{ $booking->room->room_no }} · {{ $booking->guest_name }}</h1>
            <div class="text-muted small">
                {{ $booking->booking_no }} · {{ $booking->room->type->name }} · {{ \App\Models\RoomBooking::SOURCES[$booking->source] }}
                · <span class="badge text-bg-{{ $colors[$booking->status] }}">{{ \App\Models\RoomBooking::STATUSES[$booking->status] }}</span>
            </div>
        </div>
        <a href="{{ route('room-bookings.invoice', $booking) }}" class="btn btn-outline-dark"><i class="bi bi-printer"></i> Invoice</a>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body row g-2 small">
                    <div class="col-6"><div class="text-muted">Check-in</div><strong>{{ $booking->check_in->format('D, d M Y') }}</strong>@if ($booking->checked_in_at)<div class="text-muted">arrived {{ $booking->checked_in_at->format('d M h:i A') }}</div>@endif</div>
                    <div class="col-6"><div class="text-muted">Check-out</div><strong>{{ $booking->check_out->format('D, d M Y') }}</strong>@if ($booking->checked_out_at)<div class="text-muted">left {{ $booking->checked_out_at->format('d M h:i A') }}</div>@endif</div>
                    <div class="col-6"><div class="text-muted">Guests</div>{{ $booking->adults }} adult(s), {{ $booking->children }} child(ren)</div>
                    <div class="col-6"><div class="text-muted">Contact</div><a href="tel:{{ $booking->guest_phone }}">{{ $booking->guest_phone }}</a> {{ $booking->guest_email }}</div>
                    <div class="col-6"><div class="text-muted">NIC / Passport</div>{{ $booking->guest_nic ?: '–' }}</div>
                    <div class="col-6"><div class="text-muted">Booked by</div>{{ $booking->staff?->name ?? ($booking->customer ? 'Online: '.$booking->customer->name : '–') }}</div>
                    @if ($booking->notes)<div class="col-12"><div class="text-muted">Notes</div>{{ $booking->notes }}</div>@endif
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <table class="table mb-0">
                    <tr><td>{{ $booking->nights }} night(s) × Rs. {{ number_format($booking->price_per_night, 2) }}</td><td class="text-end">{{ number_format($booking->room_total, 2) }}</td></tr>
                    @if ($booking->extra_charges > 0)<tr><td>Extra charges <span class="small text-muted">{{ $booking->extra_notes }}</span></td><td class="text-end">{{ number_format($booking->extra_charges, 2) }}</td></tr>@endif
                    @if ($booking->discount > 0)<tr class="text-danger"><td>Discount</td><td class="text-end">-{{ number_format($booking->discount, 2) }}</td></tr>@endif
                    <tr class="fw-bold"><td>Total</td><td class="text-end">Rs. {{ number_format($booking->total, 2) }}</td></tr>
                    @foreach ($booking->payments as $p)
                        <tr class="small text-success"><td>{{ $p->note }} · {{ $p->label() }} · {{ $p->paid_at->format('d M h:i A') }} · {{ $p->user?->name }}</td><td class="text-end">-{{ number_format($p->amount, 2) }}</td></tr>
                    @endforeach
                    <tr class="fw-bold fs-5 {{ $booking->balance() > 0 ? 'text-danger' : 'text-success' }}"><td>Balance</td><td class="text-end">Rs. {{ number_format($booking->balance(), 2) }}</td></tr>
                </table>
            </div>
        </div>

        <div class="col-lg-5">
            @if ($errors->any())
                <div class="alert alert-danger small"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif

            @if ($booking->status === 'booked')
                <form method="POST" action="{{ route('room-bookings.check-in', $booking) }}" class="card border-0 shadow-sm mb-3">
                    @csrf
                    @method('PATCH')
                    <div class="card-body">
                        <div class="fw-semibold mb-2"><i class="bi bi-box-arrow-in-right"></i> Check in</div>
                        @if ($booking->check_in->isFuture())
                            <div class="small text-muted">Check-in opens on {{ $booking->check_in->format('d M Y') }}.</div>
                        @else
                            <input type="text" name="guest_nic" value="{{ old('guest_nic', $booking->guest_nic) }}" maxlength="20" class="form-control mb-2" placeholder="NIC / Passport number" aria-label="NIC or passport">
                            <button class="btn btn-ec w-100">Check In Guest</button>
                        @endif
                    </div>
                </form>
            @endif

            @if (in_array($booking->status, ['booked', 'checked_in']) && $booking->balance() > 0)
                <form method="POST" action="{{ route('room-bookings.payments.store', $booking) }}" class="card border-0 shadow-sm mb-3">
                    @csrf
                    <div class="card-body">
                        <div class="fw-semibold mb-2"><i class="bi bi-cash-coin"></i> Take a payment</div>
                        <input type="number" step="0.01" min="0" max="{{ $booking->balance() }}" name="amount" class="form-control mb-2" placeholder="Amount (Rs.)" aria-label="Amount">
                        @include('room-bookings._payment-fields', ['prefix' => 'part'])
                        <button class="btn btn-outline-success w-100">Save Payment</button>
                    </div>
                </form>
            @endif

            @if ($booking->status === 'checked_in')
                <form method="POST" action="{{ route('room-bookings.check-out', $booking) }}" class="card border-0 shadow-sm mb-3" id="checkoutForm">
                    @csrf
                    @method('PATCH')
                    <div class="card-body">
                        <div class="fw-semibold mb-2"><i class="bi bi-box-arrow-right"></i> Check out &amp; final bill</div>
                        <div class="row g-2 mb-2">
                            <div class="col-5"><input type="number" step="0.01" min="0" name="extra_charges" id="extra" value="{{ old('extra_charges', (float) $booking->extra_charges) }}" class="form-control form-control-sm" placeholder="Extras (Rs.)" aria-label="Extra charges"></div>
                            <div class="col-7"><input type="text" name="extra_notes" value="{{ old('extra_notes', $booking->extra_notes) }}" maxlength="255" class="form-control form-control-sm" placeholder="e.g. laundry, room service" aria-label="Extra charges notes"></div>
                            @if ($isAdmin)
                                <div class="col-5"><input type="number" step="0.01" min="0" name="discount" id="discount" value="{{ old('discount', (float) $booking->discount) }}" class="form-control form-control-sm" placeholder="Discount" aria-label="Discount"></div>
                                <div class="col-7 small text-muted align-self-center">Discount (Admin only)</div>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between fs-5 fw-bold mb-2"><span>To pay</span><span>Rs. <span id="toPay">{{ number_format($booking->balance(), 2) }}</span></span></div>
                        @include('room-bookings._payment-fields', ['prefix' => 'out'])
                        <input type="number" step="0.01" min="0" name="cash_received" value="{{ old('cash_received') }}" class="form-control mb-2" placeholder="Cash received (for cash)" aria-label="Cash received">
                        <button class="btn btn-success w-100"><i class="bi bi-check2-circle"></i> Check Out &amp; Print Invoice</button>
                    </div>
                </form>
            @endif

            @if ($booking->status === 'booked')
                <form method="POST" action="{{ route('room-bookings.cancel', $booking) }}" class="card border-0 shadow-sm" onsubmit="return confirm('Are you sure?')">
                    @csrf
                    @method('PATCH')
                    <div class="card-body">
                        <div class="fw-semibold mb-2 text-danger">Cancel / No-show</div>
                        <input type="text" name="reason" maxlength="200" class="form-control form-control-sm mb-2" placeholder="Reason" aria-label="Reason">
                        <div class="d-flex gap-2">
                            <button name="status" value="cancelled" class="btn btn-sm btn-outline-danger flex-fill">Cancel Booking</button>
                            <button name="status" value="no_show" class="btn btn-sm btn-outline-dark flex-fill">Mark No-show</button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endsection

@include('room-bookings._payment-script')

@if ($booking->status === 'checked_in')
    @push('scripts')
    <script>
        // Live "to pay" while typing extras / discount
        const roomTotal = {{ (float) $booking->room_total }}, paid = {{ (float) $booking->amount_paid }};
        const recalc = () => {
            const extra = parseFloat(document.getElementById('extra').value) || 0;
            const discount = parseFloat(document.getElementById('discount')?.value) || 0;
            document.getElementById('toPay').textContent = (roomTotal + extra - discount - paid).toLocaleString('en-LK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        };
        ['extra', 'discount'].forEach(id => document.getElementById(id)?.addEventListener('input', recalc));
    </script>
    @endpush
@endif
