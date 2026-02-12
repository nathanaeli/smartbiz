@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">{{ __('messages.messages') }}</h4>
                    <span class="badge bg-primary">{{ $messages->total() }} {{ __('messages.messages') }}</span>
                </div>
                <div class="card-body">
                    @if($messages->count() > 0)
                        <div class="row">
                            @foreach($messages as $message)
                            <div class="col-md-12 mb-3">
                                <div class="card border {{ $message->read_at ? 'bg-light' : 'border-primary' }}">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center mb-2">
                                                    <h6 class="card-title mb-0 me-2">{{ $message->subject }}</h6>
                                                    @if(!$message->read_at)
                                                        <span class="badge bg-danger">{{ __('messages.new') }}</span>
                                                    @endif
                                                    @if($message->is_broadcast)
                                                        <span class="badge bg-info ms-2">{{ __('messages.broadcast') }}</span>
                                                    @endif
                                                </div>
                                                <p class="card-text text-muted mb-2">
                                                    {{ __('messages.from') }}: {{ $message->sender->name }}
                                                    @if($message->tenant)
                                                        | {{ __('messages.to') }}: {{ $message->tenant->name }}
                                                    @else
                                                        | {{ __('messages.to') }}: {{ __('messages.all_tenants') }}
                                                    @endif
                                                </p>
                                                <p class="card-text">
                                                    {{ Str::limit($message->body, 150) }}
                                                </p>
                                                @if($message->hasAttachment())
                                                <div class="mb-2">
                                                    <i class="fas fa-paperclip text-muted me-1"></i>
                                                    <small class="text-muted">
                                                        {{ __('messages.attachment') }}: {{ $message->attachment_name }}
                                                        ({{ $message->getFormattedFileSize() }})
                                                    </small>
                                                </div>
                                                @endif
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted d-block">
                                                    {{ $message->sent_at ? $message->sent_at->format('M d, Y H:i') : 'N/A' }}
                                                </small>
                                                <a href="{{ route('messages.show', $message) }}" class="btn btn-sm btn-outline-primary mt-2">
                                                    {{ __('messages.view_details') }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $messages->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-envelope-open fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('messages.no_messages') }}</h5>
                            <p class="text-muted">{{ __('messages.you_dont_have_any_messages_yet') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
