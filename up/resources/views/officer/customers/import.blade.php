@extends('layouts.officer')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-0">
                                <i class="fas fa-upload text-success me-2"></i>Import Customers from Excel
                            </h4>
                            <p class="text-muted mb-0 mt-1">Upload an Excel file to bulk import customers with smart validation</p>
                        </div>
                        <div>
                            <a href="{{ route('officer.customers.manage') }}" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-arrow-left me-1"></i>Back to Customers
                            </a>
                            <a href="{{ route('officer.customers.import') }}?download=template" class="btn btn-outline-info">
                                <i class="fas fa-download me-1"></i>Download Template
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Success/Error Messages -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Success!</strong> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Error!</strong> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Import Errors -->
                    @if(session('import_errors') && !empty(session('import_errors')))
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle text-warning me-2"></i>Import Issues:</h6>
                            <ul class="mb-0">
                                @foreach(session('import_errors') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Smart Features Info -->
                    <div class="alert alert-info mb-4">
                        <h6><i class="fas fa-lightbulb text-info me-2"></i>Smart Import Features:</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="mb-0">
                                    <li><strong>Auto Duka Assignment:</strong> Automatically assigns customers to dukas</li>
                                    <li><strong>Email Validation:</strong> Validates email format and uniqueness</li>
                                    <li><strong>Phone Number Handling:</strong> Accepts various phone formats</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="mb-0">
                                    <li><strong>Duplicate Prevention:</strong> Skips customers with existing emails</li>
                                    <li><strong>Flexible Data:</strong> Optional fields for maximum flexibility</li>
                                    <li><strong>Detailed Error Reporting:</strong> Shows specific issues per row</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Form -->
                    <form action="{{ route('officer.customers.import.process') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="excel_file" class="form-label">
                                        <strong>Excel File</strong>
                                        <small class="text-muted">(Max: 5MB, Formats: .xlsx, .xls)</small>
                                    </label>
                                    <input type="file" class="form-control @error('excel_file') is-invalid @enderror"
                                           id="excel_file" name="excel_file" accept=".xlsx,.xls" required>
                                    @error('excel_file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-success btn-lg">
                                            <i class="fas fa-upload me-2"></i>Import Customers
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Excel Format Requirements -->
                    <div class="card border-warning mt-4">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0">
                                <i class="fas fa-file-excel me-2"></i>Excel Format Requirements
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-3">Your Excel file must have the following columns in the first row (headers are case-sensitive):</p>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Column Name</th>
                                            <th>Type</th>
                                            <th>Required</th>
                                            <th>Description</th>
                                            <th>Example</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>name</code></td>
                                            <td>String</td>
                                            <td><span class="badge bg-danger">Yes</span></td>
                                            <td>Customer full name</td>
                                            <td>John Doe</td>
                                        </tr>
                                        <tr>
                                            <td><code>email</code></td>
                                            <td>String</td>
                                            <td><span class="badge bg-secondary">No</span></td>
                                            <td>Customer email address (must be unique)</td>
                                            <td>john.doe@example.com</td>
                                        </tr>
                                        <tr>
                                            <td><code>phone</code></td>
                                            <td>String</td>
                                            <td><span class="badge bg-secondary">No</span></td>
                                            <td>Customer phone number</td>
                                            <td>+255 123 456 789</td>
                                        </tr>
                                        <tr>
                                            <td><code>address</code></td>
                                            <td>String</td>
                                            <td><span class="badge bg-secondary">No</span></td>
                                            <td>Customer address</td>
                                            <td>123 Main Street, Dar es Salaam</td>
                                        </tr>
                                        <tr>
                                            <td><code>duka</code></td>
                                            <td>String</td>
                                            <td><span class="badge bg-secondary">No</span></td>
                                            <td>Duka name for assignment</td>
                                            <td>Main Store</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="alert alert-light mt-3">
                                <strong>Sample Data:</strong>
                                <pre class="mb-0 mt-2"><code>name,email,phone,address,duka
John Doe,john.doe@example.com,+255 123 456 789,123 Main Street, Dar es Salaam,Main Store
Jane Smith,jane.smith@example.com,+255 987 654 321,456 Side Road, Arusha,Branch A
Bob Johnson,,+255 555 123 456,789 Center Ave, Mwanza,Main Store</code></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.table code {
    background-color: #f8f9fa;
    padding: 2px 4px;
    border-radius: 3px;
    font-size: 0.875em;
}

.card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

pre code {
    white-space: pre-wrap;
    word-break: break-all;
}
</style>
@endpush
