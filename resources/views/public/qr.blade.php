@extends('layouts.app')

@section('title', 'QR Menu')

@push('styles')
<style>
    .qr-card { width: 300px; margin: 0 auto; text-align: center; }
    #qrcode img, #qrcode canvas { margin: 0 auto; }
    @media print {
        nav, .no-print { display: none !important; }
        body { background: #fff; }
    }
</style>
@endpush

@section('content')
    <div class="no-print">
        <h1 class="h3 fw-bold mb-1"><i class="bi bi-qr-code text-ec"></i> QR Menu</h1>
        <p class="text-muted">Print this card and place it on the tables. Customers scan it to see the current menu on their phone.</p>
    </div>

    <div class="card border-0 shadow-sm qr-card mb-3">
        <div class="card-body p-4">
            <div class="fw-bold fs-4 text-ec"><i class="bi bi-cup-hot-fill"></i> Elite Cafe</div>
            <div class="mb-3">Scan to see our menu</div>
            <div id="qrcode"></div>
            <div class="small text-muted mt-3 text-break">{{ $menuUrl }}</div>
        </div>
    </div>

    <div class="text-center no-print">
        <button onclick="window.print()" class="btn btn-dark"><i class="bi bi-printer"></i> Print QR Card</button>
        <a href="{{ $menuUrl }}" target="_blank" rel="noopener" class="btn btn-outline-secondary"><i class="bi bi-box-arrow-up-right"></i> Open Menu</a>
        @if (str_contains($menuUrl, '127.0.0.1') || str_contains($menuUrl, 'localhost'))
            <div class="alert alert-warning mt-3 text-start mx-auto" style="max-width: 600px;">
                <i class="bi bi-info-circle"></i> This link uses <strong>127.0.0.1</strong>, so it only works on this computer.
                To test with a phone on the same Wi-Fi, start the server with <code>php artisan serve --host=0.0.0.0</code>
                and open this page using the computer's IP address (e.g. <code>http://192.168.1.10:8000/qr-menu</code>).
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    new QRCode(document.getElementById('qrcode'), { text: @json($menuUrl), width: 220, height: 220, correctLevel: QRCode.CorrectLevel.M });
</script>
@endpush
