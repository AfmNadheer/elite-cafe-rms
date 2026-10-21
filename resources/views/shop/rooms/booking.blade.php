@extends('shop.layout')

@section('title', 'Booking '.$booking->booking_no)

@section('content')
    <a href="{{ route('shop.rooms.mine') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> My bookings</a>

    <div class="card border-0 shadow-sm mt-3">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                <div>
                    <div class="text-muted small">Booking confirmation</div>
                    <h1 class="h4 fw-bold mb-0">{{ $booking->booking_no }}</h1>
                </div>
                <div class="text-end">
                    <span class="badge text-bg-primary fs-6">{{ \App\Models\RoomBooking::STATUSES[$booking->status] }}</span>
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="text-muted small">Room</div>
                    <div class="fs-5 fw-semibold">{{ $booking->room->type->name }} · Room {{ $booking->room->room_no }}</div>
                    <div>{{ $booking->adults }} adult(s), {{ $booking->children }} child(ren)</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Dates</div>
                    <div><strong>Check-in:</strong> {{ $booking->check_in->format('D, d M Y') }} from {{ \Illuminate\Support\Carbon::parse(config('rooms.check_in_time'))->format('h:i A') }}</div>
                    <div><strong>Check-out:</strong> {{ $booking->check_out->format('D, d M Y') }} by {{ \Illuminate\Support\Carbon::parse(config('rooms.check_out_time'))->format('h:i A') }}</div>
                </div>
            </div>
            <table class="table mb-2">
                <tr><td>{{ $booking->nights }} night(s) × Rs. {{ number_format($booking->price_per_night, 2) }}</td><td class="text-end">{{ number_format($booking->room_total, 2) }}</td></tr>
                @foreach ($booking->payments as $p)
                    <tr class="text-success"><td>{{ $p->note }} ({{ $p->label() }}) {{ $p->paid_at->format('d M Y') }}</td><td class="text-end">-{{ number_format($p->amount, 2) }}</td></tr>
                @endforeach
                <tr class="fw-bold"><td>Balance</td><td class="text-end">Rs. {{ number_format($booking->balance(), 2) }}</td></tr>
            </table>
            <p class="small text-muted mb-0"><i class="bi bi-info-circle"></i> Show this page at the front desk with your NIC or passport. To change or cancel, please call the hotel.</p>
        </div>
    </div>
    <div class="text-center mt-3 no-print"><button onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer"></i> Print</button></div>
@endsection
