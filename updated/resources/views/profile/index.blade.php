@extends(auth()->user()->hasRole('superadmin') ? 'layouts.super-admin' : 'layouts.app')

@section('content')
@if(auth()->user()->hasRole('superadmin'))
<div class="container-fluid mt-n5 py-0">
@else
<div class="container-fluid content-inner pb-0">
@endif
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0"><i class="ri-user-line me-2"></i>My Profile</h4>
                    @if(auth()->user()->hasRole('superadmin'))
                        <a href="{{ route('super-admin.dashboard') }}" class="btn btn-outline-primary btn-sm">
                            <i class="ri-arrow-left-line me-1"></i>Back to Dashboard
                        </a>
                    @else
                        <a href="{{ route('tenant.dashboard') }}" class="btn btn-outline-primary btn-sm">
                            <i class="ri-arrow-left-line me-1"></i>Back to Dashboard
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="ri-check-circle-line me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="ri-error-warning-line me-2"></i>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row">
                        <!-- Profile Picture Section -->
                        <div class="col-lg-4 mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center p-4">
                                    <h5 class="card-title mb-4">Profile Picture</h5>

                                    <!-- Current Profile Picture -->
                                    <div class="profile-picture-container mb-4">
                                        <div class="position-relative d-inline-block">
                                            @if($user->profile_picture)
                                                <img src="{{ asset('storage/profiles/' . $user->profile_picture) }}"
                                                     alt="Profile Picture"
                                                     class="rounded-circle profile-img"
                                                     style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #e9ecef;">
                                            @else
                                                <div class="rounded-circle profile-placeholder d-flex align-items-center justify-content-center"
                                                     style="width: 150px; height: 150px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 3rem;">
                                                    <i class="ri-user-line"></i>
                                                </div>
                                            @endif

                                            <!-- Edit Overlay -->
                                            <div class="profile-edit-overlay rounded-circle d-flex align-items-center justify-content-center"
                                                 style="position: absolute; top: 0; left: 0; width: 150px; height: 150px; background: rgba(0,0,0,0.7); opacity: 0; transition: opacity 0.3s ease; cursor: pointer;"
                                                 onclick="document.getElementById('profile_picture').click()">
                                                <i class="ri-camera-line text-white" style="font-size: 2rem;"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <p class="text-muted small mb-3">Click the image to change your profile picture</p>

                                    @if($user->profile_picture)
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeProfilePicture()">
                                            <i class="ri-delete-bin-line me-1"></i>Remove Picture
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Profile Information Form -->
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title mb-4">Profile Information</h5>

                                    <form id="profileForm" action="{{ auth()->user()->hasRole('superadmin') ? route('super-admin.profile.update') : route('profile.update') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div id="formProgress" class="progress mb-3 d-none">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                                        </div>

                                        <!-- Hidden input for removing profile picture -->
                                        <input type="hidden" name="remove_profile_picture" id="remove_profile_picture" value="0">

                                        <!-- Profile Picture File Input (Hidden) -->
                                        <input type="file" name="profile_picture" id="profile_picture" class="d-none" accept="image/*" onchange="previewImage(this)">

                                        <!-- Name -->
                                        <div class="mb-3">
                                            <label for="name" class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                   id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Email -->
                                        <div class="mb-3">
                                            <label for="email" class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                                   id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Role (Read-only) -->
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Role</label>
                                            <input type="text" class="form-control" value="{{ $user->roles->first()?->name ?? 'User' }}" readonly>
                                            <small class="text-muted">Your account role (cannot be changed)</small>
                                        </div>

                                        <!-- Account Created -->
                                        <div class="mb-4">
                                            <label class="form-label fw-bold">Account Created</label>
                                            <input type="text" class="form-control" value="{{ $user->created_at->format('M d, Y \a\t H:i') }}" readonly>
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="d-flex gap-2">
                                            <button type="submit" id="submitBtn" class="btn btn-primary">
                                                <i class="ri-save-line me-2"></i><span id="submitText">Update Profile</span>
                                            </button>
                                            <button type="button" id="resetBtn" class="btn btn-outline-secondary" onclick="resetForm()">
                                                <i class="ri-refresh-line me-2"></i>Reset Changes
                                            </button>
                                            @if(!auth()->user()->hasRole('superadmin'))
                                            <a href="{{ route('password.change') }}" class="btn btn-outline-secondary">
                                                <i class="ri-lock-line me-2"></i>Change Password
                                            </a>
                                            @endif
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

<style>
.profile-img:hover + .profile-edit-overlay,
.profile-placeholder:hover + .profile-edit-overlay,
.profile-edit-overlay:hover {
    opacity: 1 !important;
}

.profile-placeholder {
    transition: transform 0.3s ease;
}

.profile-placeholder:hover {
    transform: scale(1.05);
}
</style>

<script>
let originalData = {};
let hasChanges = false;

// Store original form data on page load
document.addEventListener('DOMContentLoaded', function() {
    storeOriginalData();
    setupRealTimeValidation();
    setupFormSubmission();
});

// Store original form values
function storeOriginalData() {
    const form = document.getElementById('profileForm');
    const inputs = form.querySelectorAll('input[type="text"], input[type="email"], input[type="file"]');
    inputs.forEach(input => {
        if (input.type === 'file') {
            originalData[input.name] = input.files.length > 0 ? input.files[0].name : '';
        } else {
            originalData[input.name] = input.value;
        }
    });
    updateChangeIndicator();
}

// Real-time validation
function setupRealTimeValidation() {
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');

    nameInput.addEventListener('input', function() {
        validateName(this);
        updateChangeIndicator();
    });

    emailInput.addEventListener('input', function() {
        validateEmail(this);
        updateChangeIndicator();
    });
}

function validateName(input) {
    const value = input.value.trim();
    const isValid = value.length >= 2 && value.length <= 255;

    input.classList.toggle('is-valid', isValid && value !== '');
    input.classList.toggle('is-invalid', !isValid && value !== '');

    const feedback = input.parentNode.querySelector('.invalid-feedback') || createFeedback(input);
    if (!isValid && value !== '') {
        feedback.textContent = 'Name must be between 2 and 255 characters.';
        feedback.style.display = 'block';
    } else {
        feedback.style.display = 'none';
    }

    return isValid || value === '';
}

function validateEmail(input) {
    const value = input.value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const isValid = emailRegex.test(value);

    input.classList.toggle('is-valid', isValid && value !== '');
    input.classList.toggle('is-invalid', !isValid && value !== '');

    const feedback = input.parentNode.querySelector('.invalid-feedback') || createFeedback(input);
    if (!isValid && value !== '') {
        feedback.textContent = 'Please enter a valid email address.';
        feedback.style.display = 'block';
    } else {
        feedback.style.display = 'none';
    }

    return isValid || value === '';
}

function createFeedback(input) {
    const feedback = document.createElement('div');
    feedback.className = 'invalid-feedback';
    input.parentNode.appendChild(feedback);
    return feedback;
}

// Update change indicator
function updateChangeIndicator() {
    const form = document.getElementById('profileForm');
    const inputs = form.querySelectorAll('input[type="text"], input[type="email"]');
    let changed = false;

    inputs.forEach(input => {
        if (input.value !== originalData[input.name]) {
            changed = true;
        }
    });

    // Check file input
    const fileInput = document.getElementById('profile_picture');
    if (fileInput.files.length > 0) {
        changed = true;
    }

    hasChanges = changed;

    // Update submit button
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.classList.toggle('btn-warning', changed);
    submitBtn.classList.toggle('btn-primary', !changed);

    // Show reset button if changes exist
    document.getElementById('resetBtn').style.display = changed ? 'inline-block' : 'none';
}

// Smart image preview with validation
function previewImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];

        // Validate file type
        if (!file.type.match('image.*')) {
            alert('Please select a valid image file.');
            input.value = '';
            return;
        }

        // Validate file size (2MB max)
        if (file.size > 2 * 1024 * 1024) {
            alert('Image size must be less than 2MB.');
            input.value = '';
            return;
        }

        const reader = new FileReader();

        reader.onload = function(e) {
            // Update the profile picture preview
            const container = input.closest('.profile-picture-container');
            const img = container.querySelector('.profile-img') || container.querySelector('.profile-placeholder');

            if (img.classList.contains('profile-placeholder')) {
                // Replace placeholder with image
                img.outerHTML = `<img src="${e.target.result}" alt="Profile Picture" class="rounded-circle profile-img" style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #e9ecef;">`;
            } else {
                // Update existing image
                img.src = e.target.result;
            }

            // Add success indicator
            showImageUploadSuccess();
        };

        reader.readAsDataURL(file);
        updateChangeIndicator();
    }
}

function showImageUploadSuccess() {
    // Create temporary success message
    const container = document.querySelector('.profile-picture-container');
    const successMsg = document.createElement('div');
    successMsg.className = 'alert alert-success alert-dismissible fade show mt-2';
    successMsg.innerHTML = '<i class="ri-check-circle-line me-2"></i>Image uploaded successfully! <button type="button" class="btn-close" data-bs-dismiss="alert"></button>';

    // Remove existing success messages
    container.querySelectorAll('.alert-success').forEach(el => el.remove());

    container.appendChild(successMsg);

    // Auto-remove after 3 seconds
    setTimeout(() => {
        if (successMsg.parentNode) {
            successMsg.remove();
        }
    }, 3000);
}

// Enhanced remove profile picture
function removeProfilePicture() {
    if (confirm('Are you sure you want to remove your profile picture? This action cannot be undone.')) {
        // Show loading
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="ri-loader-4-line me-1"></i>Removing...';
        btn.disabled = true;

        document.getElementById('remove_profile_picture').value = '1';
        document.getElementById('profileForm').submit();
    }
}

// Reset form to original state
function resetForm() {
    if (hasChanges && confirm('Are you sure you want to reset all changes?')) {
        // Reset text inputs
        document.getElementById('name').value = originalData['name'];
        document.getElementById('email').value = originalData['email'];

        // Reset file input
        document.getElementById('profile_picture').value = '';

        // Reset profile picture display
        const container = document.querySelector('.profile-picture-container');
        const currentImg = container.querySelector('.profile-img');
        const placeholder = container.querySelector('.profile-placeholder');

        if (currentImg && !originalData['profile_picture']) {
            // Remove image, show placeholder
            currentImg.outerHTML = '<div class="rounded-circle profile-placeholder d-flex align-items-center justify-content-center" style="width: 150px; height: 150px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 3rem;"><i class="ri-user-line"></i></div>';
        }

        // Clear validation states
        document.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
            el.classList.remove('is-valid', 'is-invalid');
        });

        updateChangeIndicator();
    }
}

// Smart form submission with progress
function setupFormSubmission() {
    const form = document.getElementById('profileForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const progressBar = document.querySelector('.progress-bar');

    form.addEventListener('submit', function(e) {
        // Validate form before submission
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');

        const nameValid = validateName(nameInput);
        const emailValid = validateEmail(emailInput);

        if (!nameValid || !emailValid) {
            e.preventDefault();
            alert('Please fix the validation errors before submitting.');
            return;
        }

        // Ensure CSRF token is present and valid
        const csrfToken = document.querySelector('input[name="_token"]');
        if (!csrfToken || !csrfToken.value) {
            e.preventDefault();
            alert('Security token missing. Please refresh the page and try again.');
            return;
        }

        // Show loading state
        submitBtn.disabled = true;
        submitText.textContent = 'Updating...';
        submitBtn.innerHTML = '<i class="ri-loader-4-line me-2"></i><span id="submitText">Updating...</span>';

        // Show progress bar
        document.getElementById('formProgress').classList.remove('d-none');
        progressBar.style.width = '0%';

        // Simulate progress (since we can't track actual upload progress easily)
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress > 90) progress = 90;
            progressBar.style.width = progress + '%';
        }, 200);

        // Store interval to clear it later
        form.dataset.progressInterval = progressInterval;
    });
}

// Auto-save disabled to prevent session issues
function setupAutoSave() {
    // Auto-save disabled to maintain session stability
    console.log('Auto-save disabled to prevent session regeneration issues');
}
</script>
@endsection
