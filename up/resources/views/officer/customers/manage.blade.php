@extends('layouts.officer')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">{{ __('customers.manage_customers') }}</h4>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Add Customer Button -->
                <div class="mb-4 d-flex justify-content-between align-items-center">
                    <div>
                        <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
                                <path d="M12 4V20M20 12H4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            {{ __('customers.add_new_customer') }}
                        </button>
                        <a href="{{ route('officer.customers.import') }}" class="btn btn-success">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <line x1="16" y1="13" x2="8" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <line x1="16" y1="17" x2="8" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <polyline points="10,9 9,9 8,9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            {{ __('customers.import_excel') }}
                        </a>
                    </div>
                    <div class="text-muted small">
                        {{ __('customers.total_customers', ['count' => $customers->total()]) }}
                    </div>
                </div>

                <!-- Customers Table -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('customers.name') }}</th>
                                <th>{{ __('customers.email') }}</th>
                                <th>{{ __('customers.phone') }}</th>
                                <th>{{ __('customers.duka') }}</th>
                                <th>{{ __('customers.created_by') }}</th>
                                <th>{{ __('customers.created_at') }}</th>
                                <th>{{ __('customers.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $customer)
                                <tr>
                                    <td>{{ $customer->name }}</td>
                                    <td>{{ $customer->email ?? 'N/A' }}</td>
                                    <td>{{ $customer->phone ?? 'N/A' }}</td>
                                    <td>{{ $customer->duka->name ?? 'N/A' }}</td>
                                    <td>{{ $customer->creator ? $customer->creator->name : 'System' }}</td>
                                    <td>{{ $customer->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                                    data-bs-target="#editCustomerModal{{ $customer->id }}">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="m18.5 2.5 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                    data-bs-target="#deleteCustomerModal{{ $customer->id }}">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="empty-state">
                                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-muted mb-3">
                                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <h5 class="text-muted">{{ __('customers.no_customers_found') }}</h5>
                                            <p class="text-muted">{{ __('customers.no_customers_desc') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $customers->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('customers.add_new_customer') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('officer.customers.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('customers.name') }} *</label>
                            <input type="text" name="name" class="form-control" required>
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('customers.email') }}</label>
                            <input type="email" name="email" class="form-control">
                            @error('email')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('customers.phone') }}</label>
                            <input type="text" name="phone" class="form-control">
                            @error('phone')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('customers.duka') }} *</label>
                            @if($singleDuka)
                                <!-- Single duka - show as readonly input -->
                                <input type="text" class="form-control" value="{{ $singleDuka->name }}" readonly>
                                <input type="hidden" name="duka_id" value="{{ $singleDuka->id }}">
                                <small class="text-muted">{{ __('customers.auto_selected_duka') }}</small>
                            @else
                                <!-- Multiple dukas - show dropdown -->
                                <select name="duka_id" class="form-control" required>
                                    <option value="">{{ __('customers.select_duka') }}</option>
                                    @foreach($dukas as $duka)
                                        <option value="{{ $duka->id }}">{{ $duka->name }}</option>
                                    @endforeach
                                </select>
                                @error('duka_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            @endif
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">{{ __('customers.address') }}</label>
                            <textarea name="address" class="form-control" rows="3"></textarea>
                            @error('address')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('customers.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('customers.add_customer') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Customer Modals -->
@foreach($customers as $customer)
<div class="modal fade" id="editCustomerModal{{ $customer->id }}" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('customers.edit_customer') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('officer.customers.update', $customer->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('customers.name') }} *</label>
                            <input type="text" name="name" class="form-control" value="{{ $customer->name }}" required>
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('customers.email') }}</label>
                            <input type="email" name="email" class="form-control" value="{{ $customer->email }}">
                            @error('email')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('customers.phone') }}</label>
                            <input type="text" name="phone" class="form-control" value="{{ $customer->phone }}">
                            @error('phone')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">{{ __('customers.address') }}</label>
                            <textarea name="address" class="form-control" rows="3">{{ $customer->address }}</textarea>
                            @error('address')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('customers.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('customers.update_customer') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Customer Modals -->
<div class="modal fade" id="deleteCustomerModal{{ $customer->id }}" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">{{ __('customers.delete_customer') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{!! __('customers.delete_confirm', ['name' => '<strong>' . $customer->name . '</strong>']) !!}</p>
                <div class="alert alert-warning">
                    <strong>{{ __('customers.delete_warning') }}</strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('customers.cancel') }}</button>
                <form action="{{ route('officer.customers.destroy', $customer->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">{{ __('customers.delete_customer') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection
