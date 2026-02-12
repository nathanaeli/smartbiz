@extends('layouts.app')

@section('title', 'Assign Permissions')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Staff Permissions</h4>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif


                

                    <!-- Staff Permission Management -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>Staff Permission Management</h5>
                            <p class="text-muted">Manage permissions for officers working in your dukas.</p>

                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Officer Name</th>
                                            <th>Email</th>
                                            <th>Assigned Dukas</th>
                                            <th>Current Permissions</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($officers as $officer)
                                            <tr>
                                                <td>{{ $officer->officer->name }}</td>
                                                <td>{{ $officer->officer->email }}</td>
                                                <td>
                                                    @php
                                                        $officerDukas = $officer->officer->officerAssignments->pluck('duka.name')->join(', ');
                                                    @endphp
                                                    {{ $officerDukas }}
                                                </td>
                                                <td>
                                                    @php
                                                        $permissionCount = \App\Models\StaffPermission::where('tenant_id', auth()->user()->tenant->id)
                                                                                                    ->where('officer_id', $officer->officer->id)
                                                                                                    ->where('is_granted', true)
                                                                                                    ->count();
                                                    @endphp
                                                    <span class="badge bg-primary">{{ $permissionCount }} permissions</span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('permissions.officer.show', $officer->officer->id) }}" class="btn btn-sm btn-outline-primary">
                                                        Manage Permissions
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-3">
                                                No officers found.
                                            </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Plan Details Modal -->
<div class="modal fade" id="planDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Plan Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="planDetailsContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function checkPlanDetails(dukaId) {
    fetch(`/permissions/duka/${dukaId}/plan`)
        .then(response => response.json())
        .then(data => {
            let content = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Duka Information</h6>
                        <p><strong>Name:</strong> ${data.duka.name}</p>
                        <p><strong>Location:</strong> ${data.duka.location}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Plan Information</h6>
                        ${data.plan ? `
                            <p><strong>Plan:</strong> ${data.plan.name}</p>
                        ` : '<p class="text-muted">No plan assigned</p>'}
                    </div>
                </div>
            `;

            if (data.features && Object.keys(data.features).length > 0) {
                content += `
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <h6>Plan Features</h6>
                            <ul>
                `;
                for (let [feature, value] of Object.entries(data.features)) {
                    content += `<li><strong>${feature}:</strong> ${value}</li>`;
                }
                content += `
                            </ul>
                        </div>
                    </div>
                `;
            }

            document.getElementById('planDetailsContent').innerHTML = content;
            new bootstrap.Modal(document.getElementById('planDetailsModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading plan details');
        });
}
</script>
@endsection
