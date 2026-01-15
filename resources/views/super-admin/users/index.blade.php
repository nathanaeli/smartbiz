@extends('layouts.super-admin')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="bg-primary rounded-circle p-2 me-3">
                        <svg width="24" height="24" fill="white" viewBox="0 0 16 16">
                            <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7Zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-5.784 6A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216ZM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="mb-0">Users Management</h4>
                        <small class="text-muted">Manage system users and their permissions</small>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <div class="text-end me-3">
                        <div class="fw-bold text-primary">{{ $users->total() }}</div>
                        <small class="text-muted">Total Users</small>
                    </div>
                    <a href="#" class="btn btn-primary d-flex align-items-center">
                        <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                        </svg>
                        Add New User
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Success/Error Messages -->
                @if (session()->has('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <form method="GET" action="{{ route('super-admin.users.index') }}" class="d-flex">
                            <input type="text" name="search" class="form-control me-2"
                                   placeholder="Search users by name or email..."
                                   value="{{ request('search') }}">
                            <button type="submit" class="btn btn-outline-primary">Search</button>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <form method="GET" action="{{ route('super-admin.users.index') }}">
                            <select name="role" class="form-select" onchange="this.form.submit()">
                                <option value="">All Roles</option>
                                <option value="superadmin" {{ request('role') == 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                                <option value="tenant" {{ request('role') == 'tenant' ? 'selected' : '' }}>Tenant</option>
                                <option value="officer" {{ request('role') == 'officer' ? 'selected' : '' }}>Officer</option>
                                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <form method="GET" action="{{ route('super-admin.users.index') }}">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </form>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('super-admin.users.index') }}" class="btn btn-outline-secondary">Clear Filters</a>
                    </div>
                </div>

                <!-- Bulk Actions Bar -->
                <div id="bulk-actions" class="d-none mb-3 p-3 bg-light rounded">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="select-all-pages">
                                <label class="form-check-label fw-bold" for="select-all-pages">
                                    Select all {{ $users->total() }} users (across all pages)
                                </label>
                            </div>
                            <span id="selected-count" class="fw-bold">0</span> users selected
                        </div>
                        <div>
                            <button type="button" id="bulk-delete-btn" class="btn btn-danger me-2">
                                <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                    <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                </svg>
                                <span id="delete-btn-text">Delete Selected</span>
                            </button>
                            <button type="button" id="cancel-selection" class="btn btn-secondary">
                                <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                    <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/>
                                </svg>
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th class="border-0 text-center" style="width: 50px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="select-all">
                                        <label class="form-check-label text-white" for="select-all">
                                            <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
                                                <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
                                            </svg>
                                        </label>
                                    </div>
                                </th>
                                <th class="border-0">
                                    <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                        <path d="M5 4a1 1 0 0 0-.894.553L2.382 8H1a1 1 0 0 0 0 2h1.618l1.447 2.894A1 1 0 0 0 5 13h6a1 1 0 0 0 .894-.553L13.618 10H15a1 1 0 0 0 0-2h-1.382l-1.724-3.447A1 1 0 0 0 11 4H5z"/>
                                    </svg>
                                    ID
                                </th>
                                <th class="border-0">
                                    <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4Zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10Z"/>
                                    </svg>
                                    Name
                                </th>
                                <th class="border-0">
                                    <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                        <path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414.05 3.555ZM0 4.697v7.104l5.803-3.558L0 4.697ZM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586l-1.239-.757Zm3.436-.586L16 11.801V4.697l-5.803 3.546Z"/>
                                    </svg>
                                    Email
                                </th>
                                <th class="border-0">
                                    <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                        <path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zM2.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zM1 10.5A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3z"/>
                                    </svg>
                                    Role
                                </th>
                                <th class="border-0">
                                    <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                        <path d="M8 9.05a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z"/>
                                        <path d="M2 2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H2zm10.5 5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5z"/>
                                    </svg>
                                    Status
                                </th>
                                <th class="border-0">
                                    <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                        <path d="M2.5 3A1.5 1.5 0 0 0 1 4.5v.793c.026.009.051.02.076.032L7.674 8.51c.206.1.446.1.652 0l6.598-3.185A.755.755 0 0 1 15 5.293V4.5A1.5 1.5 0 0 0 13.5 3h-11Z"/>
                                        <path d="M15 6.954 8.978 9.86a2.25 2.25 0 0 1-1.956 0L1 6.954V11.5A1.5 1.5 0 0 0 2.5 13h11a1.5 1.5 0 0 0 1.5-1.5V6.954Z"/>
                                    </svg>
                                    Verified
                                </th>
                                <th class="border-0">
                                    <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                        <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                                    </svg>
                                    Created
                                </th>
                                <th class="border-0">
                                    <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                        <path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zM2.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3z"/>
                                    </svg>
                                    Plan
                                </th>
                                <th class="border-0">
                                    <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                        <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                                    </svg>
                                    Days Left
                                </th>
                                <th class="border-0 text-center">
                                    <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                        <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/>
                                    </svg>
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td class="text-center">
                                    @if(!$user->hasRole('superadmin') || auth()->id() !== $user->id)
                                        <div class="form-check">
                                            <input class="form-check-input user-checkbox" type="checkbox" value="{{ $user->id }}" id="user-{{ $user->id }}">
                                            <label class="form-check-label" for="user-{{ $user->id }}"></label>
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->roles->count() > 0)
                                        @foreach($user->roles as $role)
                                            @php
                                                $roleClass = match($role->name) {
                                                    'superadmin' => 'bg-danger',
                                                    'tenant' => 'bg-info',
                                                    'officer' => 'bg-warning',
                                                    'admin' => 'bg-success',
                                                    default => 'bg-secondary'
                                                };
                                                $roleIcon = match($role->name) {
                                                    'superadmin' => '<svg width="10" height="10" fill="currentColor" viewBox="0 0 16 16"><path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l2.147 2.146a.5.5 0 0 0 .707-.708L10.707 8l2.147-2.146a.5.5 0 0 0-.707-.708L10.293 7.5H4.5z"/></svg>',
                                                    'tenant' => '<svg width="10" height="10" fill="currentColor" viewBox="0 0 16 16"><path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zM2.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3z"/></svg>',
                                                    'officer' => '<svg width="10" height="10" fill="currentColor" viewBox="0 0 16 16"><path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4Zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10Z"/></svg>',
                                                    'admin' => '<svg width="10" height="10" fill="currentColor" viewBox="0 0 16 16"><path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3Zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/></svg>',
                                                    default => '<svg width="10" height="10" fill="currentColor" viewBox="0 0 16 16"><path d="M8 9.05a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z"/><path d="M2 2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H2zm10.5 5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5z"/></svg>'
                                                };
                                            @endphp
                                            <span class="badge {{ $roleClass }} me-1 d-inline-flex align-items-center">
                                                {!! $roleIcon !!} <span class="ms-1">{{ ucfirst($role->name) }}</span>
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="badge bg-secondary">
                                            <svg width="10" height="10" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                                                <path d="M8 9.05a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z"/>
                                                <path d="M2 2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H2zm10.5 5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5z"/>
                                            </svg>
                                            No Role
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->status === 'active')
                                        <span class="badge bg-success d-inline-flex align-items-center">
                                            <svg width="10" height="10" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                                                <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
                                            </svg>
                                            Active
                                        </span>
                                    @else
                                        <span class="badge bg-danger d-inline-flex align-items-center">
                                            <svg width="10" height="10" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                                <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
                                            </svg>
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->email_verified_at)
                                        <span class="badge bg-success d-inline-flex align-items-center">
                                            <svg width="10" height="10" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                                                <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
                                            </svg>
                                            Verified
                                        </span>
                                    @else
                                        <span class="badge bg-warning d-inline-flex align-items-center">
                                            <svg width="10" height="10" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                                <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
                                            </svg>
                                            Unverified
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}</td>
                                <td>
                                    @if($user->hasRole('tenant') && $user->tenant)
                                        @php
                                            $activeSubscription = $user->tenant->dukas->flatMap->dukaSubscriptions->where('status', 'active')->where('end_date', '>=', now()->toDateString())->first();
                                        @endphp
                                        @if($activeSubscription)
                                            {{ $activeSubscription->plan->name ?? 'N/A' }}
                                        @else
                                            No Active Plan
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if($user->hasRole('tenant') && $user->tenant)
                                        @php
                                            $activeSubscription = $user->tenant->dukas->flatMap->dukaSubscriptions->where('status', 'active')->where('end_date', '>=', now()->toDateString())->first();
                                        @endphp
                                        @if($activeSubscription)
                                            {{ $activeSubscription->getDaysRemaining() }} days
                                        @else
                                            Expired
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        @if($user->status === 'active')
                                            <form method="POST" action="{{ route('super-admin.users.toggle-status', $user->id) }}" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-warning"
                                                        onclick="return confirm('Are you sure you want to deactivate this user?')"
                                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Deactivate User">
                                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                                        <path d="M5 6.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0zm1.25.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5zm2.25-.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0z"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('super-admin.users.toggle-status', $user->id) }}" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success"
                                                        onclick="return confirm('Are you sure you want to activate this user?')"
                                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Activate User">
                                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                                        <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif

                                        <a href="{{ route('super-admin.users.edit', $user->id) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           data-bs-toggle="tooltip" data-bs-placement="top" title="Edit User">
                                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708l-3-3zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207l6.5-6.5zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.499.499 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2-2a.5.5 0 0 0-.65.65l2 2a.5.5 0 0 0 .168.11l.178-.178z"/>
                                            </svg>
                                        </a>

                                        @if(!$user->hasRole('superadmin'))
                                            <form method="POST" action="{{ route('super-admin.users.reset-password', $user->id) }}" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-warning"
                                                        onclick="return confirm('Are you sure you want to reset this user\'s password to \'123456\'?')"
                                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Reset Password to 123456">
                                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                        <path d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
                                                        <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif

                                        @if(!$user->hasRole('superadmin') || auth()->id() !== $user->id)
                                            <form method="POST" action="{{ route('super-admin.users.destroy', $user->id) }}" style="display: inline;"
                                                  onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Delete User">
                                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                                        <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center">No users found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-3">
                    {{ $users->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.btn-group .btn {
    border-radius: 0.375rem !important;
    margin-right: 2px;
    transition: all 0.2s ease-in-out;
}
.btn-group .btn:first-child {
    border-top-right-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
}
.btn-group .btn:last-child {
    border-top-left-radius: 0 !important;
    border-bottom-left-radius: 0 !important;
}
.btn-group .btn:not(:first-child):not(:last-child) {
    border-radius: 0 !important;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease-in-out;
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 0.5rem 0.5rem 0 0 !important;
}

.card-header .bg-primary {
    background: rgba(255, 255, 255, 0.2) !important;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.badge {
    font-weight: 500;
    padding: 0.4em 0.8em;
    border-radius: 20px;
}

.table-dark th {
    background-color: #343a40 !important;
    border: none;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.table-dark th svg {
    opacity: 0.8;
}

#bulk-actions {
    border: 2px solid #dc3545;
    background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.form-check-input:checked {
    background-color: #dc3545;
    border-color: #dc3545;
}

#selected-count {
    color: #dc3545;
    font-size: 1.1em;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Bulk selection functionality
    const selectAllCheckbox = document.getElementById('select-all');
    const selectAllPagesCheckbox = document.getElementById('select-all-pages');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const bulkActions = document.getElementById('bulk-actions');
    const selectedCount = document.getElementById('selected-count');
    const deleteBtnText = document.getElementById('delete-btn-text');
    const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
    const cancelSelection = document.getElementById('cancel-selection');

    function updateBulkActions() {
        const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
        const count = checkedBoxes.length;
        const totalCheckable = userCheckboxes.length;
        const totalUsers = {{ $users->total() }};
        const isSelectAllPages = selectAllPagesCheckbox.checked;

        if (isSelectAllPages) {
            selectedCount.textContent = totalUsers;
            deleteBtnText.textContent = 'Delete All Users';
        } else {
            selectedCount.textContent = count;
            deleteBtnText.textContent = 'Delete Selected';
        }

        if (count > 0 || isSelectAllPages) {
            bulkActions.classList.remove('d-none');
        } else {
            bulkActions.classList.add('d-none');
        }

        // Update select all checkbox state (only for current page)
        if (isSelectAllPages) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = false;
        } else {
            if (count === 0) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = false;
            } else if (count === totalCheckable) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = true;
            } else {
                selectAllCheckbox.indeterminate = true;
                selectAllCheckbox.checked = false;
            }
        }
    }

    // Select all functionality (current page)
    selectAllCheckbox.addEventListener('change', function() {
        const isChecked = this.checked;
        userCheckboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
        });
        selectAllPagesCheckbox.checked = false; // Uncheck select all pages when selecting individual
        updateBulkActions();
    });

    // Select all pages functionality
    selectAllPagesCheckbox.addEventListener('change', function() {
        const isChecked = this.checked;
        if (isChecked) {
            // Uncheck all individual checkboxes when selecting all pages
            userCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }
        updateBulkActions();
    });

    // Individual checkbox change
    userCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            selectAllPagesCheckbox.checked = false; // Uncheck select all pages when selecting individual
            updateBulkActions();
        });
    });

    // Cancel selection
    cancelSelection.addEventListener('click', function() {
        userCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
        selectAllPagesCheckbox.checked = false;
        updateBulkActions();
    });

    // Bulk delete functionality
    bulkDeleteBtn.addEventListener('click', function() {
        const isSelectAllPages = selectAllPagesCheckbox.checked;
        const selectedUsers = Array.from(userCheckboxes)
            .filter(checkbox => checkbox.checked)
            .map(checkbox => checkbox.value);

        if (!isSelectAllPages && selectedUsers.length === 0) {
            alert('Please select users to delete.');
            return;
        }

        const totalUsers = {{ $users->total() }};
        const countToDelete = isSelectAllPages ? totalUsers : selectedUsers.length;
        const confirmMessage = `Are you sure you want to delete ${countToDelete} user(s)? This action cannot be undone.`;

        if (confirm(confirmMessage)) {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("super-admin.users.bulk-delete") }}';

            // Add CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            // Add method spoofing for DELETE
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            if (isSelectAllPages) {
                // Add flag to delete all users matching current filters
                const deleteAllInput = document.createElement('input');
                deleteAllInput.type = 'hidden';
                deleteAllInput.name = 'delete_all';
                deleteAllInput.value = '1';
                form.appendChild(deleteAllInput);

                // Add current filter parameters to maintain the query
                @if(request('search'))
                const searchInput = document.createElement('input');
                searchInput.type = 'hidden';
                searchInput.name = 'search';
                searchInput.value = '{{ request('search') }}';
                form.appendChild(searchInput);
                @endif

                @if(request('role'))
                const roleInput = document.createElement('input');
                roleInput.type = 'hidden';
                roleInput.name = 'role';
                roleInput.value = '{{ request('role') }}';
                form.appendChild(roleInput);
                @endif

                @if(request('status'))
                const statusInput = document.createElement('input');
                statusInput.type = 'hidden';
                statusInput.name = 'status';
                statusInput.value = '{{ request('status') }}';
                form.appendChild(statusInput);
                @endif
            } else {
                // Add selected user IDs
                selectedUsers.forEach(userId => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'user_ids[]';
                    input.value = userId;
                    form.appendChild(input);
                });
            }

            document.body.appendChild(form);
            form.submit();
        }
    });
});
</script>
@endpush
