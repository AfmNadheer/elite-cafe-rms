@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-bold mb-0"><i class="bi bi-people text-ec"></i> Users</h1>
        <a href="{{ route('users.create') }}" class="btn btn-ec"><i class="bi bi-person-plus"></i> Add User</a>
    </div>

    {{-- Search and filter --}}
    <form method="GET" class="row g-2 mb-3">
        <div class="col-sm-6 col-md-4">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search name or username">
        </div>
        <div class="col-sm-4 col-md-3">
            <select name="role" class="form-select">
                <option value="">All roles</option>
                @foreach ($roles as $key => $label)
                    <option value="{{ $key }}" @selected(request('role') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-secondary"><i class="bi bi-search"></i> Filter</button>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $u)
                        <tr>
                            <td>{{ $u->name }}</td>
                            <td><code>{{ $u->username }}</code></td>
                            <td>{{ $u->roleName() }}</td>
                            <td>
                                @if ($u->is_active)
                                    <span class="badge text-bg-success">Active</span>
                                @else
                                    <span class="badge text-bg-secondary">Inactive</span>
                                @endif
                                @if ($u->locked_until && $u->locked_until->isFuture())
                                    <span class="badge text-bg-warning">Locked</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('users.edit', $u) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                @unless ($u->is(auth()->user()))
                                    <form method="POST" action="{{ route('users.toggle', $u) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm {{ $u->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                                onclick="return confirm('Are you sure?')">
                                            <i class="bi {{ $u->is_active ? 'bi-person-x' : 'bi-person-check' }}"></i>
                                            {{ $u->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $users->links() }}</div>
@endsection
