<div class="position-relative">
    <div class="input-group search-input">
        <span class="input-group-text" id="search-input">
            <svg class="icon-18" width="18" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <circle cx="11.7669" cy="11.7666" r="8.98856" stroke="currentColor"
                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></circle>
                <path d="M18.0186 18.4851L21.5426 22" stroke="currentColor" stroke-width="1.5"
                    stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </span>
        <input type="search" wire:model.live.debounce.300ms="search" class="form-control"
               placeholder="Search users by name or email..."
               autocomplete="off">
        @if(!empty($search))
            <button wire:click="clearSearch" class="btn btn-outline-secondary">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                </svg>
            </button>
        @endif
    </div>

    <!-- Search Results Dropdown -->
    @if($showResults)
        @php
            $users = $this->users ?? collect();
        @endphp
        @if($users->count() > 0)
            <div class="position-absolute top-100 start-0 w-100 mt-1 bg-white border rounded shadow-lg z-index-1050"
                 style="max-height: 400px; overflow-y: auto; z-index: 1050;">
                <div class="p-2">
                    <small class="text-muted d-block mb-2">{{ $users->count() }} user(s) found</small>
                    @foreach($users as $user)
                        <div class="d-flex align-items-center p-2 border-bottom border-light user-result-item"
                             style="cursor: pointer; transition: background-color 0.2s;">
                            <!-- User Avatar -->
                            <div class="me-3">
                                @if($user->profile_picture)
                                    <img src="{{ asset('storage/profiles/' . $user->profile_picture) }}"
                                         alt="{{ $user->name }}"
                                         class="rounded-circle"
                                         style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 40px; height: 40px; font-weight: bold; font-size: 16px;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>

                            <!-- User Details -->
                            <div class="flex-grow-1">
                                <div class="fw-semibold text-dark">{{ $user->name }}</div>
                                <div class="small text-muted">{{ $user->email }}</div>
                                <div class="small">
                                    <span class="badge bg-{{ $user->role == 'superadmin' ? 'danger' : ($user->role == 'tenant' ? 'primary' : 'success') }} text-white">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                    @if($user->tenant)
                                        <span class="text-muted ms-1">{{ $user->tenant->name }}</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Action Button -->
                            <div class="ms-2">
                                <button class="btn btn-sm btn-outline-primary"
                                        onclick="window.location.href='{{ route('officers.show', $user->id) }}'">
                                    View Details
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="position-absolute top-100 start-0 w-100 mt-1 bg-white border rounded shadow-lg z-index-1050"
                 style="z-index: 1050;">
                <div class="p-3 text-center text-muted">
                    <svg width="48" height="48" fill="currentColor" class="text-muted mb-2" viewBox="0 0 24 24">
                        <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                    </svg>
                    <div>No users found for "{{ $search }}"</div>
                    <small class="text-muted">Try searching with a different term</small>
                </div>
            </div>
        @endif
    @endif

    <style>
    .user-result-item:hover {
        background-color: rgba(79, 70, 229, 0.05) !important;
    }

    .z-index-1050 {
        z-index: 1050 !important;
    }

    /* Custom scrollbar for search results */
    .position-absolute .border {
        scrollbar-width: thin;
        scrollbar-color: #dee2e6 transparent;
    }

    .position-absolute .border::-webkit-scrollbar {
        width: 6px;
    }

    .position-absolute .border::-webkit-scrollbar-track {
        background: transparent;
    }

    .position-absolute .border::-webkit-scrollbar-thumb {
        background: #dee2e6;
        border-radius: 3px;
    }

    .position-absolute .border::-webkit-scrollbar-thumb:hover {
        background: #adb5bd;
    }

    /* Hide search results when clicking outside */
    @media (max-width: 768px) {
        .position-absolute {
            max-width: calc(100vw - 2rem);
            left: 1rem !important;
            right: 1rem !important;
            width: auto !important;
        }
    }
    </style>

    <script>
    document.addEventListener('livewire:loaded', () => {
        // Close search results when clicking outside
        document.addEventListener('click', function(event) {
            const searchContainer = event.target.closest('.position-relative');
            if (!searchContainer) {
                // Find the Livewire component and clear search
                const userSearchComponent = event.target.closest('[wire\\:id]');
                if (userSearchComponent) {
                    const wireId = userSearchComponent.getAttribute('wire:id');
                    if (wireId) {
                        Livewire.find(wireId).call('clearSearch');
                    }
                }
            }
        });

        // Close search results on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                // Find all UserSearch components and clear search
                document.querySelectorAll('[wire\\:id]').forEach(el => {
                    const wireId = el.getAttribute('wire:id');
                    if (wireId && el.querySelector('[wire\\:model="search"]')) {
                        Livewire.find(wireId).call('clearSearch');
                    }
                });
            }
        });
    });
    </script>
</div>
