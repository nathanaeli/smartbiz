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
                                <i class="fas fa-upload text-success me-2"></i>Import Products from Excel
                            </h4>
                            <p class="text-muted mb-0 mt-1">Upload an Excel file to bulk import products with smart validation</p>
                        </div>
                        <div>
                            <a href="{{ route('manageproduct') }}" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-arrow-left me-1"></i>Back to Products
                            </a>
                            <a href="{{ route('officer.products.import') }}?download=template" class="btn btn-outline-info">
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
                                    <li><strong>Auto Category Creation:</strong> Creates categories if they don't exist</li>
                                    <li><strong>Smart SKU Generation:</strong> Generates unique SKUs automatically</li>
                                    <li><strong>Price Validation:</strong> Ensures selling price > buying price</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="mb-0">
                                    <li><strong>Stock Assignment:</strong> Automatically assigns initial stock</li>
                                    <li><strong>Duplicate Prevention:</strong> Skips existing products</li>
                                    <li><strong>Detailed Error Reporting:</strong> Shows specific issues per row</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Form -->
                    <form action="{{ route('officer.products.import.process') }}" method="POST" enctype="multipart/form-data">
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
                                            <i class="fas fa-upload me-2"></i>Import Products
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
                                            <td>Product name</td>
                                            <td>Rice 5kg</td>
                                        </tr>
                                        <tr>
                                            <td><code>buying_price</code></td>
                                            <td>Number</td>
                                            <td><span class="badge bg-danger">Yes</span></td>
                                            <td>Purchase price (must be > 0)</td>
                                            <td>150.00</td>
                                        </tr>
                                        <tr>
                                            <td><code>selling_price</code></td>
                                            <td>Number</td>
                                            <td><span class="badge bg-danger">Yes</span></td>
                                            <td>Selling price (must be > buying_price)</td>
                                            <td>180.00</td>
                                        </tr>
                                        <tr>
                                            <td><code>unit</code></td>
                                            <td>String</td>
                                            <td><span class="badge bg-secondary">No</span></td>
                                            <td>Unit of measurement</td>
                                            <td>pcs, kg, g, ltr, ml, box, bag, pack, set, pair, dozen, carton</td>
                                        </tr>
                                        <tr>
                                            <td><code>category</code></td>
                                            <td>String</td>
                                            <td><span class="badge bg-secondary">No</span></td>
                                            <td>Product category (auto-created if doesn't exist)</td>
                                            <td>Food & Beverages</td>
                                        </tr>
                                        <tr>
                                            <td><code>description</code></td>
                                            <td>String</td>
                                            <td><span class="badge bg-secondary">No</span></td>
                                            <td>Product description</td>
                                            <td>Premium quality rice</td>
                                        </tr>
                                        <tr>
                                            <td><code>barcode</code></td>
                                            <td>String</td>
                                            <td><span class="badge bg-secondary">No</span></td>
                                            <td>Product barcode (must be unique, numeric values will be converted to strings)</td>
                                            <td>123456789</td>
                                        </tr>
                                        <tr>
                                            <td><code>initial_stock</code></td>
                                            <td>Integer</td>
                                            <td><span class="badge bg-secondary">No</span></td>
                                            <td>Initial stock quantity</td>
                                            <td>50</td>
                                        </tr>
                                        <tr>
                                            <td><code>duka</code></td>
                                            <td>String</td>
                                            <td><span class="badge bg-secondary">No</span></td>
                                            <td>Duka name for stock assignment</td>
                                            <td>Main Store</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="alert alert-light mt-3">
                                <strong>Sample Data:</strong>
                                <pre class="mb-0 mt-2"><code>name,buying_price,selling_price,unit,category,description,barcode,initial_stock,duka
Rice 5kg,150.00,180.00,pcs,Food & Beverages,Premium quality rice,123456789,50,Main Store
Sugar 1kg,80.00,95.00,pcs,Food & Beverages,White sugar,987654321,100,Branch A</code></pre>
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
