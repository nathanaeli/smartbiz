@extends('layouts.super-admin')

@section('title', 'Contact Messages')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title">Contact Messages</h4>
                        <p class="card-title-desc">Manage contact form submissions from the website.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="statusFilter" data-bs-toggle="dropdown">
                                <i class="mdi mdi-filter"></i> Filter Status
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['status' => 'all']) }}">All Messages</a></li>
                                <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['status' => 'unread']) }}">Unread Only</a></li>
                                <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['status' => 'read']) }}">Read Only</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Search and Stats -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <form method="GET" class="d-flex gap-2">
                                <input type="text" name="search" class="form-control" placeholder="Search by name, email, or subject..."
                                       value="{{ request('search') }}">
                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-magnify"></i> Search
                                </button>
                                @if(request('search') || request('status'))
                                    <a href="{{ route('super-admin.contacts.index') }}" class="btn btn-outline-secondary">
                                        <i class="mdi mdi-refresh"></i> Clear
                                    </a>
                                @endif
                            </form>
                        </div>
                        <div class="col-md-4">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="card border">
                                        <div class="card-body p-2">
                                            <h6 class="text-warning mb-1">{{ $contacts->where('is_read', false)->count() }}</h6>
                                            <small class="text-muted">Unread</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card border">
                                        <div class="card-body p-2">
                                            <h6 class="text-success mb-1">{{ $contacts->where('is_read', true)->count() }}</h6>
                                            <small class="text-muted">Read</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bulk Actions -->
                    <form id="bulkActionForm" method="POST" action="{{ route('super-admin.contacts.bulk-action') }}">
                        @csrf
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex gap-2">
                                <select name="bulk_action" class="form-select form-select-sm" style="width: auto;">
                                    <option value="">Bulk Actions</option>
                                    <option value="mark_read">Mark as Read</option>
                                    <option value="mark_unread">Mark as Unread</option>
                                    <option value="delete">Delete</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-outline-primary" id="applyBulkAction" disabled>
                                    Apply
                                </button>
                            </div>
                            <small class="text-muted">{{ $contacts->total() }} total messages</small>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-centered table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40">
                                            <input type="checkbox" class="form-check-input" id="selectAll">
                                        </th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Subject</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($contacts as $contact)
                                    <tr class="{{ !$contact->is_read ? 'table-warning' : '' }}">
                                        <td>
                                            <input type="checkbox" class="form-check-input contact-checkbox"
                                                   name="contact_ids[]" value="{{ $contact->id }}">
                                        </td>
                                        <td>{{ $contact->name }}</td>
                                        <td>
                                            <a href="mailto:{{ $contact->email }}" class="text-decoration-none">
                                                {{ $contact->email }}
                                            </a>
                                        </td>
                                        <td>{{ Str::limit($contact->subject, 50) }}</td>
                                        <td>
                                            <span title="{{ $contact->created_at->format('M d, Y H:i') }}">
                                                {{ $contact->created_at->diffForHumans() }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($contact->is_read)
                                                <span class="badge bg-success">Read</span>
                                            @else
                                                <span class="badge bg-warning">Unread</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('super-admin.contacts.show', $contact) }}"
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="mdi mdi-eye"></i> View
                                                </a>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                            data-bs-toggle="dropdown">
                                                        <i class="mdi mdi-dots-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item" href="mailto:{{ $contact->email }}?subject=Re: {{ $contact->subject }}">
                                                                <i class="mdi mdi-email"></i> Reply via Email
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <button type="button" class="dropdown-item mark-read-btn"
                                                                    data-contact-id="{{ $contact->id }}"
                                                                    data-current-status="{{ $contact->is_read ? 'read' : 'unread' }}">
                                                                <i class="mdi mdi-check-circle"></i>
                                                                {{ $contact->is_read ? 'Mark as Unread' : 'Mark as Read' }}
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="mdi mdi-email-off-outline" style="font-size: 3rem; color: #ccc;"></i>
                                            <p class="mt-2 text-muted">No contact messages found.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </form>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $contacts->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all checkbox functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    const contactCheckboxes = document.querySelectorAll('.contact-checkbox');
    const bulkActionBtn = document.getElementById('applyBulkAction');
    const bulkActionSelect = document.querySelector('select[name="bulk_action"]');

    selectAllCheckbox.addEventListener('change', function() {
        contactCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActionButton();
    });

    contactCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const checkedBoxes = document.querySelectorAll('.contact-checkbox:checked');
            selectAllCheckbox.checked = checkedBoxes.length === contactCheckboxes.length;
            selectAllCheckbox.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < contactCheckboxes.length;
            updateBulkActionButton();
        });
    });

    bulkActionSelect.addEventListener('change', updateBulkActionButton);

    function updateBulkActionButton() {
        const checkedBoxes = document.querySelectorAll('.contact-checkbox:checked');
        const hasSelection = checkedBoxes.length > 0;
        const hasAction = bulkActionSelect.value !== '';

        bulkActionBtn.disabled = !(hasSelection && hasAction);

        if (hasSelection && hasAction) {
            bulkActionBtn.textContent = `Apply to ${checkedBoxes.length} selected`;
        } else {
            bulkActionBtn.textContent = 'Apply';
        }
    }

    // Handle mark as read/unread buttons
    const markReadButtons = document.querySelectorAll('.mark-read-btn');
    markReadButtons.forEach(button => {
        button.addEventListener('click', function() {
            const contactId = this.getAttribute('data-contact-id');
            const currentStatus = this.getAttribute('data-current-status');
            const newStatus = currentStatus === 'read' ? 'unread' : 'read';

            // Create form data for AJAX request
            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            formData.append('_method', 'PATCH');

            fetch(`/super-admin/contacts/${contactId}/mark-read`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the button text and status
                    this.setAttribute('data-current-status', newStatus);
                    this.innerHTML = `<i class="mdi mdi-check-circle"></i> ${newStatus === 'read' ? 'Mark as Unread' : 'Mark as Read'}`;

                    // Update the badge in the same row
                    const row = this.closest('tr');
                    const statusCell = row.querySelector('td:nth-child(6) .badge');
                    if (newStatus === 'read') {
                        statusCell.className = 'badge bg-success';
                        statusCell.textContent = 'Read';
                        row.classList.remove('table-warning');
                    } else {
                        statusCell.className = 'badge bg-warning';
                        statusCell.textContent = 'Unread';
                        row.classList.add('table-warning');
                    }

                    // Update unread count in sidebar if it exists
                    updateUnreadCount();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to update message status. Please try again.');
            });
        });
    });

    // Handle bulk actions
    const bulkForm = document.getElementById('bulkActionForm');
    bulkForm.addEventListener('submit', function(e) {
        const action = bulkActionSelect.value;
        const selectedContacts = document.querySelectorAll('.contact-checkbox:checked');

        if (selectedContacts.length === 0) {
            e.preventDefault();
            alert('Please select at least one contact message.');
            return;
        }

        if (!action) {
            e.preventDefault();
            alert('Please select a bulk action.');
            return;
        }

        if (action === 'delete' && !confirm(`Are you sure you want to delete ${selectedContacts.length} contact message(s)?`)) {
            e.preventDefault();
        }
    });

    // Function to update unread count in sidebar
    function updateUnreadCount() {
        // This would need to be implemented if you want live updates
        // For now, it can be refreshed on page reload
    }
});
</script>
@endsection
