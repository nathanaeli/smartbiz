@extends('layouts.super-admin')

@section('content')
<div class="row">
    <div class="col-lg-12 card">
        <!-- Header Actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0"><i class="fas fa-envelope-open-text me-2 text-primary"></i>Message Details</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('super-admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('super-admin.messages.index') }}">Messages</a></li>
                        <li class="breadcrumb-item active">Message #{{ $message->id }}</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('super-admin.messages.index') }}" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left me-1"></i>Back to Messages
                </a>
                @if(!$message->read_at)
                <button class="btn btn-success" onclick="markAsRead({{ $message->id }})">
                    <i class="fas fa-check-circle me-1"></i>Mark as Read
                </button>
                @endif
            </div>
        </div>

        <div class="row">
            <!-- Main Message Content -->
            <div class="col-lg-8">
                <div class="card shadow-sm message-detail-card">
                    <div class="card-header bg-gradient-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-envelope me-2"></i>{{ $message->subject }}
                            </h5>
                            <div>
                                @php $sentiment = $message->analyzeSentiment() @endphp
                                <span class="badge bg-{{ $sentiment === 'Positive' ? 'success' : ($sentiment === 'Negative' ? 'danger' : ($sentiment === 'Urgent' ? 'warning' : 'secondary')) }}">
                                    <i class="fas fa-{{ $sentiment === 'Positive' ? 'smile' : ($sentiment === 'Negative' ? 'frown' : ($sentiment === 'Urgent' ? 'exclamation-triangle' : 'meh')) }} me-1"></i>
                                    {{ $sentiment }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Message Meta Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="meta-item">
                                    <i class="fas fa-user text-primary me-2"></i>
                                    <strong>From:</strong>
                                    <span class="ms-1">{{ $message->sender->name ?? 'System' }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="meta-item">
                                    <i class="fas fa-user-tie text-info me-2"></i>
                                    <strong>To:</strong>
                                    <span class="ms-1">
                                        @if($message->is_broadcast)
                                            <span class="badge bg-primary">All Tenants (Broadcast)</span>
                                        @else
                                            {{ $message->tenant && $message->tenant->user ? $message->tenant->user->name : 'Unknown Tenant' }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="meta-item">
                                    <i class="fas fa-clock text-warning me-2"></i>
                                    <strong>Sent:</strong>
                                    <span class="ms-1">{{ $message->created_at->format('M d, Y \a\t H:i') }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="meta-item">
                                    <i class="fas fa-eye {{ $message->read_at ? 'text-success' : 'text-warning' }} me-2"></i>
                                    <strong>Status:</strong>
                                    <span class="ms-1">
                                        @if($message->read_at)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle me-1"></i>Read on {{ $message->read_at->format('M d, Y \a\t H:i') }}
                                            </span>
                                        @else
                                            <span class="badge bg-warning">
                                                <i class="fas fa-envelope me-1"></i>Unread
                                            </span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Message Type and Thread Info -->
                        @if($message->isReply() || $message->replies->count() > 0)
                        <div class="row mb-4">
                            @if($message->isReply())
                            <div class="col-md-6">
                                <div class="meta-item">
                                    <i class="fas fa-reply text-info me-2"></i>
                                    <strong>Reply to:</strong>
                                    <a href="{{ route('super-admin.messages.show', $message->parent_id) }}" class="ms-1 text-decoration-none">
                                        Message #{{ $message->parent_id }}
                                    </a>
                                </div>
                            </div>
                            @endif
                            @if($message->replies->count() > 0)
                            <div class="col-md-6">
                                <div class="meta-item">
                                    <i class="fas fa-comments text-success me-2"></i>
                                    <strong>Replies:</strong>
                                    <span class="badge bg-info ms-1">{{ $message->replies->count() }}</span>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endif

                        <hr class="my-4">

                        <!-- Message Content -->
                        <div class="message-content mb-4">
                            <div class="content-wrapper" style="background: #f8f9fa; padding: 20px; border-radius: 10px; border-left: 4px solid #007bff;">
                                <div style="white-space: pre-wrap; line-height: 1.6; color: #333;">
                                    {{ $message->body }}
                                </div>
                            </div>
                        </div>

                        <!-- Attachments Section -->
                        @if($message->hasAttachment())
                        <div class="attachment-section mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-paperclip me-2"></i>Attachment
                            </h6>
                            <div class="attachment-card" style="border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; background: #f8f9fa;">
                                <div class="d-flex align-items-center">
                                    <div class="attachment-icon me-3">
                                        @if($message->isImageAttachment())
                                            <i class="fas fa-image fa-2x text-success"></i>
                                        @else
                                            <i class="fas fa-file fa-2x text-primary"></i>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ $message->attachment_name }}</h6>
                                        <small class="text-muted">{{ $message->getFormattedFileSize() }}</small>
                                    </div>
                                    <div>
                                        <a href="{{ $message->getAttachmentUrl() }}" target="_blank" class="btn btn-primary btn-sm">
                                            <i class="fas fa-download me-1"></i>Download
                                        </a>
                                        @if($message->isImageAttachment())
                                        <button class="btn btn-outline-info btn-sm ms-2" onclick="previewImage('{{ $message->getAttachmentUrl() }}')">
                                            <i class="fas fa-eye me-1"></i>Preview
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Video Section -->
                        @if($message->hasVideo())
                        <div class="video-section mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-video me-2"></i>Video Content
                            </h6>
                            <div class="video-container">
                                {!! $message->getVideoEmbedHtml() !!}
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-external-link-alt me-1"></i>
                                    Platform: {{ $message->getVideoPlatform() }}
                                </small>
                            </div>
                        </div>
                        @endif

                        <!-- Reply Section -->
                        <div class="reply-section mt-4 pt-4 border-top">
                            <button class="btn btn-outline-primary" onclick="toggleReplyForm()">
                                <i class="fas fa-reply me-1"></i>Reply to this Message
                            </button>

                            <div id="replyForm" class="mt-3" style="display: none;">
                                <form method="POST" action="{{ route('super-admin.messages.reply', $message->id) }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="reply_subject" class="form-label">Subject</label>
                                            <input type="text" class="form-control" id="reply_subject" name="subject"
                                                   value="Re: {{ $message->subject }}" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="reply_video_url" class="form-label">Video URL (Optional)</label>
                                            <input type="url" class="form-control" id="reply_video_url" name="video_url"
                                                   placeholder="https://youtube.com/watch?v=...">
                                            <div class="form-text small">YouTube, Vimeo, or Dailymotion</div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="reply_body" class="form-label">Message</label>
                                        <textarea class="form-control" id="reply_body" name="body" rows="6" required
                                                  placeholder="Compose your reply..."></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="reply_attachment" class="form-label">Attachment (Optional)</label>
                                        <input type="file" class="form-control" id="reply_attachment" name="attachment"
                                               accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.gif,.xls,.xlsx,.ppt,.pptx,.zip,.rar">
                                        <div class="form-text small">Max 10MB. Supported: PDF, Word, Excel, Images, etc.</div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane me-1"></i>Send Reply
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="toggleReplyForm()">
                                            <i class="fas fa-times me-1"></i>Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Replies Section -->
                @if($message->replies->count() > 0)
                <div class="card mt-4 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-comments me-2 text-success"></i>
                            Replies ({{ $message->replies->count() }})
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach($message->replies as $reply)
                        <div class="reply-item mb-3 pb-3 border-bottom">
                            <div class="d-flex">
                                <div class="reply-avatar me-3">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>{{ $reply->sender->name ?? 'Unknown' }}</strong>
                                            <small class="text-muted ms-2">{{ $reply->created_at->format('M d, Y H:i') }}</small>
                                        </div>
                                        <a href="{{ route('super-admin.messages.show', $reply->id) }}" class="btn btn-sm btn-outline-info">
                                            View Full Reply
                                        </a>
                                    </div>
                                    <div class="mt-2">
                                        <p class="mb-1">{{ Str::limit($reply->body, 200) }}</p>
                                        @if(strlen($reply->body) > 200)
                                        <a href="{{ route('super-admin.messages.show', $reply->id) }}" class="text-primary small">Read more...</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Message Statistics -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>Message Statistics
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="stat-item d-flex justify-content-between mb-2">
                            <span><i class="fas fa-envelope me-1"></i>Type:</span>
                            <span>
                                @if($message->is_broadcast)
                                    <span class="badge bg-primary">Broadcast</span>
                                @else
                                    <span class="badge bg-secondary">Direct</span>
                                @endif
                            </span>
                        </div>
                        <div class="stat-item d-flex justify-content-between mb-2">
                            <span><i class="fas fa-clock me-1"></i>Response Time:</span>
                            <span>
                                @if($message->read_at)
                                    {{ $message->created_at->diffForHumans($message->read_at, true) }}
                                @else
                                    <span class="text-warning">Not read yet</span>
                                @endif
                            </span>
                        </div>
                        <div class="stat-item d-flex justify-content-between mb-2">
                            <span><i class="fas fa-comments me-1"></i>Replies:</span>
                            <span>{{ $message->replies->count() }}</span>
                        </div>
                        <div class="stat-item d-flex justify-content-between">
                            <span><i class="fas fa-brain me-1"></i>Sentiment:</span>
                            <span class="badge bg-{{ $sentiment === 'Positive' ? 'success' : ($sentiment === 'Negative' ? 'danger' : ($sentiment === 'Urgent' ? 'warning' : 'secondary')) }}">
                                {{ $sentiment }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Tenant Information (if not broadcast and tenant exists) -->
                @if(!$message->is_broadcast && $message->tenant && $message->tenant->user)
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-user-tie me-2"></i>Tenant Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 60px; height: 60px; font-size: 24px;">
                                <i class="fas fa-user-tie"></i>
                            </div>
                        </div>
                        <div class="tenant-info">
                            <div class="mb-2">
                                <strong>Name:</strong> {{ $message->tenant->user->name ?? 'N/A' }}
                            </div>
                            <div class="mb-2">
                                <strong>Email:</strong> {{ $message->tenant->user->email ?? 'N/A' }}
                            </div>
                            <div class="mb-2">
                                <strong>Status:</strong>
                                <span class="badge bg-{{ $message->tenant->status === 'active' ? 'success' : 'danger' }}">
                                    {{ ucfirst($message->tenant->status ?? 'unknown') }}
                                </span>
                            </div>
                            <div class="mb-3">
                                <strong>Dukas:</strong> {{ $message->tenant->dukas->count() ?? 0 }}
                            </div>
                            <a href="{{ route('super-admin.tenants.show', $message->tenant->id) }}" class="btn btn-outline-primary btn-sm w-100">
                                <i class="fas fa-external-link-alt me-1"></i>View Full Profile
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Quick Actions -->
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="fas fa-bolt me-2"></i>Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('super-admin.messages.create') }}" class="btn btn-outline-primary">
                                <i class="fas fa-plus me-1"></i>Send New Message
                            </a>
                            @if(!$message->read_at)
                            <button class="btn btn-success" onclick="markAsRead({{ $message->id }})">
                                <i class="fas fa-check-circle me-1"></i>Mark as Read
                            </button>
                            @endif
                            <button class="btn btn-outline-info" onclick="printMessage()">
                                <i class="fas fa-print me-1"></i>Print Message
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Attachment Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="previewImage" src="" alt="Attachment" class="img-fluid" style="max-height: 70vh;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a id="downloadLink" href="" target="_blank" class="btn btn-primary">Download</a>
            </div>
        </div>
    </div>
</div>

<style>
.message-detail-card {
    border: none;
    border-radius: 15px;
    overflow: hidden;
}
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.meta-item {
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}
.meta-item:last-child {
    border-bottom: none;
}
.message-content {
    font-size: 16px;
    line-height: 1.6;
}
.attachment-card, .video-container {
    border-radius: 10px;
}
.reply-item {
    border-radius: 8px;
    background: #f8f9fa;
    padding: 15px;
    margin-bottom: 15px;
}
.reply-item:last-child {
    border-bottom: none !important;
    margin-bottom: 0;
}
.stat-item {
    padding: 5px 0;
}
.tenant-info div {
    padding: 5px 0;
}
</style>

<script>
function toggleReplyForm() {
    const form = document.getElementById('replyForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

function markAsRead(messageId) {
    if (confirm('Mark this message as read?')) {
        fetch(`/super-admin/messages/${messageId}/mark-read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error marking message as read');
            }
        });
    }
}

function previewImage(url) {
    document.getElementById('previewImage').src = url;
    document.getElementById('downloadLink').href = url;
    new bootstrap.Modal(document.getElementById('imagePreviewModal')).show();
}

function printMessage() {
    window.print();
}
</script>
@endsection
