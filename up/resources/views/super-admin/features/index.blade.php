@extends('layouts.super-admin')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Features Management</h4>
                <a href="{{ route('super-admin.features.create') }}" class="btn btn-primary">Add New Feature</a>
            </div>
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Assigned Plans</th> <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($features as $feature)
                            <tr>
                                <td><code>{{ $feature->code }}</code></td>
                                <td><strong>{{ $feature->name }}</strong></td>
                                <td>
                                    @forelse($feature->plans as $plan)
                                        <span class="badge bg-soft-primary text-primary border border-primary-subtle">
                                            {{ $plan->name }}
                                        </span>
                                    @empty
                                        <span class="text-muted small">Not in any Plan</span>
                                    @endforelse
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-dark"
                                                onclick="openPlanModal({{ $feature->id }}, '{{ $feature->name }}', {{ $feature->plans->pluck('id') }})">
                                            <i class="ri-price-tag-3-line"></i> Plans
                                        </button>
                                        <a href="{{ route('super-admin.features.edit', $feature->id) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center">No features found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $features->links() }}
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="assignPlanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="assignPlanForm" method="POST" action="">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white">Assign Module to Plans</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-3 text-muted">Select which subscription tiers include the <strong id="featureNameLabel" class="text-dark"></strong> module:</p>

                    <div class="row g-3">
                        @foreach($plans as $plan)
                        <div class="col-6">
                            <div class="form-check form-switch p-3 border rounded">
                                <input class="form-check-input plan-checkbox" type="checkbox"
                                       name="plan_ids[]" value="{{ $plan->id }}" id="plan_{{ $plan->id }}">
                                <label class="form-check-label fw-bold" for="plan_{{ $plan->id }}">
                                    {{ $plan->name }}
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openPlanModal(featureId, featureName, assignedPlanIds) {
        const form = document.getElementById('assignPlanForm');
        // Dynamic route for assignment
        form.action = `/super-admin/features/${featureId}/assign-plans`;

        document.getElementById('featureNameLabel').innerText = featureName;

        // Reset and then check assigned plans
        document.querySelectorAll('.plan-checkbox').forEach(cb => {
            cb.checked = assignedPlanIds.includes(parseInt(cb.value));
        });

        new bootstrap.Modal(document.getElementById('assignPlanModal')).show();
    }
</script>
@endpush
@endsection
