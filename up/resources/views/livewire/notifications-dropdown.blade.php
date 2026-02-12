@if($unreadCount > 0)
    <span class="bg-danger dots">{{ $unreadCount }}</span>
@endif

<div class="p-0 sub-drop dropdown-menu dropdown-menu-end" aria-labelledby="notification-drop">
    <div class="m-0 shadow-none card">
        <div class="py-3 card-header d-flex justify-content-between bg-primary">
            <div class="header-title">
                <h5 class="mb-0 text-white">All Notifications</h5>
            </div>
        </div>
        <div class="p-0 card-body" style="max-height: 400px; overflow-y: auto;">
            @if(count($notifications) > 0)
                @foreach($notifications as $notification)
                    <a href="{{ $notification['url'] }}" class="iq-sub-card" wire:click="markAsRead('{{ $notification['id'] }}')">
                        <div class="d-flex align-items-center">
                            <div class="position-relative">
                                @if($notification['icon'] === 'warning')
                                    <div class="p-1 avatar-40 rounded-pill bg-soft-warning d-flex align-items-center justify-content-center">
                                        <i class="ri-alert-line text-warning"></i>
                                    </div>
                                @elseif($notification['icon'] === 'error')
                                    <div class="p-1 avatar-40 rounded-pill bg-soft-danger d-flex align-items-center justify-content-center">
                                        <i class="ri-error-warning-line text-danger"></i>
                                    </div>
                                @else
                                    <div class="p-1 avatar-40 rounded-pill bg-soft-info d-flex align-items-center justify-content-center">
                                        <i class="ri-information-line text-info"></i>
                                    </div>
                                @endif
                                @if(!$notification['read'])
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.5rem; padding: 0.1rem 0.3rem;">
                                        <span class="visually-hidden">unread</span>
                                    </span>
                                @endif
                            </div>
                            <div class="ms-3 w-100">
                                <h6 class="mb-0 {{ !$notification['read'] ? 'fw-bold' : '' }}">
                                    {{ $notification['title'] }}
                                    @if($notification['type'] === 'subscription_expired')
                                        <span class="badge bg-danger ms-1">Expired</span>
                                    @elseif(isset($notification['days_left']) && $notification['days_left'] <= 1)
                                        <span class="badge bg-danger ms-1">Urgent</span>
                                    @elseif(isset($notification['days_left']) && $notification['days_left'] <= 3)
                                        <span class="badge bg-warning ms-1">Soon</span>
                                    @endif
                                </h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <p class="mb-0 text-muted small">{{ $notification['message'] }}</p>
                                    <small class="text-muted">{{ $notification['time'] }}</small>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            @else
                <div class="text-center py-4">
                    <i class="ri-notification-off-line text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">No notifications</p>
                </div>
            @endif
        </div>
        @if(count($notifications) > 0)
            <div class="card-footer text-center">
                <button class="btn btn-link btn-sm text-primary" wire:click="$refresh">
                    <i class="ri-refresh-line me-1"></i>Refresh
                </button>
            </div>
        @endif
    </div>
</div>
