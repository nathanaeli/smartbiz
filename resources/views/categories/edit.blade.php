@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">Edit Product Category</h4>
                    </div>
                    <div class="header-action">
                        <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Categories
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name', $productCategory->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="parent_category" class="form-label">Parent Category</label>
                                    <input type="text" class="form-control @error('parent_id') is-invalid @enderror"
                                           id="parent_category" name="parent_category"
                                           value="{{ old('parent_category', $productCategory->parent ? $productCategory->parent->name : '') }}"
                                           placeholder="Type to search parent category (Optional)"
                                           autocomplete="off">
                                    <input type="hidden" id="parent_id" name="parent_id" value="{{ old('parent_id', $productCategory->parent_id) }}">
                                    <div id="parent-suggestions" class="suggestions-list" style="display: none;"></div>
                                    @error('parent_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-control @error('status') is-invalid @enderror"
                                            id="status" name="status" required>
                                        <option value="active" {{ old('status', $productCategory->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $productCategory->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3">{{ old('description', $productCategory->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Category
                            </button>
                            <a href="{{ route('categories.index') }}" class="btn btn-secondary ms-2">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.suggestions-list {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ced4da;
    border-top: none;
    border-radius: 0 0 0.375rem 0.375rem;
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.suggestion-item {
    padding: 0.5rem 1rem;
    cursor: pointer;
    border-bottom: 1px solid #f8f9fa;
}

.suggestion-item:hover,
.suggestion-item.active {
    background-color: #f8f9fa;
}

.suggestion-item:last-child {
    border-bottom: none;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const parentInput = document.getElementById('parent_category');
    const parentIdInput = document.getElementById('parent_id');
    const suggestionsList = document.getElementById('parent-suggestions');
    let currentFocus = -1;

    // Categories data from server
    const categories = @json($parentCategories->map(function($category) {
        return ['id' => $category->id, 'name' => $category->name];
    }));

    // Filter suggestions locally
    function filterSuggestions(query) {
        if (query.length < 2) {
            return [];
        }

        const lowerQuery = query.toLowerCase();
        return categories.filter(category =>
            category.name.toLowerCase().includes(lowerQuery)
        );
    }

    // Show suggestions
    function showSuggestions(suggestions) {
        if (suggestions.length === 0) {
            suggestionsList.style.display = 'none';
            return;
        }

        suggestionsList.innerHTML = '';
        suggestions.forEach((suggestion, index) => {
            const div = document.createElement('div');
            div.className = 'suggestion-item';
            div.textContent = suggestion.name;
            div.dataset.id = suggestion.id;
            div.dataset.index = index;

            div.addEventListener('click', function() {
                selectSuggestion(suggestion);
            });

            suggestionsList.appendChild(div);
        });

        suggestionsList.style.display = 'block';
        currentFocus = -1;
    }

    // Select suggestion
    function selectSuggestion(suggestion) {
        parentInput.value = suggestion.name;
        parentIdInput.value = suggestion.id;
        suggestionsList.style.display = 'none';
        currentFocus = -1;
    }

    // Handle input
    parentInput.addEventListener('input', function(e) {
        const query = e.target.value.trim();
        parentIdInput.value = ''; // Clear hidden input when typing
        const filteredSuggestions = filterSuggestions(query);
        showSuggestions(filteredSuggestions);
    });

    // Handle keyboard navigation
    parentInput.addEventListener('keydown', function(e) {
        const items = suggestionsList.querySelectorAll('.suggestion-item');

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            currentFocus = currentFocus < items.length - 1 ? currentFocus + 1 : 0;
            updateFocus(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            currentFocus = currentFocus > 0 ? currentFocus - 1 : items.length - 1;
            updateFocus(items);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (currentFocus >= 0 && items[currentFocus]) {
                const suggestion = suggestions[currentFocus];
                selectSuggestion(suggestion);
            }
        } else if (e.key === 'Escape') {
            suggestionsList.style.display = 'none';
            currentFocus = -1;
        }
    });

    function updateFocus(items) {
        items.forEach((item, index) => {
            if (index === currentFocus) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    }

    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!parentInput.contains(e.target) && !suggestionsList.contains(e.target)) {
            suggestionsList.style.display = 'none';
            currentFocus = -1;
        }
    });

    // Clear suggestions on form submit
    document.querySelector('form').addEventListener('submit', function() {
        suggestionsList.style.display = 'none';
    });
});
</script>
@endsection

