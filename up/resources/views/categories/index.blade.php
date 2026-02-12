@extends('layouts.app')

@section('content')
<div class="container-fluid card">
    <div class="row">
        <div class="col-sm-12">

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Product Categories</h4>

                    <!-- Actions -->
                    <div class="d-flex gap-2">
                         <button type="button" class="btn btn-outline-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="ri-upload-cloud-2-line me-1"></i>
                            <span class="ms-1">Import</span>
                        </button>
                        <a href="{{ route('categories.create') }}" class="btn btn-primary d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span class="ms-2">Add Category</span>
                        </a>
                    </div>
                </div>

                <div class="card-body">

                    @if($categories->count() > 0)
                    <div class="table-responsive">
                        <table id="datatable" class="table table-striped" data-toggle="data-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Parent</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($categories as $category)
                                <tr>
                                    <td class="fw-semibold">{{ $category->name }}</td>

                                    <td>{{ $category->parent?->name ?? '-' }}</td>

                                    <td>{{ $category->description ?? '-' }}</td>

                                    <td>
                                        <span class="badge bg-{{ $category->status === 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($category->status) }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="d-flex gap-2">

                                            <!-- Edit -->
                                            <a href="{{ route('categories.edit', $category) }}"
                                               class="btn btn-sm btn-outline-primary p-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" viewBox="0 0 24 24"
                                                     fill="none" stroke="currentColor" stroke-width="2"
                                                     stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M12 20h9"></path>
                                                    <path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"></path>
                                                </svg>
                                            </a>

                                            <!-- Delete -->
                                            <form action="{{ route('categories.destroy', $category) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" onclick="return confirm('Delete this category?')"
                                                        class="btn btn-sm btn-outline-danger p-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16"
                                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                         stroke-width="2" stroke-linecap="round"
                                                         stroke-linejoin="round">
                                                        <polyline points="3 6 5 6 21 6"></polyline>
                                                        <path d="M19 6l-1 14H6L5 6"></path>
                                                        <path d="M10 11v6"></path>
                                                        <path d="M14 11v6"></path>
                                                    </svg>
                                                </button>
                                            </form>

                                        </div>
                                    </td>

                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{ $categories->links() }}

                    @else
                    <!-- Empty State -->
                    <div class="text-center py-5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="60"
                             viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 7l9-4 9 4-9 4-9-4z"></path>
                            <path d="M3 17l9 4 9-4"></path>
                            <path d="M3 12l9 4 9-4"></path>
                        </svg>

                        <h5 class="text-muted mt-3">No categories found</h5>
                        <p class="text-muted">Start by creating your first product category.</p>

                        <a href="{{ route('categories.create') }}" class="btn btn-primary mt-2 d-flex align-items-center justify-content-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" viewBox="0 0 24 24" fill="none"
                                 stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span class="ms-2">Create Category</span>
                        </a>
                    </div>
                    @endif

                </div>
            </div>

        </div>
    </div>
</div>
    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold" id="importModalLabel">Import Categories</h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('categories.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="text-center mb-4">
                            <div class="bg-soft-primary rounded-circle d-inline-flex p-3 mb-3">
                                <i class="ri-upload-cloud-2-line ri-2x text-primary"></i>
                            </div>
                            <p class="text-muted small">Upload a CSV or Excel file to bulk create categories.</p>
                            <a href="{{ route('categories.import-template') }}" class="text-primary small fw-bold text-decoration-none">
                                <i class="ri-download-line me-1"></i> Download Sample Template
                            </a>
                        </div>

                        <div class="mb-3">
                            <label for="importFile" class="form-label fw-600">Select File</label>
                            <input type="file" name="file" class="form-control form-control-lg rounded-pill shadow-none" id="importFile" accept=".csv, .xlsx, .xls" required>
                        </div>
                        <div class="text-muted small">
                            <ul>
                                <li>Supported formats: .csv, .xlsx, .xls</li>
                                <li>Max file size: 5MB</li>
                                <li>Duplicate names will be ignored.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pb-4 px-4">
                        <button type="button" class="btn btn-light rounded-pill px-4 me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
