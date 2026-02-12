@extends('layouts.app')

@section('content')
<div class="container-fluid card px-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">{{ __('messages.all_dukas') }}</h3>
        <a href="{{ route('duka.create.plan') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>{{ __('messages.add_new_duka') }}
        </a>
    </div>

    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if ($dukas->isEmpty())
    <div class="alert alert-info">
        {{ __('messages.you_have_not_registered_any_duka_yet') }}
    </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">

            <div class="table-responsive">
                <table id="basic-table" class="table table-striped mb-0" role="grid">
                    <thead>
                        <tr>
                            <th>{{ __('messages.duka_name') }}</th>
                            <th>{{ __('messages.manager') }}</th>
                            <th>{{ __('messages.active_plan') }}</th>
                            <th>{{ __('messages.status') }}</th>

                            <th>{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dukas as $duka)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded bg-soft-primary d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <span class="fw-bold text-primary">{{ substr($duka->name, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $duka->name }}</h6>
                                        <small class="text-muted">{{ $duka->location }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="iq-media-group iq-media-group-1">
                                    <a href="#" class="iq-media-1">
                                        <div class="icon iq-icon-box-3 rounded-pill">
                                            {{ substr($duka->manager_name ?? 'MG', 0, 2) }}
                                        </div>
                                    </a>
                                </div>
                            </td>
                            <td>
                                @if ($duka->activeSubscription && $duka->activeSubscription->plan)
                                <h6 class="mb-0">{{ $duka->activeSubscription->plan->name }}</h6>
                                @else
                                <span class="text-muted">{{ __('messages.none') }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($duka->status === 'active')
                                <div class="text-success">{{ __('messages.active') }}</div>
                                @else
                                <div class="text-warning">{{ __('messages.inactive') }}</div>
                                @endif
                            </td>

                            <td>
                                <div class="d-flex align-items-center">
                                    <a href="{{ route('duka.show', ['id' => Crypt::encrypt($duka->id)]) }}" class="btn btn-soft-primary btn-sm me-2">
                                        {{ __('messages.view') }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>
@endsection