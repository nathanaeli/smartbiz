@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">

                <!-- HEADER -->
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">Create Product Category</h4>
                    </div>
                    <div class="header-action">
                        <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Categories
                        </a>
                    </div>
                </div>

                <!-- BODY -->
                <div class="card-body">
                    <form action="{{ route('categories.store') }}" method="POST">
                        @csrf

                        <div class="row">

                            <!-- CATEGORY NAME -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name') }}" required>

                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- SMART SEARCH PARENT CATEGORY -->
                            <div class="col-md-6 position-relative">
                                <div class="form-group">
                                    <label for="parent_category" class="form-label">Parent Category</label>

                                    <input type="text"
                                           class="form-control @error('parent_id') is-invalid @enderror"
                                           id="parent_category"
                                           name="parent_category"
                                           placeholder="Type to search parent category"
                                           value="{{ old('parent_category') }}"
                                           autocomplete="off">

                                    <input type="hidden" id="parent_id" name="parent_id" value="{{ old('parent_id') }}">

                                    <div id="parent-suggestions" class="suggestions-list" style="display:none;"></div>

                                    @error('parent_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                        </div>

                        <div class="row">

                            <!-- STATUS -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-control @error('status') is-invalid @enderror"
                                            id="status" name="status" required>
                                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>

                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                        </div>

                        <!-- DESCRIPTION -->
                        <div class="form-group">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description"
                                      name="description"
                                      rows="3">{{ old('description') }}</textarea>

                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- BUTTONS -->
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Category
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

<!-- ======================== SMART AUTOCOMPLETE CSS ======================== -->
<style>
.suggestions-list {
    position: absolute;
    top: 102%;
    left: 0;
    right: 0;
    background: #fff;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    box-shadow: 0px 10px 25px rgba(0,0,0,0.08);
    max-height: 240px;
    overflow-y: auto;
    z-index: 2000;
    animation: fadeIn 0.15s ease-in-out;
}

.suggestion-item {
    padding: 10px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    transition: 0.2s ease;
    font-size: 15px;
}

.suggestion-item:not(:last-child) {
    border-bottom: 1px solid #f3f4f6;
}

.suggestion-item svg {
    width: 18px;
    height: 18px;
    color: #64748b;
}

.suggestion-item:hover,
.suggestion-item.active {
    background: #f8fafc;
}

.highlight {
    font-weight: bold;
    color: #4f46e5;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(4px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>

<!-- ======================== SMART AUTOCOMPLETE JS ======================== -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    const parentInput     = document.getElementById('parent_category');
    const parentIdInput   = document.getElementById('parent_id');
    const suggestionsList = document.getElementById('parent-suggestions');

    let currentFocus = -1;

    // Load parent categories (from controller, NO extra backend call)
    const categories = @json(
        $parentCategories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])
    );

    // Filter locally
    function filterSuggestions(query) {
        query = query.toLowerCase();
        return categories.filter(cat => cat.name.toLowerCase().includes(query));
    }

    // Highlight matched text
    function highlight(text, query) {
        return text.replace(new RegExp(query, "gi"), match => `<span class="highlight">${match}</span>`);
    }

    // Render suggestion list
    function showSuggestions(list, query) {
        if (query.length < 1) {
            suggestionsList.style.display = "none";
            return;
        }

        suggestionsList.innerHTML = "";

        if (list.length === 0) {
            suggestionsList.innerHTML = `<div class="suggestion-item text-muted">No matching categories</div>`;
            suggestionsList.style.display = "block";
            return;
        }

        list.forEach((cat, index) => {
            const div = document.createElement("div");
            div.className = "suggestion-item";
            div.dataset.id = cat.id;

            div.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <span>${highlight(cat.name, query)}</span>
            `;

            div.addEventListener("click", () => select(cat));

            suggestionsList.appendChild(div);
        });

        suggestionsList.style.display = "block";
    }

    // Select category
    function select(cat) {
        parentInput.value = cat.name;
        parentIdInput.value = cat.id;
        suggestionsList.style.display = "none";
    }

    // Input typing event
    parentInput.addEventListener("input", function() {
        const query = this.value.trim();
        parentIdInput.value = ""; // clear hidden field
        showSuggestions(filterSuggestions(query), query);
    });

    // Keyboard navigation
    parentInput.addEventListener("keydown", function(e) {
        const items = suggestionsList.querySelectorAll(".suggestion-item");

        if (e.key === "ArrowDown") {
            e.preventDefault();
            currentFocus = (currentFocus + 1) % items.length;
            updateActive(items);
        }

        if (e.key === "ArrowUp") {
            e.preventDefault();
            currentFocus = (currentFocus - 1 + items.length) % items.length;
            updateActive(items);
        }

        if (e.key === "Enter") {
            e.preventDefault();
            if (currentFocus > -1 && items[currentFocus]) {
                items[currentFocus].click();
            }
        }
    });

    function updateActive(items) {
        items.forEach(i => i.classList.remove("active"));
        if (items[currentFocus]) {
            items[currentFocus].classList.add("active");
            items[currentFocus].scrollIntoView({ block: "nearest" });
        }
    }

    // Click outside to hide
    document.addEventListener("click", function(e) {
        if (!parentInput.contains(e.target) && !suggestionsList.contains(e.target)) {
            suggestionsList.style.display = "none";
        }
    });
});
</script>

@endsection
