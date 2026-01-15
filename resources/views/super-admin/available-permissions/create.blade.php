@extends('layouts.super-admin')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4>Create New Available Permission</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('super-admin.available-permissions.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Permission Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}" required
                                   placeholder="e.g., manage-users, view-reports">
                            <small class="form-text text-muted">Use lowercase with hyphens (e.g., manage-users)</small>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="display_name" class="form-label">Display Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('display_name') is-invalid @enderror"
                                   id="display_name" name="display_name" value="{{ old('display_name') }}" required
                                   placeholder="e.g., Manage Users, View Reports">
                            <small class="form-text text-muted">Human-readable name for the permission</small>
                            @error('display_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="3"
                                  placeholder="Describe what this permission allows users to do">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-8">
                                <label for="excel_file" class="form-label">Upload Excel File (Optional)</label>
                                <input type="file" class="form-control @error('excel_file') is-invalid @enderror"
                                       id="excel_file" name="excel_file" accept=".xlsx,.xls,.csv">
                                <small class="form-text text-muted">Upload an Excel file to import multiple permissions. The file should have columns: name, display_name, description, is_active</small>
                                @error('excel_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <a href="{{ route('super-admin.available-permissions.download-sample') }}"
                                   class="btn btn-outline-primary w-100"
                                   title="Download sample Excel format with existing permissions">
                                    ⬇ Download Sample Format
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Active
                            </label>
                        </div>
                        <small class="form-text text-muted">Inactive permissions won't be available for assignment</small>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('super-admin.available-permissions.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Permission</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
