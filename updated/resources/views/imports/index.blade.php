@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Import History & Rollback</h1>
    </div>

    <!-- Legacy Cleanup Section (Visible if user has 0-profit sales today) -->
    <div class="card border-left-danger shadow mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="fw-bold text-danger">Cleanup Failed Imports</h5>
                    <p class="mb-0 text-muted">Did you upload sales today that show <strong>0 Profit</strong>? Use this tool to remove them and reset your stock before re-importing.</p>
                </div>
                <div class="col-auto">
                    <form action="{{ route('imports.cleanup_legacy') }}" method="POST" onsubmit="return confirm('Are you sure? This will delete ALL sales imported today that have 0 Profit.');">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i> Delete Invalid Sales (Today)
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Future Imports Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Recent Import Batches</h6>
        </div>
        <div class="card-body">
            @if($batches->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Import Date</th>
                            <th>Records</th>
                            <th>Total Value</th>
                            <th>Sort Key</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($batches as $batch)
                        <tr>
                            <td>
                                <strong>{{ $batch->created_at->format('M d, Y H:i') }}</strong><br>
                                <small class="text-muted">{{ $batch->created_at->diffForHumans() }}</small>
                            </td>
                            <td>{{ $batch->count }} Sales</td>
                            <td>{{ number_format($batch->total, 2) }}</td>
                            <td><small class="text-xs font-monospace">{{ Str::limit($batch->import_batch, 8) }}</small></td>
                            <td>
                                <form action="{{ route('imports.rollback', $batch->import_batch) }}" method="POST" onsubmit="return confirm('WARNING: This will delete {{ $batch->count }} sales and reverse stock changes. Continue?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-undo me-1"></i> Rollback
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5 text-gray-500">
                <i class="fas fa-history fa-3x mb-3"></i>
                <p>No tracked import batches found.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection