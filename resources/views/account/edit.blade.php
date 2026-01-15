@extends('layouts.app')

@section('title', 'Edit Account')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Edit Account</h4>
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

                    <form action="{{ route('account.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Company Name *</label>
                                <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $account->company_name) }}" required>
                                @error('company_name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $account->phone) }}">
                                @error('phone')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $account->email) }}">
                                @error('email')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Website</label>
                                <input type="url" name="website" class="form-control" value="{{ old('website', $account->website) }}" placeholder="https://example.com">
                                @error('website')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Currency</label>
                                <select name="currency" class="form-control">
                                    <option value="TZS" {{ old('currency', $account->currency) == 'TZS' ? 'selected' : '' }}>Tanzanian Shilling (TZS)</option>
                                    <option value="USD" {{ old('currency', $account->currency) == 'USD' ? 'selected' : '' }}>US Dollar (USD)</option>
                                    <option value="EUR" {{ old('currency', $account->currency) == 'EUR' ? 'selected' : '' }}>Euro (EUR)</option>
                                    <option value="GBP" {{ old('currency', $account->currency) == 'GBP' ? 'selected' : '' }}>British Pound (GBP)</option>
                                    <option value="KES" {{ old('currency', $account->currency) == 'KES' ? 'selected' : '' }}>Kenyan Shilling (KES)</option>
                                </select>
                                @error('currency')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Timezone</label>
                                <select name="timezone" class="form-control">
                                    <option value="Africa/Dar_es_Salaam" {{ old('timezone', $account->timezone) == 'Africa/Dar_es_Salaam' ? 'selected' : '' }}>East Africa Time (EAT)</option>
                                    <option value="Africa/Nairobi" {{ old('timezone', $account->timezone) == 'Africa/Nairobi' ? 'selected' : '' }}>Nairobi Time</option>
                                    <option value="UTC" {{ old('timezone', $account->timezone) == 'UTC' ? 'selected' : '' }}>UTC</option>
                                </select>
                                @error('timezone')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="3">{{ old('address', $account->address) }}</textarea>
                                @error('address')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Company Logo</label>
                                <input type="file" name="logo" class="form-control" accept="image/*">
                                <small class="text-muted">Upload a new logo image (JPEG, PNG, GIF). Max size: 2MB. Leave empty to keep current logo.</small>
                                @if($account->logo)
                                    <div class="mt-2">
                                        <img src="{{ $account->logo_url }}" alt="Current Logo" style="max-height: 100px;" class="img-thumbnail">
                                        <small class="text-muted d-block">Current logo</small>
                                    </div>
                                @endif
                                @error('logo')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="4" placeholder="Tell us about your company...">{{ old('description', $account->description) }}</textarea>
                                @error('description')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Update Account</button>
                            <a href="{{ route('accountsetup') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
