@extends('layouts.app')

@section('title', 'Messages')

@section('content')
    <h1 class="h3 fw-bold mb-3"><i class="bi bi-envelope text-ec"></i> Website Messages</h1>

    <div class="list-group shadow-sm">
        @forelse ($messages as $m)
            <div class="list-group-item py-3 {{ $m->is_read ? '' : 'border-start border-4 border-warning' }}">
                <div class="d-flex flex-wrap justify-content-between gap-2">
                    <div>
                        <strong>{{ $m->name }}</strong>
                        <a href="mailto:{{ $m->email }}" class="ms-2">{{ $m->email }}</a>
                        @if ($m->phone)<a href="tel:{{ $m->phone }}" class="ms-2">{{ $m->phone }}</a>@endif
                        @unless ($m->is_read)<span class="badge text-bg-warning ms-2">New</span>@endunless
                    </div>
                    <div class="small text-muted">{{ $m->created_at->format('d M Y, h:i A') }}</div>
                </div>
                <p class="mb-2 mt-2" style="white-space: pre-line;">{{ $m->message }}</p>
                <div class="d-flex gap-2">
                    <form method="POST" action="{{ route('messages.toggle', $m) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-secondary">{{ $m->is_read ? 'Mark as new' : 'Mark as read' }}</button></form>
                    <form method="POST" action="{{ route('messages.destroy', $m) }}" onsubmit="return confirm('Delete this message?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
                </div>
            </div>
        @empty
            <div class="list-group-item text-center text-muted py-5">No messages yet. Messages from the website's Contact form appear here.</div>
        @endforelse
    </div>
    <div class="mt-3">{{ $messages->links() }}</div>
@endsection
