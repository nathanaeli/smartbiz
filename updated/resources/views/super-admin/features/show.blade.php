@extends('layouts.super-admin')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Feature Details: {{ $feature->name }}</h4>
                <div>
                    <a href="{{ route('super-admin.features.edit', $feature->id) }}" class="btn btn-warning">Edit Feature</a>
                    <a href="{{ route('super-admin.features.index') }}" class="btn btn-secondary">Back to Features</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5>Feature Information</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Code:</th>
                                        <td><code>{{ $feature->code }}</code></td>
                                    </tr>
                                    <tr>
                                        <th>Name:</th>
                                        <td>{{ $feature->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Description:</th>
                                        <td>{{ $feature->description ?? 'No description' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Created:</th>
                                        <td>{{ $feature->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Last Updated:</th>
                                        <td>{{ $feature->updated_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>Usage Statistics</h5>
                            </div>
                            <div class="card-body">
                                <div class="text-center">
                                    <h3 class="text-primary">{{ $feature->plans()->count() }}</h3>
                                    <p class="text-muted">Plans using this feature</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Plans using this feature -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Plans Using This Feature ({{ $feature->plans->count() }})</h5>
                            </div>
                            <div class="card-body">
                                @if($feature->plans->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Plan Name</th>
                                                    <th>Price</th>
                                                    <th>Billing Cycle</th>
                                                    <th>Status</th>
                                                    <th>Feature Value</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($feature->plans as $plan)
                                                <tr>
                                                    <td>
                                                        <a href="{{ route('super-admin.plans.show', $plan->id) }}">{{ $plan->name }}</a>
                                                    </td>
                                                    <td>${{ number_format($plan->price, 2) }}</td>
                                                    <td>{{ ucfirst($plan->billing_cycle) }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $plan->is_active ? 'success' : 'danger' }}">
                                                            {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $plan->pivot->value ?? 'N/A' }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted">This feature is not currently used in any plans.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
