@extends('layouts.super-admin')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Permission Details: {{ $permission->display_name }}</h4>
                <div>
                    <a href="{{ route('super-admin.available-permissions.edit', $permission->id) }}" class="btn btn-warning">Edit Permission</a>
                    <a href="{{ route('super-admin.available-permissions.index') }}" class="btn btn-secondary">Back to Permissions</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5>Permission Information</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>ID:</th>
                                        <td>{{ $permission->id }}</td>
                                    </tr>
                                    <tr>
                                        <th>Name:</th>
                                        <td><code>{{ $permission->name }}</code></td>
                                    </tr>
                                    <tr>
                                        <th>Display Name:</th>
                                        <td>{{ $permission->display_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Description:</th>
                                        <td>{{ $permission->description ?? 'No description' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td>
                                            @if($permission->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Created:</th>
                                        <td>{{ $permission->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Last Updated:</th>
                                        <td>{{ $permission->updated_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>Usage Statistics</h5>
                            </div>
                            <div class="card-body">
                                <div class="text-center">
                                    <h3 class="text-primary">{{ $permission->staffPermissions()->count() }}</h3>
                                    <p class="text-muted">Officers with this permission</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Officers with this permission -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Officers with This Permission ({{ $permission->staffPermissions->count() }})</h5>
                            </div>
                            <div class="card-body">
                                @if($permission->staffPermissions->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Officer Name</th>
                                                    <th>Email</th>
                                                    <th>Tenant</th>
                                                    <th>Assigned Dukas</th>
                                                    <th>Granted At</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($permission->staffPermissions as $staffPermission)
                                                <tr>
                                                    <td>{{ $staffPermission->officer->name }}</td>
                                                    <td>{{ $staffPermission->officer->email }}</td>
                                                    <td>{{ $staffPermission->tenant->name ?? 'N/A' }}</td>
                                                    <td>{{ $staffPermission->officer->officerAssignments->count() }} dukas</td>
                                                    <td>{{ $staffPermission->created_at->format('M d, Y') }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted">This permission is not currently assigned to any officers.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
