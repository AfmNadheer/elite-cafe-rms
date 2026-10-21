@extends('layouts.app')

@section('title', 'Database Backup')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <h1 class="h3 fw-bold my-3"><i class="bi bi-database-down text-ec"></i> Database Backup</h1>
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    <p>Download a full copy of the <strong>{{ $database }}</strong> database ({{ $tables }} tables) as a <code>.sql</code> file.
                       Keep it on a USB drive or in cloud storage, away from this computer.</p>
                    <p class="text-muted small mb-4">
                        Last backup:
                        @if ($lastBackup)
                            {{ $lastBackup->created_at->format('d M Y, h:i A') }} by {{ $lastBackup->user?->name }}
                            ({{ $lastBackup->created_at->diffForHumans() }})
                        @else
                            never
                        @endif
                    </p>
                    <a href="{{ route('backup.download') }}" class="btn btn-ec btn-lg"><i class="bi bi-download"></i> Download Backup Now</a>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">How to restore a backup</div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li>Open <strong>phpMyAdmin</strong> (http://localhost/phpmyadmin) and select the <strong>{{ $database }}</strong> database.</li>
                        <li>Click <strong>Import</strong>, choose the backup <code>.sql</code> file and click <strong>Import</strong>.</li>
                        <li>All tables are replaced with the data in the backup.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection
