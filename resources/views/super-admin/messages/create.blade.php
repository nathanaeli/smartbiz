@extends('layouts.super-admin')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-sm">
            <div class="card-header bg-gradient-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Send Message to Tenants</h4>
                        <small class="text-white-50">Compose and send messages with attachments and media</small>
                    </div>
                    <a href="{{ route('super-admin.messages.index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Back to Messages
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('super-admin.messages.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Recipient Type <span class="text-danger">*</span></label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="recipient_type" id="single" value="single"
                                       {{ old('recipient_type', 'single') === 'single' ? 'checked' : '' }}>
                                <label class="form-check-label" for="single">
                                    Send to specific tenant
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="recipient_type" id="all" value="all"
                                       {{ old('recipient_type') === 'all' ? 'checked' : '' }}>
                                <label class="form-check-label" for="all">
                                    Send to all tenants
                                </label>
                            </div>
                            @error('recipient_type')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3" id="tenant-select" style="{{ old('recipient_type', 'single') === 'single' ? '' : 'display: none;' }}">
                            <label for="tenant_id" class="form-label">Select Tenant <span class="text-danger">*</span></label>
                            <select class="form-select @error('tenant_id') is-invalid @enderror" id="tenant_id" name="tenant_id">
                                <option value="">Choose a tenant...</option>
                                @foreach($tenants as $tenant)
                                <option value="{{ $tenant->id }}" {{ old('tenant_id') == $tenant->id ? 'selected' : '' }}>
                                    {{ $tenant->user->name }} ({{ $tenant->user->email }})
                                </option>
                                @endforeach
                            </select>
                            @error('tenant_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('subject') is-invalid @enderror"
                               id="subject" name="subject" value="{{ old('subject') }}" required>
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="body" class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('body') is-invalid @enderror"
                                  id="body" name="body" rows="8" required
                                  placeholder="Compose your message here...">{{ old('body') }}</textarea>
                        @error('body')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Attachment Section -->
                    <div class="card mb-4 border-info">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 text-info">
                                <i class="fas fa-paperclip me-2"></i>Attachment (Optional)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="attachment" class="form-label">Upload File</label>
                                <input type="file" class="form-control @error('attachment') is-invalid @enderror"
                                       id="attachment" name="attachment"
                                       accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.gif,.xls,.xlsx,.ppt,.pptx,.zip,.rar">
                                <div class="form-text">
                                    <small class="text-muted">
                                        Supported formats: PDF, Word, Excel, PowerPoint, Images, Text files, Archives (Max: 10MB)
                                    </small>
                                </div>
                                @error('attachment')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Video Section -->
                    <div class="card mb-4 border-success">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 text-success">
                                <i class="fas fa-video me-2"></i>Video URL (Optional)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="video_url" class="form-label">Video Link</label>
                                <input type="url" class="form-control @error('video_url') is-invalid @enderror"
                                       id="video_url" name="video_url" value="{{ old('video_url') }}"
                                       placeholder="https://youtube.com/watch?v=... or https://vimeo.com/...">
                                <div class="form-text">
                                    <small class="text-muted">
                                        Supported platforms: YouTube, Vimeo, Dailymotion
                                    </small>
                                </div>
                                @error('video_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div id="video-preview" class="mt-3" style="display: none;">
                                <h6>Video Preview:</h6>
                                <div id="video-preview-content"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Message Preview -->
                    <div class="card mb-4 border-warning">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 text-warning">
                                <i class="fas fa-eye me-2"></i>Message Preview
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="message-preview" class="border p-3 bg-light rounded">
                                <strong>Subject:</strong> <span id="preview-subject">No subject</span><br>
                                <strong>Message:</strong><br>
                                <div id="preview-body" class="mt-2">No message content</div>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Indicator -->
                    <div id="progress-container" class="mb-3" style="display: none;">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                 role="progressbar" style="width: 0%" id="progress-bar">
                                <span id="progress-text">Preparing message...</span>
                            </div>
                        </div>
                        <div id="progress-details" class="mt-2 small text-muted"></div>
                    </div>

                    <!-- Status Messages -->
                    <div id="status-messages" style="display: none;">
                        <div id="success-message" class="alert alert-success" style="display: none;">
                            <i class="fas fa-check-circle me-2"></i>
                            <span id="success-text"></span>
                        </div>
                        <div id="error-message" class="alert alert-danger" style="display: none;">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <span id="error-text"></span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('super-admin.messages.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary" id="submit-btn">
                            <i class="fas fa-paper-plane me-1"></i>Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.preview-video {
    max-width: 100%;
    height: 200px;
    border-radius: 8px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const singleRadio = document.getElementById('single');
    const allRadio = document.getElementById('all');
    const tenantSelect = document.getElementById('tenant-select');
    const subjectInput = document.getElementById('subject');
    const bodyInput = document.getElementById('body');
    const videoUrlInput = document.getElementById('video_url');

    // Toggle tenant selection
    function toggleTenantSelect() {
        if (singleRadio.checked) {
            tenantSelect.style.display = 'block';
            document.getElementById('tenant_id').required = true;
        } else {
            tenantSelect.style.display = 'none';
            document.getElementById('tenant_id').required = false;
        }
    }

    // Live preview functionality
    function updatePreview() {
        const subject = subjectInput.value || 'No subject';
        const body = bodyInput.value || 'No message content';

        document.getElementById('preview-subject').textContent = subject;
        document.getElementById('preview-body').innerHTML = body.replace(/\n/g, '<br>');
    }

    // Video preview functionality
    function updateVideoPreview() {
        const videoUrl = videoUrlInput.value.trim();
        const previewDiv = document.getElementById('video-preview');
        const previewContent = document.getElementById('video-preview-content');

        if (videoUrl && isValidVideoUrl(videoUrl)) {
            const videoId = getVideoId(videoUrl);
            const platform = getVideoPlatform(videoUrl);

            let embedCode = '';
            switch (platform) {
                case 'YouTube':
                    embedCode = `<iframe width="100%" height="200" src="https://www.youtube.com/embed/${videoId}" frameborder="0" allowfullscreen class="preview-video"></iframe>`;
                    break;
                case 'Vimeo':
                    embedCode = `<iframe width="100%" height="200" src="https://player.vimeo.com/video/${videoId}" frameborder="0" allowfullscreen class="preview-video"></iframe>`;
                    break;
                case 'Dailymotion':
                    embedCode = `<iframe width="100%" height="200" src="https://www.dailymotion.com/embed/video/${videoId}" frameborder="0" allowfullscreen class="preview-video"></iframe>`;
                    break;
            }

            previewContent.innerHTML = embedCode;
            previewDiv.style.display = 'block';
        } else {
            previewDiv.style.display = 'none';
        }
    }

    function isValidVideoUrl(url) {
        const patterns = [
            /youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/,
            /youtu\.be\/([a-zA-Z0-9_-]+)/,
            /youtube\.com\/embed\/([a-zA-Z0-9_-]+)/,
            /vimeo\.com\/([0-9]+)/,
            /vimeo\.com\/video\/([0-9]+)/,
            /dailymotion\.com\/video\/([a-zA-Z0-9]+)/,
            /dai\.ly\/([a-zA-Z0-9]+)/,
        ];

        return patterns.some(pattern => pattern.test(url));
    }

    function getVideoId(url) {
        const patterns = [
            /youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/,
            /youtu\.be\/([a-zA-Z0-9_-]+)/,
            /youtube\.com\/embed\/([a-zA-Z0-9_-]+)/,
            /vimeo\.com\/([0-9]+)/,
            /vimeo\.com\/video\/([0-9]+)/,
            /dailymotion\.com\/video\/([a-zA-Z0-9]+)/,
            /dai\.ly\/([a-zA-Z0-9]+)/,
        ];

        for (let pattern of patterns) {
            const match = url.match(pattern);
            if (match) return match[1];
        }
        return null;
    }

    function getVideoPlatform(url) {
        if (url.includes('youtube.com') || url.includes('youtu.be')) return 'YouTube';
        if (url.includes('vimeo.com')) return 'Vimeo';
        if (url.includes('dailymotion.com') || url.includes('dai.ly')) return 'Dailymotion';
        return 'Unknown';
    }

    // File size validation
    document.getElementById('attachment').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const maxSize = 10 * 1024 * 1024; // 10MB
            if (file.size > maxSize) {
                showError('File size must be less than 10MB');
                e.target.value = '';
            } else {
                showSuccess(`File "${file.name}" selected (${formatBytes(file.size)})`);
            }
        }
    });

    // Form submission with progress tracking
    const form = document.querySelector('form');
    const submitBtn = document.getElementById('submit-btn');
    const progressContainer = document.getElementById('progress-container');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const progressDetails = document.getElementById('progress-details');

    form.addEventListener('submit', function(e) {
        // Basic validation
        if (!validateForm()) {
            e.preventDefault();
            return false;
        }

        // Show progress
        progressContainer.style.display = 'block';
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sending...';

        updateProgress(10, 'Validating form data...');
        setTimeout(() => updateProgress(30, 'Processing attachments...'), 500);
        setTimeout(() => updateProgress(60, 'Sending message...'), 1000);
    });

    function validateForm() {
        const recipientType = document.querySelector('input[name="recipient_type"]:checked');
        const tenantId = document.getElementById('tenant_id');
        const subject = document.getElementById('subject').value.trim();
        const body = document.getElementById('body').value.trim();

        if (!recipientType) {
            showError('Please select recipient type');
            return false;
        }

        if (recipientType.value === 'single' && !tenantId.value) {
            showError('Please select a tenant');
            return false;
        }

        if (!subject) {
            showError('Subject is required');
            return false;
        }

        if (!body) {
            showError('Message body is required');
            return false;
        }

        return true;
    }

    function updateProgress(percent, text) {
        progressBar.style.width = percent + '%';
        progressText.textContent = text;
        progressDetails.textContent = `Step ${Math.floor(percent / 25) + 1} of 4: ${text}`;
    }

    function showSuccess(message) {
        const successDiv = document.getElementById('success-message');
        const successText = document.getElementById('success-text');
        successText.textContent = message;
        successDiv.style.display = 'block';
        document.getElementById('status-messages').style.display = 'block';

        setTimeout(() => {
            successDiv.style.display = 'none';
        }, 5000);
    }

    function showError(message) {
        const errorDiv = document.getElementById('error-message');
        const errorText = document.getElementById('error-text');
        errorText.textContent = message;
        errorDiv.style.display = 'block';
        document.getElementById('status-messages').style.display = 'block';

        setTimeout(() => {
            errorDiv.style.display = 'none';
        }, 8000);
    }

    // Event listeners
    singleRadio.addEventListener('change', toggleTenantSelect);
    allRadio.addEventListener('change', toggleTenantSelect);
    subjectInput.addEventListener('input', updatePreview);
    bodyInput.addEventListener('input', updatePreview);
    videoUrlInput.addEventListener('input', updateVideoPreview);

    // Initialize
    toggleTenantSelect();
    updatePreview();
});
</script>
@endsection
