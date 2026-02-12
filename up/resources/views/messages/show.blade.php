@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">{{ $message->subject }}</h4>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            Actions
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <li><a class="dropdown-item" href="#" onclick="copyMessageContent()"><i class="fa fa-copy me-2"></i>Copy Message</a></li>
                            <li><a class="dropdown-item" href="{{ route('messages.index') }}"><i class="fa fa-arrow-left me-2"></i>Back to Messages</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Message Content -->
                            <div class="message-content">
                                <div class="message-header mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="user-avatar me-3">
                                            <img src="{{ asset('assets/images/avatars/01.png') }}" alt="Avatar" class="avatar-50 rounded-circle">
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $message->sender->name }}</h6>
                                            <small class="text-muted">{{ $message->sent_at ? $message->sent_at->format('M d, Y \a\t H:i') : 'N/A' }}</small>
                                        </div>
                                    </div>
                                    @if($message->is_broadcast)
                                    <span class="badge bg-primary">Broadcast Message</span>
                                    @endif
                                </div>

                                <div class="message-body">
                                    <p class="mb-3">{{ nl2br(e($message->body)) }}</p>

                                    <!-- Video Display -->
                                    @if($message->hasVideo())
                                    <div class="message-video mb-3">
                                        <div class="video-container">
                                            {!! $message->getVideoEmbedHtml() !!}
                                        </div>
                                        <div class="video-info mt-2">
                                            <small class="text-muted">
                                                <i class="fas fa-video me-1"></i>
                                                {{ $message->getVideoPlatform() }} Video
                                                <a href="{{ $message->video_url }}" target="_blank" class="ms-2">
                                                    <i class="fas fa-external-link-alt"></i> Open in new tab
                                                </a>
                                            </small>
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Message Stats -->
                                    <div class="message-stats row">
                                        <div class="col-md-4">
                                            <small class="text-muted">
                                                <i class="fa fa-clock me-1"></i>
                                                Reading time: {{ ceil(str_word_count($message->body) / 200) }} min
                                            </small>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">
                                                <i class="fa fa-hashtag me-1"></i>
                                                Words: {{ str_word_count($message->body) }}
                                            </small>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">
                                                <i class="fa fa-magic me-1"></i>
                                                Sentiment:
                                                <span class="badge bg-{{ $message->analyzeSentiment() === 'Positive' ? 'success' : ($message->analyzeSentiment() === 'Negative' ? 'danger' : 'warning') }}">
                                                    {{ $message->analyzeSentiment() }}
                                                </span>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <!-- Message Info Sidebar -->
                            <div class="message-info">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Message Details</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="info-item mb-3">
                                            <label class="form-label">From:</label>
                                            <p class="mb-0">{{ $message->sender->name }}</p>
                                            <small class="text-muted">{{ $message->sender->email }}</small>
                                        </div>

                                        <div class="info-item mb-3">
                                            <label class="form-label">Sent:</label>
                                            <p class="mb-0">{{ $message->sent_at ? $message->sent_at->format('M d, Y \a\t H:i') : 'N/A' }}</p>
                                        </div>

                                        @if($message->tenant)
                                        <div class="info-item mb-3">
                                            <label class="form-label">To:</label>
                                            <p class="mb-0">{{ $message->tenant->name }}</p>
                                        </div>
                                        @endif

                                        <div class="info-item mb-3">
                                            <label class="form-label">Status:</label>
                                            <span class="badge bg-{{ $message->read_at ? 'success' : 'warning' }}">
                                                {{ $message->read_at ? 'Read' : 'Unread' }}
                                            </span>
                                        </div>

                                        <div class="info-item">
                                            <label class="form-label">Type:</label>
                                            <span class="badge bg-{{ $message->is_broadcast ? 'primary' : 'info' }}">
                                                {{ $message->is_broadcast ? 'Broadcast' : 'Direct' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attachments Section -->
                    @if($message->hasAttachment())
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title"><i class="fa fa-paperclip me-2"></i>Attachment</h5>
                                </div>
                                <div class="card-body">
                                    <div class="attachment-item d-flex align-items-center">
                                        <div class="attachment-icon me-3">
                                            @if($message->isImageAttachment())
                                                <img src="{{ $message->getAttachmentUrl() }}" alt="Attachment" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                            @else
                                                <i class="fa fa-file fa-3x text-primary"></i>
                                            @endif
                                        </div>
                                        <div class="attachment-info flex-grow-1">
                                            <h6 class="mb-1">{{ $message->attachment_name }}</h6>
                                            <small class="text-muted">{{ $message->getFormattedFileSize() }}</small>
                                        </div>
                                        <div class="attachment-actions">
                                            <a href="{{ $message->getAttachmentUrl() }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fa fa-eye me-1"></i>View
                                            </a>
                                            <a href="{{ $message->getAttachmentUrl() }}" download class="btn btn-sm btn-primary ms-2">
                                                <i class="fa fa-download me-1"></i>Download
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Conversation Thread -->
                    @if($message->replies->count() > 0)
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title"><i class="fa fa-comments me-2"></i>Conversation Thread ({{ $message->replies->count() }} replies)</h5>
                                    @if($message->replies->count() > 3)
                                    <div class="search-box">
                                        <input type="text" id="replySearch" class="form-control form-control-sm" placeholder="Search replies..." style="width: 200px;">
                                    </div>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <div class="conversation-thread">
                                        @foreach($message->replies as $index => $reply)
                                        <div class="reply-item mb-3" data-reply-id="{{ $reply->id }}">
                                            <div class="d-flex">
                                                <div class="user-avatar me-3">
                                                    <img src="{{ asset('assets/images/avatars/0' . (($index % 5) + 1) . '.png') }}" alt="Avatar" class="avatar-40 rounded-circle">
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="reply-header d-flex justify-content-between align-items-center mb-2">
                                                        <div>
                                                            <strong>{{ $reply->sender->name }}</strong>
                                                            <small class="text-muted ms-2">{{ $reply->sent_at ? $reply->sent_at->diffForHumans() : 'N/A' }}</small>
                                                        </div>
                                                        @if($reply->hasAttachment())
                                                        <span class="badge bg-info"><i class="fa fa-paperclip me-1"></i>Attachment</span>
                                                        @endif
                                                    </div>
                                                    <div class="reply-content">
                                                        <p class="mb-2">{{ nl2br(e($reply->body)) }}</p>

                                                        <!-- Video Display for Reply -->
                                                        @if($reply->hasVideo())
                                                        <div class="reply-video mb-2">
                                                            <div class="video-container">
                                                                {!! $reply->getVideoEmbedHtml() !!}
                                                            </div>
                                                            <div class="video-info mt-1">
                                                                <small class="text-muted">
                                                                    <i class="fas fa-video me-1"></i>
                                                                    {{ $reply->getVideoPlatform() }} Video
                                                                    <a href="{{ $reply->video_url }}" target="_blank" class="ms-1">
                                                                        <i class="fas fa-external-link-alt"></i>
                                                                    </a>
                                                                </small>
                                                            </div>
                                                        </div>
                                                        @endif

                                                        @if($reply->hasAttachment())
                                                        <div class="attachment-preview mt-2">
                                                            @if($reply->isImageAttachment())
                                                                <img src="{{ $reply->getAttachmentUrl() }}" alt="Attachment" class="img-thumbnail" style="max-width: 200px;">
                                                            @else
                                                                <div class="file-attachment">
                                                                    <i class="fa fa-file me-2"></i>
                                                                    <a href="{{ $reply->getAttachmentUrl() }}" target="_blank">{{ $reply->attachment_name }}</a>
                                                                    <small class="text-muted ms-2">({{ $reply->getFormattedFileSize() }})</small>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Reply Form -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title"><i class="fa fa-reply me-2"></i>Compose Your Reply</h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('messages.reply', $message) }}" method="POST" enctype="multipart/form-data">
                                        @csrf

                                        <div class="mb-3">
                                            <label for="body" class="form-label">Your Reply <span class="text-danger">*</span></label>
                                            <textarea class="form-control @error('body') is-invalid @enderror"
                                                      id="body"
                                                      name="body"
                                                      rows="4"
                                                      placeholder="Type your reply here..."
                                                      required>{{ old('body') }}</textarea>
                                            @error('body')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="video_url" class="form-label">
                                                <i class="fas fa-video me-2"></i>Video Link (Optional)
                                            </label>
                                            <input type="url" class="form-control @error('video_url') is-invalid @enderror"
                                                   id="video_url" name="video_url"
                                                   placeholder="https://youtube.com/watch?v=... or https://vimeo.com/..."
                                                   value="{{ old('video_url') }}">
                                            @error('video_url')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Supported platforms: YouTube, Vimeo, Dailymotion
                                            </small>
                                        </div>

                                        <div class="mb-3">
                                            <label for="attachment" class="form-label">Attachment (Optional)</label>
                                            <input type="file" class="form-control @error('attachment') is-invalid @enderror"
                                                   id="attachment" name="attachment" accept="image/*,.pdf,.doc,.docx,.txt">
                                            @error('attachment')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">Supported formats: Images, PDF, Word documents, Text files (Max: 10MB)</small>
                                        </div>

                                        <div class="d-flex justify-content-end">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fa fa-paper-plane me-2"></i>Send Reply
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

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Attachment Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="" class="img-fluid">
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Custom styles for message show page */
.message-stats {
    margin-bottom: 1rem;
}

.conversation-thread {
    max-height: 400px;
    overflow-y: auto;
}

.reply-item {
    border-left: 3px solid #e9ecef;
    padding-left: 1rem;
    margin-bottom: 1rem;
}

.reply-item:last-child {
    border-left: none;
}

/* Video Styles */
.message-video, .reply-video {
    margin: 1rem 0;
}

.video-container {
    position: relative;
    max-width: 100%;
    margin: 0 auto;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.video-container:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
}

.video-wrapper {
    position: relative;
    padding-bottom: 56.25%; /* 16:9 aspect ratio */
    height: 0;
    overflow: hidden;
    max-width: 100%;
    background: #000;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.video-wrapper iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: 0;
    border-radius: 12px;
}

.video-info {
    margin-top: 0.5rem;
    text-align: center;
}

.video-info a {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
}

.video-info a:hover {
    color: #5a67d8;
    text-decoration: underline;
}

/* Video input styling */
.form-control[type="url"] {
    background-image: linear-gradient(45deg, transparent 50%, #667eea 50%),
                      linear-gradient(135deg, #667eea 50%, transparent 50%);
    background-position: calc(100% - 18px) calc(1em + 2px),
                         calc(100% - 13px) calc(1em + 2px);
    background-size: 5px 5px, 5px 5px;
    background-repeat: no-repeat;
    padding-right: 40px;
}

.form-control[type="url"]:focus {
    background-image: linear-gradient(45deg, transparent 50%, #3182ce 50%),
                      linear-gradient(135deg, #3182ce 50%, transparent 50%);
}

/* Responsive video */
@media (max-width: 768px) {
    .video-container {
        margin: 0;
    }

    .video-wrapper {
        border-radius: 8px;
    }

    .video-wrapper iframe {
        border-radius: 8px;
    }
}

/* Video loading animation */
.video-container::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 40px;
    height: 40px;
    margin: -20px 0 0 -20px;
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-top: 4px solid #667eea;
    border-radius: 50%;
    animation: videoLoading 1s linear infinite;
    z-index: 1;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.video-container.loading::before {
    opacity: 1;
}

@keyframes videoLoading {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Video platform badges */
.video-platform-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.75rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.video-platform-badge i {
    font-size: 0.8rem;
}
</style>
@endpush

@push('scripts')
<script>
function copyMessageContent() {
    const content = document.querySelector('.message-body p');
    if (content) {
        const text = content.textContent || content.innerText;
        navigator.clipboard.writeText(text).then(function() {
            // Show success message
            showNotification('Message content copied to clipboard!', 'success');
        });
    }
}

function showNotification(message, type = 'info') {
    // Simple notification system
    alert(message);
}

// Search functionality for replies
document.getElementById('replySearch')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const replies = document.querySelectorAll('.reply-item');

    replies.forEach(reply => {
        const text = reply.textContent.toLowerCase();
        const isVisible = text.includes(searchTerm);
        reply.style.display = isVisible ? 'block' : 'none';
    });
});
</script>
@endpush
