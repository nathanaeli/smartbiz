@extends(auth()->user()->hasRole('officer') ? 'layouts.officer' : 'layouts.app')

@section('content')
<div class="container-fluid card">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('officer.dashboard') }}">
                    <i class="fas fa-home me-1"></i>Dashboard
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('cash-flow.index') }}">Cash Flow</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                Edit Entry
            </li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>Edit Cash Flow Entry
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('cash-flow.update', $cashFlow) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Type Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="type" id="income" value="income"
                                           {{ old('type', $cashFlow->type) == 'income' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-success" for="income">
                                        <i class="fas fa-arrow-up me-1"></i>Income
                                    </label>

                                    <input type="radio" class="btn-check" name="type" id="expense" value="expense"
                                           {{ old('type', $cashFlow->type) == 'expense' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-danger" for="expense">
                                        <i class="fas fa-arrow-down me-1"></i>Expense
                                    </label>
                                </div>
                                @error('type')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Duka Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="duka_id" class="form-label">Duka <span class="text-danger">*</span></label>
                                <select name="duka_id" id="duka_id" class="form-select @error('duka_id') is-invalid @enderror" required>
                                    <option value="">Select Duka</option>
                                    @foreach($availableDukas as $duka)
                                        <option value="{{ $duka->id }}" {{ old('duka_id', $cashFlow->duka_id) == $duka->id ? 'selected' : '' }}>
                                            {{ $duka->name }} - {{ $duka->location }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('duka_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Category -->
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                <select name="category" id="category" class="form-select @error('category') is-invalid @enderror" required>
                                    <option value="">Select Category</option>
                                    @if(old('type', $cashFlow->type) == 'income')
                                        @foreach($categories['income'] as $cat)
                                            <option value="{{ $cat }}" {{ old('category', $cashFlow->category) == $cat ? 'selected' : '' }}>
                                                {{ $cat }}
                                            </option>
                                        @endforeach
                                    @else
                                        @foreach($categories['expense'] as $cat)
                                            <option value="{{ $cat }}" {{ old('category', $cashFlow->category) == $cat ? 'selected' : '' }}>
                                                {{ $cat }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Amount -->
                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label">Amount (TSH) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">TSH</span>
                                    <input type="number" step="0.01" min="0.01" name="amount" id="amount"
                                           class="form-control @error('amount') is-invalid @enderror"
                                           value="{{ old('amount', $cashFlow->amount) }}" placeholder="0.00" required>
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Transaction Date -->
                        <div class="mb-3">
                            <label for="transaction_date" class="form-label">Transaction Date <span class="text-danger">*</span></label>
                            <input type="date" name="transaction_date" id="transaction_date"
                                   class="form-control @error('transaction_date') is-invalid @enderror"
                                   value="{{ old('transaction_date', $cashFlow->transaction_date->format('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                            @error('transaction_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" rows="3"
                                      class="form-control @error('description') is-invalid @enderror"
                                      placeholder="Enter description (optional)">{{ old('description', $cashFlow->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Reference Number -->
                        <div class="mb-3">
                            <label for="reference_number" class="form-label">Reference Number</label>
                            <input type="text" name="reference_number" id="reference_number"
                                   class="form-control @error('reference_number') is-invalid @enderror"
                                   value="{{ old('reference_number', $cashFlow->reference_number) }}" placeholder="Receipt/Invoice number (optional)">
                            @error('reference_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between pt-3 border-top">
                            <a href="{{ route('cash-flow.show', $cashFlow) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Cancel
                            </a>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update Entry
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update categories when type changes
    document.querySelectorAll('input[name="type"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            updateCategories(this.value);
        });
    });

    function updateCategories(type) {
        const categorySelect = document.getElementById('category');
        const categories = @json($categories);

        // Clear current options
        categorySelect.innerHTML = '<option value="">Select Category</option>';

        // Add new options
        categories[type].forEach(function(category) {
            const option = document.createElement('option');
            option.value = category;
            option.textContent = category;
            if (category === '{{ $cashFlow->category }}') {
                option.selected = true;
            }
            categorySelect.appendChild(option);
        });
    }
});
</script>
@endsection
