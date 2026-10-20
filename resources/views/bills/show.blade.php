<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bill {{ $bill->bill_no }} | {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        body { background: #f7f4f0; }
        .receipt { width: 80mm; max-width: 100%; margin: 0 auto; background: #fff; padding: 6mm 5mm;
                   font-family: 'Courier New', monospace; font-size: 12px; color: #000; }
        .receipt table { width: 100%; }
        .receipt td { padding: 1px 0; vertical-align: top; }
        .line { border-top: 1px dashed #000; margin: 4px 0; }
        .big { font-size: 15px; font-weight: bold; }
        @media print {
            @page { size: 80mm auto; margin: 0; }
            body { background: #fff; }
            .no-print { display: none !important; }
            .receipt { box-shadow: none !important; padding: 2mm; }
        }
    </style>
</head>
<body>
    @php
        $order = $bill->order;
        $payment = $bill->payments->first();
        $items = $order->items->where('status', '!=', 'cancelled');
    @endphp

    <div class="no-print container py-3" style="max-width: 600px;">
        @if (session('success'))
            <div class="alert alert-success"><i class="bi bi-check-circle"></i> {{ session('success') }}</div>
        @endif
        <div class="d-flex gap-2 justify-content-center">
            <button onclick="window.print()" class="btn btn-dark"><i class="bi bi-printer"></i> Print Bill</button>
            <a href="{{ route('billing.index') }}" class="btn btn-outline-secondary"><i class="bi bi-cash-coin"></i> Back to Billing</a>
            <a href="{{ route('bills.index') }}" class="btn btn-outline-secondary"><i class="bi bi-list"></i> Bills</a>
        </div>
    </div>

    <div class="receipt shadow-sm mb-4">
        <div class="text-center">
            <div class="big">ELITE CAFE (PVT) LTD</div>
            <div>Thank you for dining with us</div>
        </div>
        <div class="line"></div>
        <table>
            <tr><td>Bill No</td><td class="text-end">{{ $bill->bill_no }}</td></tr>
            <tr><td>Date</td><td class="text-end">{{ $bill->created_at->format('d/m/Y h:i A') }}</td></tr>
            @if ($order->order_type === 'delivery')
                <tr><td>Type</td><td class="text-end">Delivery</td></tr>
                <tr><td>Customer</td><td class="text-end">{{ $order->customer?->name }}</td></tr>
            @else
                <tr><td>Table</td><td class="text-end">{{ $order->table?->table_no ?? 'Takeaway' }}</td></tr>
                <tr><td>Waiter</td><td class="text-end">{{ $order->waiter?->name }}</td></tr>
            @endif
            <tr><td>Cashier</td><td class="text-end">{{ $bill->cashier->name }}</td></tr>
        </table>
        <div class="line"></div>
        <table>
            <tr class="fw-bold"><td>Item</td><td class="text-center">Qty</td><td class="text-end">Price</td><td class="text-end">Amount</td></tr>
            @foreach ($items as $item)
                <tr>
                    <td>{{ $item->menuItem->name }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-end">{{ number_format($item->lineTotal(), 2) }}</td>
                </tr>
            @endforeach
        </table>
        <div class="line"></div>
        <table>
            <tr><td>Subtotal</td><td class="text-end">{{ number_format($bill->subtotal, 2) }}</td></tr>
            @if ($bill->service_charge > 0)
                <tr><td>Service charge ({{ \App\Services\BillingService::SERVICE_CHARGE_PERCENT }}%)</td><td class="text-end">{{ number_format($bill->service_charge, 2) }}</td></tr>
            @endif
            @if ($bill->delivery_fee > 0)
                <tr><td>Delivery fee</td><td class="text-end">{{ number_format($bill->delivery_fee, 2) }}</td></tr>
            @endif
            @if ($bill->discount > 0)
                <tr><td>Discount</td><td class="text-end">-{{ number_format($bill->discount, 2) }}</td></tr>
            @endif
            <tr class="big"><td>TOTAL (Rs.)</td><td class="text-end">{{ number_format($bill->total, 2) }}</td></tr>
        </table>
        <div class="line"></div>
        @if ($payment)
            <table>
                <tr><td>Paid by {{ ucfirst($payment->method) }}</td><td class="text-end">{{ number_format($payment->amount_paid, 2) }}</td></tr>
                @if ($payment->method === 'cash')
                    <tr><td>Balance</td><td class="text-end">{{ number_format($payment->balance, 2) }}</td></tr>
                @elseif ($payment->cardLabel())
                    <tr><td>Card</td><td class="text-end">{{ $payment->cardLabel() }}</td></tr>
                    <tr><td>Approval code</td><td class="text-end">{{ $payment->approval_code }}</td></tr>
                @endif
            </table>
            <div class="line"></div>
        @endif
        <div class="text-center">Thank you! Please come again.</div>
    </div>
</body>
</html>
