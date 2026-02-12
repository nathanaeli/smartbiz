@extends('layouts.app')

@section('content')
<div class="content-fluid mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12 col-lg-10 col-xl-8 mx-auto">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="header-title">
                        <h4 class="card-title"><i class="ri-store-2-line me-2"></i>Register New Duka</h4>
                        <p class="text-muted small mb-0">Expand your business by adding a new branch.</p>
                    </div>
                    <a href="{{ route('tenant.dukas.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center">
                        <i class="ri-arrow-left-line me-1"></i> Back to Dukas
                    </a>
                </div>
                <div class="card-body">
                    <div class="row justify-content-center">
                        <div class="col-lg-11">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-4">
                                    <form action="{{ route('duka.store.with.plan') }}" method="POST">
                                        @csrf

                                        <!-- Duka Name -->
                                        <div class="form-group mb-4">
                                            <label class="form-label fw-bold h6" for="dukaName">Duka Name <span class="text-danger">*</span></label>
                                            <input type="text" name="name" id="dukaName" class="form-control form-control-lg" placeholder="e.g. Kariakoo Branch" required>
                                            <div class="form-text">The official name of the new shop or branch.</div>
                                        </div>

                                        <div class="row">
                                            <!-- Physical Location -->
                                            <div class="form-group col-md-6 mb-4">
                                                <label class="form-label fw-bold h6" for="location">Physical Location <span class="text-danger">*</span></label>
                                                <input type="text" name="location" id="location" class="form-control" placeholder="e.g. Plot 12, Msimbazi St" required>
                                            </div>

                                            <!-- Manager Name -->
                                            <div class="form-group col-md-6 mb-4">
                                                <label class="form-label fw-bold h6" for="managerName">Manager Name</label>
                                                <input type="text" name="manager_name" id="managerName" class="form-control" placeholder="Who manages this branch?">
                                            </div>
                                        </div>

                                        <!-- Plan Entitlement Info Box -->
                                        <div class="alert alert-soft-primary d-flex align-items-center mb-4" role="alert">
                                            <i class="ri-information-line fs-3 me-3"></i>
                                            <div>
                                                <h6 class="alert-heading">Plan Entitlement</h6>
                                                <p class="mb-0">You have used <strong>{{ auth()->user()->tenant->dukas()->count() }}</strong> of <strong>{{ auth()->user()->tenant->activeSubscription->plan->max_dukas }}</strong> available shop slots.</p>
                                            </div>
                                        </div>

                                        <!-- Submit Button -->
                                        <div class="text-end">
                                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                               <i class="ri-add-circle-line me-2"></i>Create Shop
                                            </button>
                                        </div>
                                    </form>
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
