@extends('layouts.super-admin')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4>Edit Plan</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('super-admin.plans.update', $plan->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $plan->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror"
                                       id="price" name="price" value="{{ old('price', $plan->price) }}" required>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="billing_cycle" class="form-label">Billing Cycle <span class="text-danger">*</span></label>
                            <select class="form-select @error('billing_cycle') is-invalid @enderror"
                                    id="billing_cycle" name="billing_cycle" required>
                                <option value="">Select billing cycle</option>
                                <option value="monthly" {{ old('billing_cycle', $plan->billing_cycle) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="yearly" {{ old('billing_cycle', $plan->billing_cycle) == 'yearly' ? 'selected' : '' }}>Yearly</option>
                            </select>
                            @error('billing_cycle')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="max_dukas" class="form-label">Max Dukas <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('max_dukas') is-invalid @enderror"
                                   id="max_dukas" name="max_dukas" value="{{ old('max_dukas', $plan->max_dukas) }}" required>
                            @error('max_dukas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="max_products" class="form-label">Max Products <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('max_products') is-invalid @enderror"
                                   id="max_products" name="max_products" value="{{ old('max_products', $plan->max_products) }}" required>
                            @error('max_products')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="3">{{ old('description', $plan->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', $plan->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Active Plan
                            </label>
                        </div>
                    </div>

                    <!-- Features Section -->
                    <div class="mb-3">
                        <h5>Features</h5>
                        <div id="features-container">
                            @php
                                $oldFeatures = old('features', []);
                                $existingFeatures = $plan->planFeatures->pluck('pivot.value', 'id')->toArray();
                                $featuresData = !empty($oldFeatures) ? $oldFeatures : $existingFeatures;
                            @endphp

                            @if(!empty($featuresData))
                                @foreach($featuresData as $featureId => $value)
                                    @php
                                        $feature = $features->find($featureId);
                                        if (!$feature) continue;
                                    @endphp
                                <div class="feature-row mb-2 p-3 border rounded">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <select class="form-select" name="features[{{ $featureId }}][feature_id]" required>
                                                <option value="">Select Feature</option>
                                                @foreach($features as $f)
                                                    <option value="{{ $f->id }}" {{ $featureId == $f->id ? 'selected' : '' }}>
                                                        {{ $f->name }} ({{ $f->code }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-5">
                                            <input type="text" class="form-control" name="features[{{ $featureId }}][value]"
                                                   placeholder="Value (optional)" value="{{ $value }}">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-danger btn-sm remove-feature">Remove</button>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            @endif
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-feature">Add Feature</button>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('super-admin.plans.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let featureIndex = Date.now(); // Use timestamp to avoid conflicts

    document.getElementById('add-feature').addEventListener('click', function() {
        addFeatureRow();
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-feature')) {
            e.target.closest('.feature-row').remove();
        }
    });

    function addFeatureRow(featureId = '', value = '') {
        const container = document.getElementById('features-container');
        const row = document.createElement('div');
        row.className = 'feature-row mb-2 p-3 border rounded';
        row.innerHTML = `
            <div class="row">
                <div class="col-md-5">
                    <select class="form-select" name="features[new_${featureIndex}][feature_id]" required>
                        <option value="">Select Feature</option>
                        @foreach($features as $feature)
                        <option value="{{ $feature->id }}" ${featureId == {{ $feature->id }} ? 'selected' : ''}>
                            {{ $feature->name }} ({{ $feature->code }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <input type="text" class="form-control" name="features[new_${featureIndex}][value]"
                           placeholder="Value (optional)" value="${value}">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-sm remove-feature">Remove</button>
                </div>
            </div>
        `;
        container.appendChild(row);
        featureIndex++;
    }
});
</script>
@endsection
