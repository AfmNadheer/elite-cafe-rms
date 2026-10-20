@extends('layouts.app')

@section('title', 'Kitchen Display')

@push('styles')
<style>
    .kds-card { border-left: 6px solid #6c757d; }
    .kds-card.preparing { border-left-color: #0dcaf0; }
    .kds-card.late { border-left-color: #dc3545; background: #fff5f5; }
    .kds-name { font-size: 1.25rem; font-weight: 700; }
    .kds-qty { font-size: 1.6rem; font-weight: 800; }
</style>
@endpush

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-bold mb-0"><i class="bi bi-fire text-ec"></i> Kitchen Display</h1>
        <div class="text-muted small">
            <span class="badge text-bg-secondary">Pending</span>
            <span class="badge text-bg-info">Preparing</span>
            <span class="badge text-bg-danger">Waiting &gt; {{ $delayMinutes }} min</span>
            · Updated <span id="updatedAt">–</span>
        </div>
    </div>

    <div id="kdsMessage" class="alert alert-danger d-none"></div>
    <div class="row g-3" id="kdsGrid">
        <div class="col-12 text-center text-muted py-5">Loading orders…</div>
    </div>
@endsection

@push('scripts')
<script>
    const DELAY = {{ $delayMinutes }};
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const grid = document.getElementById('kdsGrid');

    // Escape text before putting it into HTML (prevents XSS)
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    function card(item) {
        const late = item.minutes >= DELAY;
        const button = item.status === 'pending'
            ? `<button class="btn btn-info w-100" onclick="setStatus(${item.id}, 'preparing')"><i class="bi bi-play-fill"></i> Start Preparing</button>`
            : `<button class="btn btn-success w-100" onclick="setStatus(${item.id}, 'ready')"><i class="bi bi-check-lg"></i> Mark Ready</button>`;
        return `
        <div class="col-sm-6 col-lg-4 col-xl-3">
            <div class="card kds-card ${item.status} ${late ? 'late' : ''} shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span class="badge text-bg-dark fs-6">${esc(item.table)}</span>
                        <span class="small ${late ? 'text-danger fw-bold' : 'text-muted'}"><i class="bi bi-clock"></i> ${item.minutes} min · ${esc(item.time)}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-2">
                        <span class="kds-qty">${item.quantity}×</span>
                        <span class="kds-name">${esc(item.name)}</span>
                    </div>
                    ${item.notes ? `<div class="alert alert-warning py-1 px-2 mt-2 mb-0 small"><i class="bi bi-chat-left-text"></i> ${esc(item.notes)}</div>` : ''}
                    <div class="small text-muted mt-2">Order #${item.order_id}</div>
                </div>
                <div class="card-footer bg-white border-0">${button}</div>
            </div>
        </div>`;
    }

    async function load() {
        try {
            const res = await fetch('{{ route('kitchen.items') }}', { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error();
            const data = await res.json();
            grid.innerHTML = data.items.length
                ? data.items.map(card).join('')
                : '<div class="col-12 text-center text-muted py-5"><i class="bi bi-emoji-smile fs-1 d-block"></i>No orders waiting.</div>';
            document.getElementById('updatedAt').textContent = data.updated_at;
            document.getElementById('kdsMessage').classList.add('d-none');
        } catch (e) {
            const msg = document.getElementById('kdsMessage');
            msg.textContent = 'Cannot reach the server. Retrying…';
            msg.classList.remove('d-none');
        }
    }

    async function setStatus(id, status) {
        const res = await fetch(`{{ url('kitchen/items') }}/${id}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ status })
        });
        if (!res.ok) {
            const data = await res.json().catch(() => ({}));
            alert(data.message || 'Could not update the item.');
        }
        load();
    }

    load();
    setInterval(load, 10000);   // every 10 seconds (NFR-02)
</script>
@endpush
