<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $booking->booking_no }} | Elite Cafe</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        body { background: #f7f4f0; }
        .invoice { max-width: 760px; margin: 0 auto; background: #fff; padding: 32px; }
        .brand { color: #8b3a1a; }
        @media print { body { background: #fff; } .no-print { display: none !important; } .invoice { box-shadow: none !important; padding: 0; } }
    </style>
</head>
<body>
    <div class="no-print container py-3 text-center" style="max-width: 760px;">
        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        <button onclick="window.print()" class="btn btn-dark"><i class="bi bi-printer"></i> Print</button>
        <a href="{{ route('room-bookings.show', $booking) }}" class="btn btn-outline-secondary">Back to booking</a>
        <a href="{{ route('room-bookings.board') }}" class="btn btn-outline-secondary">Room board</a>
    </div>

    <div class="invoice shadow-sm mb-4">
        <div class="d-flex justify-content-between border-bottom pb-3 mb-3">
            <div>
                <div class="h4 fw-bold brand mb-0">Elite Cafe (Pvt) Ltd</div>
                <div class="text-muted">Rooms &amp; Restaurant</div>
            </div>
            <div class="text-end">
                <div class="h5 mb-0">{{ $booking->status === 'checked_out' ? 'INVOICE' : 'BOOKING CONFIRMATION' }}</div>
                <div>{{ $booking->booking_no }}</div>
                <div class="small text-muted">{{ now()->format('d M Y, h:i A') }}</div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <div class="text-muted small">Guest</div>
                <div class="fw-semibold">{{ $booking->guest_name }}</div>
                <div>{{ $booking->guest_phone }}</div>
                @if ($booking->guest_email)<div>{{ $booking->guest_email }}</div>@endif
                @if ($booking->guest_nic)<div class="small">NIC/Passport: {{ $booking->guest_nic }}</div>@endif
            </div>
            <div class="col-6 text-end">
                <div class="text-muted small">Room</div>
                <div class="fw-semibold">{{ $booking->room->room_no }} · {{ $booking->room->type->name }}</div>
                <div>{{ $booking->check_in->format('d M Y') }} → {{ $booking->check_out->format('d M Y') }}</div>
                <div class="small">{{ $booking->adults }} adult(s), {{ $booking->children }} child(ren)</div>
            </div>
        </div>

        <table class="table">
            <thead class="table-light"><tr><th>Description</th><th class="text-end">Amount (Rs.)</th></tr></thead>
            <tbody>
                <tr><td>Room charge: {{ $booking->nights }} night(s) × {{ number_format($booking->price_per_night, 2) }}</td><td class="text-end">{{ number_format($booking->room_total, 2) }}</td></tr>
                @if ($booking->extra_charges > 0)<tr><td>Extra charges {{ $booking->extra_notes ? '('.$booking->extra_notes.')' : '' }}</td><td class="text-end">{{ number_format($booking->extra_charges, 2) }}</td></tr>@endif
                @if ($booking->discount > 0)<tr><td>Discount</td><td class="text-end">-{{ number_format($booking->discount, 2) }}</td></tr>@endif
            </tbody>
            <tfoot>
                <tr class="fw-bold"><td>Total</td><td class="text-end">{{ number_format($booking->total, 2) }}</td></tr>
                @foreach ($booking->payments as $p)
                    <tr class="small"><td>Paid – {{ $p->note }} ({{ $p->label() }}) {{ $p->paid_at->format('d M Y') }}</td><td class="text-end">-{{ number_format($p->amount, 2) }}</td></tr>
                @endforeach
                <tr class="fw-bold fs-5"><td>Balance due</td><td class="text-end">{{ number_format($booking->balance(), 2) }}</td></tr>
            </tfoot>
        </table>

        <p class="small text-muted mb-0">
            Check-in from {{ \Illuminate\Support\Carbon::parse(config('rooms.check_in_time'))->format('h:i A') }} ·
            check-out by {{ \Illuminate\Support\Carbon::parse(config('rooms.check_out_time'))->format('h:i A') }}.
            Thank you for staying with Elite Cafe.
        </p>
    </div>
</body>
</html>
