@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Edit Sale #{{ $sale->id }}</h5>
                    <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-secondary btn-sm">Back to Sale Details</a>
                </div>
                <div class="card-body">
                    <form action="{{ route('sales.update', $sale->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="discount_amount" class="form-label">Discount Amount</label>
                                <input type="number" step="0.01" class="form-control" id="discount_amount" name="discount_amount" value="{{ old('discount_amount', $sale->discount_amount) }}">
                                @error('discount_amount')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="discount_reason" class="form-label">Discount Reason</label>
                                <input type="text" class="form-control" id="discount_reason" name="discount_reason" value="{{ old('discount_reason', $sale->discount_reason) }}">
                                @error('discount_reason')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Sale</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
