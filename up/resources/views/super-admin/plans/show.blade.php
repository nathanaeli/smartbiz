@extends('layouts.super-admin')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Plan Details: {{ $plan->name }}</h4>
                <div>
                    <a href="{{ route('super-admin.plans.edit', $plan->id) }}" class="btn btn-warning">Edit Plan</a>
                    <a href="{{ route('super-admin.plans.index') }}" class="btn btn-secondary">Back to Plans</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Plan Information</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Name:</th>
                                        <td>{{ $plan->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Description:</th>
                                        <td>{{ $plan->description ?? 'No description' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Price:</th>
                                        <td>${{ number_format($plan->price, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Billing Cycle:</th>
                                        <td>{{ ucfirst($plan->billing_cycle) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Max Dukas:</th>
                                        <td>{{ $plan->max_dukas }}</td>
                                    </tr>
                                    <tr>
                                        <th>Max Products:</th>
                                        <td>{{ $plan->max_products }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td>
                                            <span class="badge bg-{{ $plan->is_active ? 'success' : 'danger' }}">
                                                {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Created:</th>
                                        <td>{{ $plan->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Last Updated:</th>
                                        <td>{{ $plan->updated_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Plan Features ({{ $plan->planFeatures->count() }})</h5>
                            </div>
                            <div class="card-body">
                                @if($plan->planFeatures->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Feature</th>
                                                    <th>Code</th>
                                                    <th>Value</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($plan->planFeatures as $planFeature)
                                                <tr>
                                                    <td>{{ $planFeature->name }}</td>
                                                    <td><code>{{ $planFeature->code }}</code></td>
                                                    <td>{{ $planFeature->pivot->value ?? 'N/A' }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted">No features assigned to this plan.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subscription Statistics -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Subscription Statistics</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h3 class="text-primary">{{ $plan->dukaSubscriptions()->count() }}</h3>
                                            <p class="text-muted">Total Subscriptions</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h3 class="text-success">{{ $plan->dukaSubscriptions()->where('status', 'active')->count() }}</h3>
                                            <p class="text-muted">Active Subscriptions</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h3 class="text-warning">{{ $plan->dukaSubscriptions()->where('status', 'pending')->count() }}</h3>
                                            <p class="text-muted">Pending Subscriptions</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h3 class="text-danger">{{ $plan->dukaSubscriptions()->where('status', 'cancelled')->count() }}</h3>
                                            <p class="text-muted">Cancelled Subscriptions</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
