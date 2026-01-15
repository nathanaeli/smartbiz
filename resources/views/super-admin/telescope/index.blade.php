@extends('layouts.super-admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-gradient-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">
                                <svg class="me-2" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                                Smart Telescope Monitoring
                            </h4>
                            <small class="text-white-50">Advanced application debugging and performance monitoring</small>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#quickActionsModal">
                                <svg class="me-1" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                                </svg>
                                Quick Actions
                            </button>
                            <button class="btn btn-success btn-sm" onclick="exportEntries()">
                                <svg class="me-1" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
                                </svg>
                                Export
                            </button>
                            <button class="btn btn-warning btn-sm" onclick="toggleAutoRefresh()">
                                <svg class="me-1" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46C19.54 14.87 20 13.48 20 12c0-4.42-3.58-8-8-8zm0 14c-3.31 0-6-2.69-6-6 0-1.01.25-1.97.7-2.8L5.24 7.74C4.46 9.13 4 10.52 4 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3z"/>
                                </svg>
                                <span id="refreshText">Auto Refresh</span>
                            </button>
                            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#clearModal">
                                <svg class="me-1" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M16 9v10H8V9h8m-1.5-6h-5l-1 1H5v2h14V4h-3.5l-1-1zM18 7H6v12c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7z"/>
                                </svg>
                                Clear Logs
                            </button>
                            <span class="badge bg-light text-dark">{{ number_format($entries->total()) }} entries</span>
                        </div>
                    </div>
                </div>
            <div class="card-body">
                <!-- Statistics Cards -->
                <div class="row mb-4" id="statsContainer">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body text-center">
                                <svg class="mb-2" width="32" height="32" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                                <h5 id="totalEntries">{{ number_format($stats['total_entries']) }}</h5>
                                <small>Total Entries</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body text-center">
                                <svg class="mb-2" width="32" height="32" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20 3h-1V1h-2v2H7V1H5v2H4c-1.1 0-1.99.9-1.99 2L2 21c0 1.1.89 2 2 2h16c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 18H4V8h16v13z"/>
                                </svg>
                                <h5 id="todayEntries">{{ number_format($stats['today_entries']) }}</h5>
                                <small>Today's Entries</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white h-100">
                            <div class="card-body text-center">
                                <svg class="mb-2" width="32" height="32" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                                </svg>
                                <h5 id="queryCount">{{ number_format($stats['query_count']) }}</h5>
                                <small>Database Queries</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white h-100">
                            <div class="card-body text-center">
                                <svg class="mb-2" width="32" height="32" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19.14,12.94c0.04-0.3,0.06-0.61,0.06-0.94c0-0.32-0.02-0.64-0.07-0.94l2.03-1.58c0.18-0.14,0.23-0.41,0.12-0.61 l-1.92-3.32c-0.12-0.22-0.37-0.29-0.59-0.22l-2.39,0.96c-0.5-0.38-1.03-0.7-1.62-0.94L14.4,2.81c-0.04-0.24-0.24-0.41-0.48-0.41 h-3.84c-0.24,0-0.43,0.17-0.47,0.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-0.22-0.08-0.47,0-0.59,0.22L2.74,8.87 C2.62,9.08,2.66,9.34,2.86,9.48l2.03,1.58C4.84,11.36,4.82,11.69,4.82,12s0.02,0.64,0.07,0.94l-2.03,1.58 c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32c0.12,0.22,0.37,0.29,0.59,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.36,2.54 c0.05,0.24,0.24,0.41,0.48,0.41h3.84c0.24,0,0.43-0.17,0.47-0.41l0.36-2.54c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96 c0.22,0.08,0.47,0,0.59-0.22l1.92-3.32c0.12-0.22,0.07-0.47-0.12-0.61L19.14,12.94z M12,15.6c-1.98,0-3.6-1.62-3.6-3.6 s1.62-3.6,3.6-3.6s3.6,1.62,3.6,3.6S13.98,15.6,12,15.6z"/>
                                </svg>
                                <h5 id="modelCount">{{ number_format($stats['model_count']) }}</h5>
                                <small>Model Events</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Metrics -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-danger">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="text-danger mb-1">
                                            <svg class="me-2" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                                            </svg>
                                            Performance Alerts
                                        </h6>
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <div class="border-end">
                                                    <h4 class="text-warning mb-0" id="slowQueries">{{ number_format($stats['slow_queries']) }}</h4>
                                                    <small class="text-muted">Slow Queries</small>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="border-end">
                                                    <h4 class="text-danger mb-0" id="errorCount">{{ number_format($stats['error_count']) }}</h4>
                                                    <small class="text-muted">Errors (24h)</small>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <h4 class="text-info mb-0" id="avgResponseTime">{{ number_format($stats['avg_response_time'], 2) }}ms</h4>
                                                <small class="text-muted">Avg Response</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-info">
                            <div class="card-body">
                                <h6 class="text-info mb-3">
                                    <svg class="me-2" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M3.5 18.49l6-6.01 4 4L22 6.92l-1.41-1.41-7.09 7.97-4-4L2 16.99z"/>
                                    </svg>
                                    System Overview
                                </h6>
                                <div class="row text-center">
                                    <div class="col-6">
                                        <h5 class="text-primary mb-0">{{ number_format($stats['database_size'], 2) }} MB</h5>
                                        <small class="text-muted">Database Size</small>
                                    </div>
                                    <div class="col-6">
                                        <h5 class="text-success mb-0">{{ number_format($stats['request_count']) }}</h5>
                                        <small class="text-muted">Total Requests</small>
                                    </div>
                                </div>
                                <hr>
                                <small class="text-muted">
                                    <svg class="me-1" width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"/>
                                        <path d="M12.5 7H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
                                    </svg>
                                    Oldest: {{ $stats['oldest_entry'] ? \Carbon\Carbon::parse($stats['oldest_entry'])->diffForHumans() : 'N/A' }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Success/Error Messages -->
                @if (session()->has('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <svg class="me-2" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <svg class="me-2" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                        </svg>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Advanced Filters -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <svg class="me-2" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M3 17v2h6v-2H3zM3 5v2h10V5H3zm10 16v-2h8v-2h-8v-2h-2v6h2zM7 9v2H3v2h4v2h2V9H7zm14 4v-2H11v2h10zm-6-4h2V7h4V5h-4V3h-2v6z"/>
                            </svg>
                            Filters
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('super-admin.telescope.index') }}" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Entry Type</label>
                                <select name="type" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="request" {{ request('type') == 'request' ? 'selected' : '' }}>Request</option>
                                    <option value="query" {{ request('type') == 'query' ? 'selected' : '' }}>Database Query</option>
                                    <option value="model" {{ request('type') == 'model' ? 'selected' : '' }}>Model Event</option>
                                    <option value="command" {{ request('type') == 'command' ? 'selected' : '' }}>Command</option>
                                    <option value="job" {{ request('type') == 'job' ? 'selected' : '' }}>Job</option>
                                    <option value="event" {{ request('type') == 'event' ? 'selected' : '' }}>Event</option>
                                    <option value="cache" {{ request('type') == 'cache' ? 'selected' : '' }}>Cache</option>
                                    <option value="log" {{ request('type') == 'log' ? 'selected' : '' }}>Log</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">From Date</label>
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">To Date</label>
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Search</label>
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search content..."
                                           value="{{ request('search') }}">
                                    <button type="submit" class="btn btn-primary">
                                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="col-12">
                                <a href="{{ route('super-admin.telescope.index') }}" class="btn btn-outline-secondary">
                                    <svg class="me-1" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                                    </svg>
                                    Clear Filters
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Bulk Actions Bar -->
                <div class="d-flex justify-content-between align-items-center mb-3" id="bulkActionsBar" style="display: none;">
                    <div>
                        <span id="selectedCount" class="text-muted">0 entries selected</span>
                    </div>
                    <div>
                        <button class="btn btn-danger btn-sm" onclick="bulkDelete()">
                            <svg class="me-1" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M16 9v10H8V9h8m-1.5-6h-5l-1 1H5v2h14V4h-3.5l-1-1zM18 7H6v12c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7z"/>
                            </svg>
                            Delete Selected
                        </button>
                    </div>
                </div>

                <!-- Entries Table -->
                <form id="bulkActionForm" method="POST" action="{{ route('super-admin.telescope.bulk-delete') }}">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th width="40">
                                        <input type="checkbox" class="form-check-input" id="selectAllEntries">
                                    </th>
                                    <th>
                                        <svg class="me-1" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M7 20l4-16h2l4 16h-2l-1-4H10l-1 4H7zm3-6h2l-1-4h-2l1 4z"/>
                                        </svg>
                                        ID
                                    </th>
                                    <th>
                                        <svg class="me-1" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M5.5 7C4.67 7 4 6.33 4 5.5S4.67 4 5.5 4 7 4.67 7 5.5 6.33 7 5.5 7zm15.91 4.58l-9-9C12.05 2.22 11.55 2 11 2H4c-1.1 0-2 .9-2 2v7c0 .55.22 1.05.59 1.41l9 9c.36.36.86.59 1.41.59.55 0 1.05-.22 1.41-.59l7-7c.37-.36.59-.86.59-1.41 0-.55-.23-1.06-.59-1.42zM13.06 10.06L11 8l1.06-1.06 1.06 1.06 3.18-3.18 1.06 1.06-4.24 4.24z"/>
                                        </svg>
                                        Type
                                    </th>
                                    <th>
                                        <svg class="me-1" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                                        </svg>
                                        Content Preview
                                    </th>
                                    <th>
                                        <svg class="me-1" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"/>
                                            <path d="M12.5 7H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
                                        </svg>
                                        Timestamp
                                    </th>
                                    <th>
                                        <svg class="me-1" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                        </svg>
                                        Duration
                                    </th>
                                    <th>
                                        <svg class="me-1" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                                        </svg>
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($entries as $entry)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input entry-checkbox"
                                               name="entry_ids[]" value="{{ $entry->uuid }}">
                                    </td>
                                    <td>
                                        <code class="text-muted small">{{ substr($entry->uuid, 0, 8) }}...</code>
                                    </td>
                                    <td>
                                        @php
                                            $typeColors = [
                                                'request' => 'primary',
                                                'query' => 'warning',
                                                'model' => 'info',
                                                'command' => 'secondary',
                                                'job' => 'success',
                                                'event' => 'danger',
                                                'cache' => 'dark',
                                                'log' => 'light'
                                            ];
                                            $color = $typeColors[$entry->type] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $color }}">
                                            @if($entry->type === 'query')
                                                <svg class="me-1" width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                </svg>
                                            @elseif($entry->type === 'model')
                                                <svg class="me-1" width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M19.14,12.94c0.04-0.3,0.06-0.61,0.06-0.94c0-0.32-0.02-0.64-0.07-0.94l2.03-1.58c0.18-0.14,0.23-0.41,0.12-0.61 l-1.92-3.32c-0.12-0.22-0.37-0.29-0.59-0.22l-2.39,0.96c-0.5-0.38-1.03-0.7-1.62-0.94L14.4,2.81c-0.04-0.24-0.24-0.41-0.48-0.41 h-3.84c-0.24,0-0.43,0.17-0.47,0.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-0.22-0.08-0.47,0-0.59,0.22L2.74,8.87 C2.62,9.08,2.66,9.34,2.86,9.48l2.03,1.58C4.84,11.36,4.82,11.69,4.82,12s0.02,0.64,0.07,0.94l-2.03,1.58 c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32c0.12,0.22,0.37,0.29,0.59,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.36,2.54 c0.05,0.24,0.24,0.41,0.48,0.41h3.84c0.24,0,0.43-0.17,0.47-0.41l0.36-2.54c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96 c0.22,0.08,0.47,0,0.59-0.22l1.92-3.32c0.12-0.22,0.07-0.47-0.12-0.61L19.14,12.94z M12,15.6c-1.98,0-3.6-1.62-3.6-3.6 s1.62-3.6,3.6-3.6s3.6,1.62,3.6,3.6S13.98,15.6,12,15.6z"/>
                                                </svg>
                                            @elseif($entry->type === 'request')
                                                <svg class="me-1" width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                                </svg>
                                            @else
                                                <svg class="me-1" width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
                                                    <circle cx="12" cy="12" r="8"/>
                                                </svg>
                                            @endif
                                            {{ ucfirst($entry->type) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $content = json_decode($entry->content, true);
                                            $preview = '';

                                            switch($entry->type) {
                                                case 'query':
                                                    $preview = $content['sql'] ?? 'SQL Query';
                                                    break;
                                                case 'model':
                                                    $preview = ($content['action'] ?? '') . ' ' . ($content['model'] ?? '');
                                                    break;
                                                case 'request':
                                                    $preview = ($content['method'] ?? '') . ' ' . ($content['uri'] ?? '');
                                                    break;
                                                case 'log':
                                                    $preview = ($content['level'] ?? '') . ': ' . ($content['message'] ?? '');
                                                    break;
                                                default:
                                                    $preview = substr(json_encode($content), 0, 100) . '...';
                                            }
                                        @endphp
                                        <span class="text-truncate d-inline-block" style="max-width: 300px;" title="{{ $preview }}">
                                            {{ Str::limit($preview, 50) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($entry->created_at)->format('M d, Y H:i:s') }}
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($entry->created_at)->diffForHumans() }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($entry->type === 'request' || $entry->type === 'query')
                                            @php
                                                $duration = $content['duration'] ?? null;
                                            @endphp
                                            @if($duration)
                                                <span class="badge {{ $duration > 1000 ? 'bg-danger' : ($duration > 500 ? 'bg-warning' : 'bg-success') }}">
                                                    {{ number_format($duration, 2) }}ms
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('super-admin.telescope.show', $entry->uuid) }}" class="btn btn-sm btn-info">
                                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                                                </svg>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteSingleEntry('{{ $entry->uuid }}')">
                                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M16 9v10H8V9h8m-1.5-6h-5l-1 1H5v2h14V4h-3.5l-1-1zM18 7H6v12c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="empty-state">
                                            <svg class="mb-3" width="64" height="64" fill="currentColor" viewBox="0 0 24 24" style="color: #6c757d;">
                                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                            </svg>
                                            <h5 class="text-muted">No Telescope Entries Found</h5>
                                            <p class="text-muted">There are no monitoring entries matching your criteria.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </form>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $entries->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions Modal -->
<div class="modal fade" id="quickActionsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <svg class="me-2" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                    </svg>
                    Quick Actions
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-body text-center">
                                <svg class="mb-2" width="32" height="32" fill="currentColor" viewBox="0 0 24 24" style="color: #dc3545;">
                                    <path d="M16 9v10H8V9h8m-1.5-6h-5l-1 1H5v2h14V4h-3.5l-1-1zM18 7H6v12c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7z"/>
                                </svg>
                                <h6>Clear Old Entries</h6>
                                <p class="text-muted small">Remove entries older than selected period</p>
                                <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#clearModal">
                                    Configure
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <svg class="mb-2" width="32" height="32" fill="currentColor" viewBox="0 0 24 24" style="color: #198754;">
                                    <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
                                </svg>
                                <h6>Export Data</h6>
                                <p class="text-muted small">Download current filtered entries as JSON</p>
                                <button class="btn btn-outline-success btn-sm" onclick="exportEntries()">
                                    Export Now
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-info">
                            <div class="card-body text-center">
                                <svg class="mb-2" width="32" height="32" fill="currentColor" viewBox="0 0 24 24" style="color: #0dcaf0;">
                                    <path d="M3.5 18.49l6-6.01 4 4L22 6.92l-1.41-1.41-7.09 7.97-4-4L2 16.99z"/>
                                </svg>
                                <h6>Performance Report</h6>
                                <p class="text-muted small">Generate performance analysis report</p>
                                <button class="btn btn-outline-info btn-sm" onclick="generateReport()">
                                    Generate
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-warning">
                            <div class="card-body text-center">
                                <svg class="mb-2" width="32" height="32" fill="currentColor" viewBox="0 0 24 24" style="color: #ffc107;">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                                <h6>Database Health</h6>
                                <p class="text-muted small">Check database performance and size</p>
                                <button class="btn btn-outline-warning btn-sm" onclick="checkDbHealth()">
                                    Check Health
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Clear Logs Modal -->
<div class="modal fade" id="clearModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <svg class="me-2" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                    </svg>
                    Smart Log Cleanup
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="clearForm" method="POST" action="{{ route('super-admin.telescope.clear') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Entry Type</label>
                        <select name="type" class="form-select">
                            <option value="all">All Types</option>
                            <option value="query">Database Queries</option>
                            <option value="model">Model Events</option>
                            <option value="request">Requests</option>
                            <option value="command">Commands</option>
                            <option value="job">Jobs</option>
                            <option value="event">Events</option>
                            <option value="cache">Cache</option>
                            <option value="log">Logs</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Smart Cleanup Options</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="cleanup_mode" value="time" id="timeBased" checked>
                            <label class="form-check-label" for="timeBased">
                                Time-based: Clear entries older than
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="cleanup_mode" value="count" id="countBased">
                            <label class="form-check-label" for="countBased">
                                Count-based: Keep only last
                            </label>
                        </div>
                    </div>
                    <div class="mb-3" id="timeOptions">
                        <select name="days" class="form-select">
                            <option value="1">1 day</option>
                            <option value="7" selected>7 days</option>
                            <option value="30">30 days</option>
                            <option value="90">90 days</option>
                            <option value="365">1 year</option>
                        </select>
                    </div>
                    <div class="mb-3" id="countOptions" style="display: none;">
                        <select name="keep_count" class="form-select">
                            <option value="1000">1,000 entries</option>
                            <option value="5000">5,000 entries</option>
                            <option value="10000">10,000 entries</option>
                            <option value="50000">50,000 entries</option>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <svg class="me-2" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                        </svg>
                        <strong>Smart Cleanup:</strong> This will intelligently remove old entries while preserving recent debugging data.
                    </div>
                    <div class="alert alert-warning">
                        <svg class="me-2" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                        </svg>
                        <strong>Warning:</strong> This action cannot be undone. Make sure to export important data first.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <svg class="me-1" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                    </svg>
                    Cancel
                </button>
                <button type="submit" form="clearForm" class="btn btn-danger">
                    <svg class="me-1" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M16 9v10H8V9h8m-1.5-6h-5l-1 1H5v2h14V4h-3.5l-1-1zM18 7H6v12c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7z"/>
                    </svg>
                    Smart Cleanup
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.table-hover tbody tr:hover {
    background-color: rgba(0,123,255,0.05);
}
.empty-state {
    padding: 40px 20px;
}
.refreshing {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Performance Report Styles */
.performance-report .metric-item {
    padding: 8px 0;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.performance-report .metric-item:last-child {
    border-bottom: none;
}

.performance-report .overall-score .badge {
    font-size: 1.1em;
    padding: 8px 16px;
}

.performance-report .card {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.2s ease;
}

.performance-report .card:hover {
    transform: translateY(-2px);
}

.performance-report .recommendations .alert {
    margin-bottom: 10px;
    border-radius: 8px;
}

.performance-report .recommendations .alert:last-child {
    margin-bottom: 0;
}

/* Modal improvements */
.modal-xl {
    max-width: 1200px;
}

@media (max-width: 1200px) {
    .modal-xl {
        max-width: 95vw;
    }
}
</style>

<script>
let autoRefreshInterval = null;
let isAutoRefreshEnabled = false;

function toggleAutoRefresh() {
    const button = event.target.closest('button');
    const icon = button.querySelector('i');
    const text = button.querySelector('#refreshText');

    if (isAutoRefreshEnabled) {
        clearInterval(autoRefreshInterval);
        isAutoRefreshEnabled = false;
        icon.classList.remove('refreshing');
        text.textContent = 'Auto Refresh';
        button.classList.remove('btn-success');
        button.classList.add('btn-warning');
    } else {
        isAutoRefreshEnabled = true;
        text.textContent = 'Stop Refresh';
        button.classList.remove('btn-warning');
        button.classList.add('btn-success');
        icon.classList.add('refreshing');

        // Refresh stats every 30 seconds
        autoRefreshInterval = setInterval(() => {
            updateLiveStats();
        }, 30000);
    }
}

function updateLiveStats() {
    fetch('{{ route("super-admin.telescope.stats") }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalEntries').textContent = new Intl.NumberFormat().format(data.total_entries);
            document.getElementById('todayEntries').textContent = new Intl.NumberFormat().format(data.today_entries);
            document.getElementById('queryCount').textContent = new Intl.NumberFormat().format(data.query_count);
            document.getElementById('modelCount').textContent = new Intl.NumberFormat().format(data.model_count);
            document.getElementById('slowQueries').textContent = new Intl.NumberFormat().format(data.slow_queries);
            document.getElementById('errorCount').textContent = new Intl.NumberFormat().format(data.error_count);
            document.getElementById('avgResponseTime').textContent = parseFloat(data.avg_response_time).toFixed(2) + 'ms';
        })
        .catch(error => console.error('Error updating stats:', error));
}

function exportEntries() {
    const url = new URL('{{ route("super-admin.telescope.export") }}', window.location.origin);
    const params = new URLSearchParams(window.location.search);
    url.search = params.toString();

    window.open(url, '_blank');
}

function deleteSingleEntry(uuid) {
    if (confirm('Are you sure you want to delete this entry?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("super-admin.telescope.bulk-delete") }}';

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';

        const entryId = document.createElement('input');
        entryId.type = 'hidden';
        entryId.name = 'entry_ids[]';
        entryId.value = uuid;

        form.appendChild(csrf);
        form.appendChild(entryId);
        document.body.appendChild(form);
        form.submit();
    }
}

function bulkDelete() {
    const selectedEntries = document.querySelectorAll('.entry-checkbox:checked');
    if (selectedEntries.length === 0) {
        alert('Please select entries to delete.');
        return;
    }

    if (confirm(`Are you sure you want to delete ${selectedEntries.length} entries?`)) {
        document.getElementById('bulkActionForm').submit();
    }
}

// Bulk selection functionality
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAllEntries');
    const entryCheckboxes = document.querySelectorAll('.entry-checkbox');
    const bulkActionsBar = document.getElementById('bulkActionsBar');
    const selectedCount = document.getElementById('selectedCount');

    function updateBulkActions() {
        const checkedBoxes = document.querySelectorAll('.entry-checkbox:checked');
        const count = checkedBoxes.length;

        if (count > 0) {
            bulkActionsBar.style.display = 'flex';
            selectedCount.textContent = `${count} entr${count === 1 ? 'y' : 'ies'} selected`;
        } else {
            bulkActionsBar.style.display = 'none';
        }
    }

    selectAllCheckbox.addEventListener('change', function() {
        entryCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
    });

    entryCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(entryCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(entryCheckboxes).some(cb => cb.checked);

            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = someChecked && !allChecked;

            updateBulkActions();
        });
    });

    // Initial stats update
    updateLiveStats();

    // Handle cleanup mode switching
    document.querySelectorAll('input[name="cleanup_mode"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const timeOptions = document.getElementById('timeOptions');
            const countOptions = document.getElementById('countOptions');

            if (this.value === 'time') {
                timeOptions.style.display = 'block';
                countOptions.style.display = 'none';
            } else {
                timeOptions.style.display = 'none';
                countOptions.style.display = 'block';
            }
        });
    });
});

function generateReport() {
    // Show loading state
    const generateBtn = event.target.closest('button');
    const originalText = generateBtn.innerHTML;
    generateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Generating...';
    generateBtn.disabled = true;

    fetch('{{ route("super-admin.telescope.stats") }}')
        .then(response => response.json())
        .then(data => {
            // Calculate additional metrics
            const totalEntries = data.total_entries || 0;
            const todayEntries = data.today_entries || 0;
            const yesterdayEntries = data.yesterday_entries || 0;
            const weekEntries = data.week_entries || 0;
            const monthEntries = data.month_entries || 0;

            const avgResponseTime = parseFloat(data.avg_response_time || 0);
            const slowQueries = data.slow_queries || 0;
            const errorCount = data.error_count || 0;
            const databaseSize = parseFloat(data.database_size || 0);

            // Calculate growth rates
            const dailyGrowth = yesterdayEntries > 0 ? ((todayEntries - yesterdayEntries) / yesterdayEntries * 100).toFixed(1) : 0;
            const weeklyGrowth = weekEntries > 0 ? ((totalEntries - weekEntries) / weekEntries * 100).toFixed(1) : 0;

            // Performance scores (0-100)
            const responseScore = Math.max(0, Math.min(100, 100 - (avgResponseTime / 10)));
            const errorScore = Math.max(0, Math.min(100, 100 - (errorCount * 5)));
            const queryScore = Math.max(0, Math.min(100, 100 - (slowQueries * 2)));
            const overallScore = Math.round((responseScore + errorScore + queryScore) / 3);

            // Generate report HTML
            const reportHtml = `
                <div class="performance-report">
                    <div class="report-header mb-4">
                        <h4 class="mb-1">📊 Smart Telescope Performance Report</h4>
                        <p class="text-muted mb-0">Generated on ${new Date().toLocaleString()}</p>
                        <div class="overall-score mt-3">
                            <h5>Overall Performance Score: <span class="badge ${overallScore >= 80 ? 'bg-success' : overallScore >= 60 ? 'bg-warning' : 'bg-danger'} fs-6">${overallScore}/100</span></h5>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">📈 Entry Statistics</h6>
                                </div>
                                <div class="card-body">
                                    <div class="metric-item d-flex justify-content-between align-items-center mb-2">
                                        <span>Total Entries:</span>
                                        <strong class="text-primary">${new Intl.NumberFormat().format(totalEntries)}</strong>
                                    </div>
                                    <div class="metric-item d-flex justify-content-between align-items-center mb-2">
                                        <span>Today's Entries:</span>
                                        <strong class="text-success">${new Intl.NumberFormat().format(todayEntries)}</strong>
                                    </div>
                                    <div class="metric-item d-flex justify-content-between align-items-center mb-2">
                                        <span>Yesterday's Entries:</span>
                                        <strong class="text-info">${new Intl.NumberFormat().format(yesterdayEntries)}</strong>
                                    </div>
                                    <div class="metric-item d-flex justify-content-between align-items-center mb-2">
                                        <span>This Week:</span>
                                        <strong class="text-warning">${new Intl.NumberFormat().format(weekEntries)}</strong>
                                    </div>
                                    <div class="metric-item d-flex justify-content-between align-items-center mb-2">
                                        <span>This Month:</span>
                                        <strong class="text-secondary">${new Intl.NumberFormat().format(monthEntries)}</strong>
                                    </div>
                                    <hr>
                                    <div class="metric-item d-flex justify-content-between align-items-center">
                                        <span>Daily Growth:</span>
                                        <strong class="${dailyGrowth >= 0 ? 'text-success' : 'text-danger'}">${dailyGrowth}%</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">⚡ Performance Metrics</h6>
                                </div>
                                <div class="card-body">
                                    <div class="metric-item d-flex justify-content-between align-items-center mb-2">
                                        <span>Avg Response Time:</span>
                                        <strong class="${avgResponseTime < 500 ? 'text-success' : avgResponseTime < 1000 ? 'text-warning' : 'text-danger'}">${avgResponseTime.toFixed(2)}ms</strong>
                                    </div>
                                    <div class="metric-item d-flex justify-content-between align-items-center mb-2">
                                        <span>Slow Queries (24h):</span>
                                        <strong class="${slowQueries < 5 ? 'text-success' : slowQueries < 15 ? 'text-warning' : 'text-danger'}">${new Intl.NumberFormat().format(slowQueries)}</strong>
                                    </div>
                                    <div class="metric-item d-flex justify-content-between align-items-center mb-2">
                                        <span>Errors (24h):</span>
                                        <strong class="${errorCount < 3 ? 'text-success' : errorCount < 10 ? 'text-warning' : 'text-danger'}">${new Intl.NumberFormat().format(errorCount)}</strong>
                                    </div>
                                    <div class="metric-item d-flex justify-content-between align-items-center mb-2">
                                        <span>Database Size:</span>
                                        <strong class="${databaseSize < 50 ? 'text-success' : databaseSize < 100 ? 'text-warning' : 'text-danger'}">${databaseSize.toFixed(2)} MB</strong>
                                    </div>
                                    <hr>
                                    <div class="metric-item d-flex justify-content-between align-items-center">
                                        <span>Response Score:</span>
                                        <strong class="${responseScore >= 80 ? 'text-success' : responseScore >= 60 ? 'text-warning' : 'text-danger'}">${Math.round(responseScore)}/100</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">🏷️ Entry Type Breakdown</h6>
                                </div>
                                <div class="card-body">
                                    <div class="metric-item d-flex justify-content-between align-items-center mb-2">
                                        <span>Database Queries:</span>
                                        <strong class="text-primary">${new Intl.NumberFormat().format(data.query_count || 0)}</strong>
                                    </div>
                                    <div class="metric-item d-flex justify-content-between align-items-center mb-2">
                                        <span>Model Events:</span>
                                        <strong class="text-info">${new Intl.NumberFormat().format(data.model_count || 0)}</strong>
                                    </div>
                                    <div class="metric-item d-flex justify-content-between align-items-center mb-2">
                                        <span>HTTP Requests:</span>
                                        <strong class="text-success">${new Intl.NumberFormat().format(data.request_count || 0)}</strong>
                                    </div>
                                    <div class="metric-item d-flex justify-content-between align-items-center mb-2">
                                        <span>Commands:</span>
                                        <strong class="text-warning">${new Intl.NumberFormat().format(data.command_count || 0)}</strong>
                                    </div>
                                    <div class="metric-item d-flex justify-content-between align-items-center mb-2">
                                        <span>Jobs:</span>
                                        <strong class="text-secondary">${new Intl.NumberFormat().format(data.job_count || 0)}</strong>
                                    </div>
                                    <div class="metric-item d-flex justify-content-between align-items-center">
                                        <span>Events:</span>
                                        <strong class="text-dark">${new Intl.NumberFormat().format(data.event_count || 0)}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0">📊 System Health</h6>
                                </div>
                                <div class="card-body">
                                    <div class="metric-item d-flex justify-content-between align-items-center mb-2">
                                        <span>Cache Operations:</span>
                                        <strong class="text-info">${new Intl.NumberFormat().format(data.cache_count || 0)}</strong>
                                    </div>
                                    <div class="metric-item d-flex justify-content-between align-items-center mb-2">
                                        <span>Log Entries:</span>
                                        <strong class="text-secondary">${new Intl.NumberFormat().format(data.log_count || 0)}</strong>
                                    </div>
                                    <div class="metric-item d-flex justify-content-between align-items-center mb-2">
                                        <span>Oldest Entry:</span>
                                        <strong class="text-muted">${data.oldest_entry ? new Date(data.oldest_entry).toLocaleDateString() : 'N/A'}</strong>
                                    </div>
                                    <div class="metric-item d-flex justify-content-between align-items-center mb-2">
                                        <span>Newest Entry:</span>
                                        <strong class="text-muted">${data.newest_entry ? new Date(data.newest_entry).toLocaleDateString() : 'N/A'}</strong>
                                    </div>
                                    <hr>
                                    <div class="metric-item d-flex justify-content-between align-items-center">
                                        <span>Storage Efficiency:</span>
                                        <strong class="${databaseSize < 100 ? 'text-success' : databaseSize < 500 ? 'text-warning' : 'text-danger'}">${databaseSize < 100 ? 'Optimal' : databaseSize < 500 ? 'Moderate' : 'High'}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-danger">
                        <div class="card-header bg-danger text-white">
                            <h6 class="mb-0">🎯 Recommendations</h6>
                        </div>
                        <div class="card-body">
                            <div class="recommendations">
                                ${generateRecommendations(data, avgResponseTime, slowQueries, errorCount, databaseSize, totalEntries)}
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Create and show modal
            const modalHtml = `
                <div class="modal fade" id="performanceReportModal" tabindex="-1">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header bg-gradient-primary text-white">
                                <h5 class="modal-title">📊 Performance Analysis Report</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                                ${reportHtml}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" onclick="exportReport()">Export Report</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Remove existing modal if present
            const existingModal = document.getElementById('performanceReportModal');
            if (existingModal) {
                existingModal.remove();
            }

            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', modalHtml);

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('performanceReportModal'));
            modal.show();

            // Reset button
            generateBtn.innerHTML = originalText;
            generateBtn.disabled = false;
        })
        .catch(error => {
            console.error('Error generating report:', error);
            alert('Error generating performance report. Please try again.');
            generateBtn.innerHTML = originalText;
            generateBtn.disabled = false;
        });
}

function generateRecommendations(data, avgResponseTime, slowQueries, errorCount, databaseSize, totalEntries) {
    let recommendations = [];

    if (avgResponseTime > 1000) {
        recommendations.push('<div class="alert alert-danger"><strong>🚨 Critical:</strong> Average response time is very high. Consider optimizing database queries and implementing caching.</div>');
    } else if (avgResponseTime > 500) {
        recommendations.push('<div class="alert alert-warning"><strong>⚠️ Warning:</strong> Response time is elevated. Review slow queries and consider query optimization.</div>');
    }

    if (slowQueries > 10) {
        recommendations.push('<div class="alert alert-danger"><strong>🚨 Critical:</strong> High number of slow queries detected. Add database indexes and optimize complex queries.</div>');
    } else if (slowQueries > 5) {
        recommendations.push('<div class="alert alert-warning"><strong>⚠️ Warning:</strong> Some slow queries detected. Monitor query performance.</div>');
    }

    if (errorCount > 5) {
        recommendations.push('<div class="alert alert-danger"><strong>🚨 Critical:</strong> High error rate detected. Review application logs and fix underlying issues.</div>');
    } else if (errorCount > 2) {
        recommendations.push('<div class="alert alert-warning"><strong>⚠️ Warning:</strong> Some errors detected. Monitor error patterns.</div>');
    }

    if (databaseSize > 500) {
        recommendations.push('<div class="alert alert-danger"><strong>🚨 Critical:</strong> Database size is very large. Implement data cleanup and archiving strategies.</div>');
    } else if (databaseSize > 100) {
        recommendations.push('<div class="alert alert-warning"><strong>⚠️ Warning:</strong> Database size is growing. Consider regular cleanup of old entries.</div>');
    }

    if (totalEntries > 100000) {
        recommendations.push('<div class="alert alert-info"><strong>ℹ️ Info:</strong> Large number of entries. Consider implementing data retention policies.</div>');
    }

    if (recommendations.length === 0) {
        recommendations.push('<div class="alert alert-success"><strong>✅ Excellent:</strong> All performance metrics are within optimal ranges. Keep up the good work!</div>');
    }

    return recommendations.join('');
}

function exportReport() {
    // Simple export - in a real implementation, this would generate a PDF or detailed report
    const reportData = {
        generated_at: new Date().toISOString(),
        url: window.location.href,
        user_agent: navigator.userAgent,
        note: 'Performance report exported from Smart Telescope Management'
    };

    const dataStr = JSON.stringify(reportData, null, 2);
    const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);

    const exportFileDefaultName = `telescope-performance-report-${new Date().toISOString().split('T')[0]}.json`;

    const linkElement = document.createElement('a');
    linkElement.setAttribute('href', dataUri);
    linkElement.setAttribute('download', exportFileDefaultName);
    linkElement.click();

    // Show success message
    const toastHtml = `
        <div class="toast align-items-center text-white bg-success border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">Performance report exported successfully!</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;

    const toastContainer = document.createElement('div');
    toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
    toastContainer.innerHTML = toastHtml;
    document.body.appendChild(toastContainer);

    const toast = new bootstrap.Toast(toastContainer.querySelector('.toast'));
    toast.show();

    setTimeout(() => {
        toastContainer.remove();
    }, 3000);
}

function checkDbHealth() {
    fetch('{{ route("super-admin.telescope.stats") }}')
        .then(response => response.json())
        .then(data => {
            let healthMessage = '🗄️ Database Health Check\n\n';
            healthMessage += `📊 Database Size: ${data.database_size} MB\n`;
            healthMessage += `🔍 Total Entries: ${new Intl.NumberFormat().format(data.total_entries)}\n`;
            healthMessage += `⚡ Slow Queries (24h): ${new Intl.NumberFormat().format(data.slow_queries)}\n`;
            healthMessage += `❌ Errors (24h): ${new Intl.NumberFormat().format(data.error_count)}\n`;
            healthMessage += `📈 Avg Response Time: ${data.avg_response_time}ms\n\n`;

            if (data.database_size > 100) {
                healthMessage += '⚠️ Database size is large. Consider cleanup.\n';
            }
            if (data.slow_queries > 10) {
                healthMessage += '⚠️ High number of slow queries detected.\n';
            }
            if (data.error_count > 5) {
                healthMessage += '⚠️ Multiple errors detected recently.\n';
            }

            alert(healthMessage);
        })
        .catch(error => {
            alert('Error checking database health: ' + error.message);
        });
}
</script>
@endsection
