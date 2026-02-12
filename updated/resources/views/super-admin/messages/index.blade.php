@extends('layouts.super-admin')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-sm">
            <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0"><i class="fas fa-envelope me-2"></i>Messages Management</h4>
                    <small class="text-white-50">Manage and monitor all tenant communications</small>
                </div>
                <a href="{{ route('super-admin.messages.create') }}" class="btn btn-light">
                    <i class="fas fa-plus me-1"></i>Send New Message
                </a>
            </div>
            <div class="card-body">
                <!-- Success/Error Messages -->
                @if (session()->has('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-envelope fa-2x mb-2"></i>
                                <h5>{{ $messages->total() }}</h5>
                                <small>Total Messages</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-envelope-open fa-2x mb-2"></i>
                                <h5>{{ $messages->where('read_at', null)->count() }}</h5>
                                <small>Unread Messages</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-broadcast-tower fa-2x mb-2"></i>
                                <h5>{{ $messages->where('is_broadcast', true)->count() }}</h5>
                                <small>Broadcast Messages</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-reply fa-2x mb-2"></i>
                                <h5>{{ $messages->whereNotNull('parent_id')->count() }}</h5>
                                <small>Replies</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search and Filters -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <form method="GET" action="{{ route('super-admin.messages.index') }}" class="d-flex">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" name="search" class="form-control"
                                       placeholder="Search messages by subject, content, or sender/receiver..."
                                       value="{{ request('search') }}">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i>Search
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="{{ route('super-admin.messages.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Clear Filters
                        </a>
                    </div>
                </div>

                <!-- Messages List -->
                <div class="row">
                    @forelse($messages as $message)
                    <div class="col-lg-12 mb-3">
                        <div class="card message-card {{ $message->read_at ? 'border-light' : 'border-warning shadow-sm' }}"
                             style="{{ $message->read_at ? '' : 'border-left: 4px solid #ffc107 !important;' }}">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <div class="d-flex align-items-start">
                                            <div class="message-icon me-3">
                                                @if($message->is_broadcast)
                                                    <i class="fas fa-broadcast-tower text-primary fa-2x"></i>
                                                @elseif($message->isReply())
                                                    <i class="fas fa-reply text-info fa-2x"></i>
                                                @else
                                                    <i class="fas fa-envelope {{ $message->read_at ? 'text-muted' : 'text-warning' }} fa-2x"></i>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="card-title mb-1">
                                                    <a href="{{ route('super-admin.messages.show', $message->id) }}"
                                                       class="text-decoration-none {{ $message->read_at ? 'text-muted' : 'text-dark fw-bold' }}">
                                                        {{ Str::limit($message->subject, 60) }}
                                                    </a>
                                                </h6>
                                                <p class="card-text text-muted small mb-2">
                                                    {{ Str::limit(strip_tags($message->body), 100) }}
                                                </p>
                                                <div class="message-meta">
                                                    <small class="text-muted">
                                                        <i class="fas fa-user me-1"></i>
                                                        <strong>From:</strong> {{ $message->sender->name ?? 'System' }}
                                                        @if(!$message->is_broadcast)
                                                            <span class="mx-2">•</span>
                                                            <i class="fas fa-user-tie me-1"></i>
                                                            <strong>To:</strong> {{ $message->tenant->user->name ?? 'Unknown Tenant' }}
                                                        @endif
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <div class="mb-2">
                                            @if($message->is_broadcast)
                                                <span class="badge bg-primary"><i class="fas fa-broadcast-tower me-1"></i>Broadcast</span>
                                            @else
                                                <span class="badge bg-secondary"><i class="fas fa-user me-1"></i>Direct</span>
                                            @endif

                                            @php $sentiment = $message->analyzeSentiment() @endphp
                                            <span class="badge bg-{{ $sentiment === 'Positive' ? 'success' : ($sentiment === 'Negative' ? 'danger' : ($sentiment === 'Urgent' ? 'warning' : 'secondary')) }} ms-1">
                                                <i class="fas fa-{{ $sentiment === 'Positive' ? 'smile' : ($sentiment === 'Negative' ? 'frown' : ($sentiment === 'Urgent' ? 'exclamation-triangle' : 'meh')) }} me-1"></i>
                                                {{ $sentiment }}
                                            </span>
                                        </div>
                                        <div class="mb-2">
                                            @if($message->read_at)
                                                <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Read</span>
                                            @else
                                                <span class="badge bg-warning"><i class="fas fa-envelope me-1"></i>Unread</span>
                                            @endif
                                        </div>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-clock me-1"></i>{{ $message->created_at->format('M d, Y H:i') }}
                                        </small>
                                        <div class="mt-2">
                                            <a href="{{ route('super-admin.messages.show', $message->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i>View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                @if($message->hasAttachment() || $message->hasVideo())
                                <div class="mt-3 pt-3 border-top">
                                    <small class="text-muted">
                                        @if($message->hasAttachment())
                                            <i class="fas fa-paperclip me-1"></i>Has attachment
                                        @endif
                                        @if($message->hasVideo())
                                            @if($message->hasAttachment()) <span class="mx-2">•</span> @endif
                                            <i class="fas fa-video me-1"></i>Contains video
                                        @endif
                                    </small>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No messages found</h5>
                                <p class="text-muted">There are no messages matching your criteria.</p>
                            </div>
                        </div>
                    </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $messages->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.message-card {
    transition: all 0.3s ease;
}
.message-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
}
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.message-icon {
    min-width: 50px;
    text-align: center;
}
</style>
@endsection
