@extends('layouts.super-admin')

@section('title', 'Manage Available Permissions')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0" data-aos="fade-up">
                <div class="card-header d-flex justify-content-between align-items-center bg-white py-3">
                    <div class="header-title">
                        <h4 class="card-title text-dark fw-800 mb-0">Available Permissions</h4>
                        <p class="text-muted small mb-0">Define and group granular actions into plan features.</p>
                    </div>
                    <a href="{{ route('super-admin.available-permissions.create') }}" class="btn btn-primary shadow-sm">
                        <i class="ri-add-line me-1"></i> Add New Permission
                    </a>
                </div>

                <div class="card-body">
                    @if (session()->has('success'))
                        <div class="alert alert-soft-success alert-dismissible fade show" role="alert">
                            <i class="ri-checkbox-circle-line me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <form method="GET" action="{{ route('super-admin.available-permissions.index') }}"
                                class="d-flex">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i
                                            class="ri-search-line text-muted"></i></span>
                                    <input type="text" name="search" class="form-control border-start-0 ps-0"
                                        placeholder="Search by name or code..." value="{{ request('search') }}">
                                    <button type="submit" class="btn btn-dark px-4">Search</button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="{{ route('super-admin.available-permissions.index') }}"
                                class="btn btn-outline-secondary btn-sm rounded-pill">
                                <i class="ri-refresh-line"></i> Clear Filters
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive border rounded">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3" style="width: 80px;">ID</th>
                                    <th>Permission Details</th>
                                    <th>Feature Group</th>
                                    <th>Plans</th>
                                    <th>Model</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($permissions as $permission)
                                    <tr>
                                        <td class="ps-3 text-muted">#{{ $permission->id }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $permission->display_name }}</div>
                                            <code>{{ $permission->name }}</code>
                                        </td>
                                        <td>
                                            @if ($permission->feature)
                                                <div class="d-flex align-items-center">
                                                    <div class="p-1 bg-soft-primary rounded me-2">
                                                        <i class="ri-stack-line text-primary"></i>
                                                    </div>
                                                    <span
                                                        class="text-primary fw-600">{{ $permission->feature->name }}</span>
                                                </div>
                                            @else
                                                <span class="badge bg-soft-secondary text-secondary rounded-pill">
                                                    <i class="ri-info-i me-1"></i> General Access
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($permission->feature && $permission->feature->plans->count() > 0)
                                                @foreach ($permission->feature->plans as $plan)
                                                    <span class="badge bg-soft-info text-info me-1">{{ $plan->name }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($permission->model)
                                                <code>{{ $permission->model }}</code>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($permission->is_active)
                                                <span class="badge bg-success shadow-none">Active</span>
                                            @else
                                                <span class="badge bg-danger shadow-none">Disabled</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="btn-group shadow-none">
                                                <button type="button" class="btn btn-sm btn-outline-primary border-0"
                                                    data-bs-toggle="modal" data-bs-target="#assignFeatureModal"
                                                    onclick="fillModalData({{ $permission->id }}, '{{ $permission->display_name }}', '{{ $permission->feature_id }}')">
                                                    <i class="ri-links-line"></i> Assign
                                                </button>
                                                <a href="{{ route('super-admin.available-permissions.edit', $permission->id) }}"
                                                    class="btn btn-sm btn-outline-warning border-0" title="Edit">
                                                    <i class="ri-pencil-line"></i>
                                                </a>

                                                <form method="POST"
                                                    action="{{ route('super-admin.available-permissions.destroy', $permission->id) }}"
                                                    class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger border-0"
                                                        onclick="return confirm('Deleting this will affect all staff assigned this permission. Proceed?')"
                                                        title="Delete">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="ri-shield-line h1 opacity-25"></i>
                                            <p class="mt-2">No permissions found matching your criteria.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $permissions->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="assignFeatureModal" tabindex="-1" aria-labelledby="assignFeatureModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form id="assignFeatureForm" method="POST" action="">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header bg-dark text-white border-0">
                        <h5 class="modal-title text-white" id="assignFeatureModalLabel">Grouping Permission</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <div
                            class="avatar avatar-60 bg-soft-primary rounded-pill mb-3 mx-auto d-flex align-items-center justify-content-center">
                            <i class="ri-settings-4-line h3 text-primary mb-0"></i>
                        </div>
                        <h5>Link to Subscription Feature</h5>
                        <p class="text-muted">Select the module that unlocks <strong id="modalPermName"
                                class="text-dark"></strong>.</p>

                        <div class="form-group mt-4 text-start">
                            <label class="form-label fw-bold">Select Feature Module</label>
                            <select name="feature_id" class="form-select form-select-lg border-primary">
                                <option value="">-- General Access (All Plans) --</option>
                                @foreach ($features as $feature)
                                    <option value="{{ $feature->id }}">
                                        {{ $feature->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-2">
                                <i class="ri-information-line"></i>
                                Grouping a permission under a feature limits its use to Tenants who have purchased a Plan
                                containing that feature.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0 justify-content-between">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">Save Assignment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function fillModalData(id, displayName, currentFeatureId) {
                // Find the form
                const form = document.getElementById('assignFeatureForm');
                if (!form) return;

                // 1. Update the action URL
                form.action = `/super-admin/available-permissions/${id}/assign-feature`;

                // 2. Update text label
                const nameLabel = document.getElementById('modalPermName');
                if (nameLabel) nameLabel.innerText = displayName;

                // 3. Set dropdown value
                const select = form.querySelector('select[name="feature_id"]');
                if (select) select.value = currentFeatureId || "";
            }

            // Optional: Log to console to verify scripts are loading
            console.log("Permission Index Scripts Loaded");
        </script>
    @endpush
@endsection
