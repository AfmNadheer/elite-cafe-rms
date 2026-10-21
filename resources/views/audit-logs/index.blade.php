@extends('layouts.app')

@section('title', 'Audit Log')

@section('content')
    <h1 class="h3 fw-bold mb-1"><i class="bi bi-shield-check text-ec"></i> Audit Log</h1>
    <p class="text-muted">Who did what and when: logins, price changes, discounts, cancelled items, stock changes and backups.</p>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-sm-6 col-md-3">
            <select name="user" class="form-select">
                <option value="">All users</option>
                @foreach ($users as $u)
                    <option value="{{ $u->id }}" @selected(request('user') == $u->id)>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-6 col-md-3">
            <select name="action" class="form-select">
                <option value="">All actions</option>
                @foreach ($actions as $key => $label)
                    <option value="{{ $key }}" @selected(request('action') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto"><input type="date" name="from" value="{{ request('from') }}" class="form-control @error('from') is-invalid @enderror" aria-label="From"></div>
        <div class="col-auto"><input type="date" name="to" value="{{ request('to') }}" class="form-control @error('to') is-invalid @enderror" aria-label="To"></div>
        <div class="col-auto"><button class="btn btn-outline-secondary"><i class="bi bi-funnel"></i> Filter</button></div>
        <div class="col-auto"><a href="{{ route('audit-logs.index') }}" class="btn btn-link">Clear</a></div>
    </form>

    @php
        $colors = ['login_failed' => 'warning', 'account_locked' => 'danger', 'price_changed' => 'info', 'discount' => 'primary',
                   'item_cancelled' => 'danger', 'menu_item_deleted' => 'danger', 'expense_deleted' => 'danger', 'backup' => 'success'];
    @endphp

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light"><tr><th>Date &amp; time</th><th>User</th><th>Action</th><th>Details</th><th>IP address</th></tr></thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="text-nowrap">{{ $log->created_at->format('d M Y, h:i:s A') }}</td>
                            <td>{{ $log->user?->name ?? '–' }}</td>
                            <td><span class="badge text-bg-{{ $colors[$log->action] ?? 'secondary' }}">{{ $log->actionName() }}</span></td>
                            <td>{{ $log->description }}</td>
                            <td class="small text-muted">{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No entries found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $logs->links() }}</div>
@endsection
