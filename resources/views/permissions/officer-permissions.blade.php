@extends('layouts.app')

@section('title', 'Manage Officer Permissions')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Manage Permissions: {{ $officer->name }}</h4>
                        <a href="{{ route('permissions.index') }}" class="btn btn-secondary btn-sm">Back to Permissions</a>
                    </div>
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

                    <!-- Officer Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>Officer Information</h6>
                                    <p><strong>Name:</strong> {{ $officer->name }}</p>
                                    <p><strong>Email:</strong> {{ $officer->email }}</p>
                                    <p><strong>Role:</strong> {{ $officer->roles->first()?->name ?? 'Officer' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>Permission Summary</h6>
                                    <p><strong>Total Available Permissions:</strong> {{ $availablePermissions->count() }}</p>
                                    <p><strong>Granted Permissions:</strong> {{ $permissions->where('is_granted', true)->count() }}</p>
                                    <p><strong>Revoked Permissions:</strong> {{ $permissions->where('is_granted', false)->count() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Duka Details -->
                    @if($officerDukas->isNotEmpty())
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5>Duka Information</h5>
                            <div class="row">
                                @foreach($officerDukas as $assignment)
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">{{ $assignment->duka->name }}</h6>
                                            <p class="card-text">
                                                <strong>Location:</strong> {{ $assignment->duka->location }}<br>
                                                <strong>Manager:</strong> {{ $assignment->duka->manager_name }}<br>
                                                <strong>Plan:</strong>

                                                <br>
                                                <strong>Status:</strong> {{ $assignment->duka->status }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Permission Management Form -->
                    <form action="{{ route('permissions.officer.update', $officer->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-12">
                                <h5>Available Permissions</h5>
                                <p class="text-muted">Select the permissions you want to grant to this officer.</p>

                                <div class="row">
                                    @foreach($availablePermissions as $permission)
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                        name="permissions[]" value="{{ $permission->name }}"
                                                        id="permission_{{ $permission->name }}"
                                                        {{ $permissions->has($permission->name) && $permissions[$permission->name]->is_granted ? 'checked' : '' }}>
                                                <label class="form-check-label" for="permission_{{ $permission->name }}">
                                                    <strong>{{ $permission->display_name }}</strong><br>
                                                    <small class="text-muted">{{ $permission->description }}</small>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-exclamation-triangle"></i> Important Notes</h6>
                                    <ul class="mb-0">
                                        <li>Permissions are applied to all dukas where this officer is assigned.</li>
                                        <li>Changes take effect immediately.</li>
                                        <li>Make sure you understand the implications of granting these permissions.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                Update Permissions
                            </button>
                            <a href="{{ route('permissions.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelector('form').addEventListener('submit', function() {
    const submitBtn = document.getElementById('submitBtn');
    const spinner = submitBtn.querySelector('.spinner-border');
    const text = submitBtn.querySelector('span:not(.spinner-border)');

    // Show spinner and disable button
    spinner.classList.remove('d-none');
    submitBtn.disabled = true;
    text.textContent = ' Updating...';
});
</script>
@endsection
