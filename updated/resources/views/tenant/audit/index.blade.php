@extends('layouts.app')

@section('content')
<style>
    /* Muonekano wa Kisasa wa SaaS */
    .audit-container {
        background-color: #f8fafc;
        min-height: 100vh;
        padding: 1.5rem;
    }

    .stat-card {
        border: none;
        border-radius: 12px;
        transition: transform 0.2s;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .audit-table-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
    }

    /* Rangi Laini (Soft Colors) */
    .bg-soft-indigo {
        background-color: #eef2ff;
        color: #4f46e5;
    }

    .bg-soft-emerald {
        background-color: #ecfdf5;
        color: #10b981;
    }

    .bg-soft-amber {
        background-color: #fffbeb;
        color: #f59e0b;
    }

    .bg-soft-rose {
        background-color: #fff1f2;
        color: #e11d48;
    }

    .icon-box {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }

    .table thead th {
        background-color: #f1f5f9;
        text-transform: uppercase;
        font-size: 0.7rem;
        font-weight: 700;
        color: #64748b;
        letter-spacing: 0.05em;
    }

    .badge-pill {
        padding: 0.4em 0.8em;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.75rem;
    }
</style>

<div class="audit-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Audit Trail</h2>
            <p class="text-muted mb-0">Monitor all system activities and security events.</p>
        </div>
        <button class="btn btn-white border shadow-sm rounded-pill px-4" onclick="window.location.reload()">
            <i class="fas fa-sync-alt me-2"></i> Refresh
        </button>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box bg-soft-indigo me-3"><i class="fas fa-bolt fa-lg"></i></div>
                    <div>
                        <small class="text-muted fw-bold text-uppercase">Today</small>
                        <h3 class="fw-bold mb-0">{{ number_format($totalToday) }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box bg-soft-emerald me-3"><i class="fas fa-user-check fa-lg"></i></div>
                    <div>
                        <small class="text-muted fw-bold text-uppercase">Top Actor</small>
                        <h6 class="fw-bold mb-0 text-truncate" style="max-width: 120px;">{{ $activeUserName }}</h6>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box bg-soft-amber me-3"><i class="fas fa-clock fa-lg"></i></div>
                    <div>
                        <small class="text-muted fw-bold text-uppercase">Last Action</small>
                        <h6 class="fw-bold mb-0 small">{{ $lastActiveTime }}</h6>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box bg-soft-rose me-3"><i class="fas fa-database fa-lg"></i></div>
                    <div>
                        <small class="text-muted fw-bold text-uppercase">Total Logs</small>
                        <h3 class="fw-bold mb-0">{{ number_format($logs->total()) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($logs->isEmpty())
    <div class="card audit-table-card py-5 text-center">
        <div class="opacity-25 mb-3"><i class="fas fa-shield-alt fa-4x"></i></div>
        <h5 class="text-muted">No activity recorded yet.</h5>
    </div>
    @else
    <div class="card audit-table-card">
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">User Details</th>
                        <th>Action & Object</th>
                        <th>Duka</th>
                        <th>Details</th>
                        <th class="text-end pe-4">Options</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    @php
                    $badge = match($log->event) {
                    'created' => ['success', 'plus'],
                    'updated' => ['primary', 'edit'],
                    'deleted' => ['danger', 'trash'],
                    default => ['secondary', 'info-circle']
                    };
                    @endphp
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle p-2 me-3 text-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-user text-muted"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $log->causer->name ?? 'System' }}</div>
                                    <div class="text-muted small">{{ $log->ip_address }} • {{ $log->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-pill bg-{{ $badge[0] }} mb-1">
                                <i class="fas fa-{{ $badge[1] }} me-1"></i> {{ ucfirst($log->event) }}
                            </span>
                            <div class="fw-bold small text-dark">{{ class_basename($log->subject_type) }}</div>
                        </td>
                        <td>
                            <div class="small fw-medium text-secondary">
                                <i class="fas fa-store me-1 opacity-50"></i> {{ $log->duka->name ?? 'System' }}
                            </div>
                        </td>
                        <td>
                            @php $changeCount = count($log->properties['attributes'] ?? []); @endphp
                            <span class="text-muted small">
                                <i class="fas fa-list-ul me-1"></i> {{ $changeCount }} items affected
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-light border rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modal{{ $log->id }}">
                                Details
                            </button>
                        </td>
                    </tr>

                    <!-- Soft UI Modal -->
                    <div class="modal fade" id="modal{{ $log->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                                <div class="modal-header border-0 bg-soft-indigo px-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-box bg-white text-primary me-3 rounded-circle shadow-sm" style="width: 40px; height: 40px;">
                                            <i class="fas fa-search"></i>
                                        </div>
                                        <div>
                                            <h5 class="modal-title fw-bold text-dark mb-0">Audit Details</h5>
                                            <small class="text-secondary opacity-75">ID: #{{ $log->id }} • {{ $log->created_at->format('M d, Y h:i A') }}</small>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4 bg-light">
                                    <div class="row g-3 mb-4">
                                        <!-- Actor Card -->
                                        <div class="col-md-6">
                                            <div class="card border-0 shadow-sm h-100">
                                                <div class="card-body p-3">
                                                    <h6 class="text-uppercase text-muted small fw-bold mb-3"><i class="fas fa-user-circle me-1"></i> Actor</h6>
                                                    <div class="d-flex align-items-center mb-2">
                                                        <div class="fw-bold text-dark">{{ $log->causer->name ?? 'System' }}</div>
                                                        <span class="badge bg-light text-dark border ms-2 small">{{ $log->causer->role ?? 'N/A' }}</span>
                                                    </div>
                                                    <div class="small text-muted mb-1"><i class="fas fa-globe me-1 w-20"></i> {{ $log->ip_address }}</div>
                                                    <div class="small text-muted text-truncate" title="{{ $log->user_agent }}"><i class="fas fa-desktop me-1 w-20"></i> {{ Str::limit($log->user_agent, 40) }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Target Card -->
                                        <div class="col-md-6">
                                            <div class="card border-0 shadow-sm h-100">
                                                <div class="card-body p-3">
                                                    <h6 class="text-uppercase text-muted small fw-bold mb-3"><i class="fas fa-bullseye me-1"></i> Target</h6>
                                                    <div class="d-flex align-items-center mb-2">
                                                        <span class="badge bg-{{ $badge[0] }} me-2">{{ ucfirst($log->event) }}</span>
                                                        <span class="fw-bold text-dark">{{ class_basename($log->subject_type) }}</span>
                                                    </div>
                                                    <div class="small text-muted mb-1"><i class="fas fa-store me-1 w-20"></i> {{ $log->duka->name ?? 'System' }}</div>
                                                    <div class="small text-muted"><i class="fas fa-info-circle me-1 w-20"></i> {{ $log->description_human }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                                            <h6 class="text-uppercase text-muted small fw-bold"><i class="fas fa-exchange-alt me-1"></i> Data Changes</h6>
                                        </div>
                                        <div class="card-body p-0">
                                            @php $props = $log->properties; @endphp

                                            @if(isset($props['old']) && isset($props['attributes']))
                                            <!-- Update Difference Table -->
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0">
                                                    <thead class="bg-light">
                                                        <tr>
                                                            <th class="ps-4 text-secondary small text-uppercase">Field</th>
                                                            <th class="text-danger small text-uppercase w-35">Old Value</th>
                                                            <th class="text-success small text-uppercase w-35">New Value</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach(array_unique(array_merge(array_keys($props['old']??[]), array_keys($props['attributes']??[]))) as $key)
                                                        @if(in_array($key, ['updated_at', 'created_at', 'deleted_at', 'id', 'tenant_id', 'duka_id'])) @continue @endif
                                                        <tr>
                                                            <td class="ps-4 fw-medium text-dark">{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                                                            <td class="text-danger bg-soft-rose small text-break">{{ $props['old'][$key] ?? '-' }}</td>
                                                            <td class="text-success bg-soft-emerald small fw-bold text-break">{{ $props['attributes'][$key] ?? '-' }}</td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            @elseif(isset($props['attributes']))
                                            <!-- Creation Attribute List -->
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0">
                                                    <thead class="bg-light">
                                                        <tr>
                                                            <th class="ps-4 text-secondary small text-uppercase w-40">Field</th>
                                                            <th class="text-secondary small text-uppercase">Value</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($props['attributes'] as $key => $value)
                                                        @if(in_array($key, ['updated_at', 'created_at', 'deleted_at', 'id', 'tenant_id', 'duka_id'])) @continue @endif
                                                        <tr>
                                                            <td class="ps-4 fw-medium text-dark">{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                                                            <td class="text-secondary small text-break">{{ is_array($value) ? json_encode($value) : $value }}</td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            @else
                                            <div class="p-3">
                                                <pre class="bg-light p-3 rounded text-muted small mb-0">{{ json_encode($props, JSON_PRETTY_PRINT) }}</pre>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 bg-white py-2">
                                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold text-muted" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-top-0 py-3">
            {{ $logs->links() }}
        </div>
    </div>
    @endif
</div>
@endsection
