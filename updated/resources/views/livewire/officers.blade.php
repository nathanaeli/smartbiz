
<div class="p-3">

    {{-- ---------------- OFFICERS LIST ---------------- --}}
    <div class="card shadow-sm mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Officers ({{ $officers->total() }})</h5>

            <button class="btn btn-primary btn-sm" wire:click="$set('showAddModal', true)">
                <i class="ri-add-circle-line"></i> Add Officer
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Date Registered</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($officers as $officer)
                        <tr>
                            <td class="fw-bold">{{ $officer->name }}</td>
                            <td>{{ $officer->email }}</td>
                            <td>{{ $officer->phone ?? 'N/A' }}</td>
                            <td>{{ $officer->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">
                                No officers registered yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($officers->hasPages())
            <div class="border-top p-2">
                {{ $officers->links() }}
            </div>
        @endif
    </div>


    {{-- ---------------- ADD OFFICER MODAL ---------------- --}}
    @if($showAddModal)
        <div class="modal fade show d-block" tabindex="-1"
             style="background: rgba(0, 0, 0, 0.45);">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Register Officer</h5>
                        <button type="button" class="btn-close"
                            wire:click="$set('showAddModal', false)"></button>
                    </div>

                    <form wire:submit.prevent="saveOfficer">
                        <div class="modal-body">

                            <div class="mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" class="form-control" wire:model="name">
                                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" wire:model="email">
                                @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" wire:model="phone">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password *</label>
                                <input type="password" class="form-control" wire:model="password">
                                @error('password') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary"
                                wire:click="$set('showAddModal', false)">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Officer</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

</div>

