@extends('layouts.app')

@section('content')
    <div class="container-fluid card px-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">{{ __('messages.all_dukas') }}</h3>
            <a href="{{ route('duka.create.plan') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>{{ __('messages.add_new_duka') }}
            </a>
        </div>

        @if ($dukas->isEmpty())
            <div class="alert alert-info">
                {{ __('messages.you_have_not_registered_any_duka_yet') }}
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('messages.duka_name') }}</th>
                                <th>{{ __('messages.location') }}</th>
                                <th>{{ __('messages.manager') }}</th>
                                <th>{{ __('messages.active_plan') }}</th>
                                <th>{{ __('messages.ends_on') }}</th>
                                <th>{{ __('messages.products') }}</th>
                                <th>{{ __('messages.stocks') }}</th>
                                <th>{{ __('messages.categories') }}</th>
                                <th class="text-end">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($dukas as $index => $duka)
                                <tr>
                                    <td>{{ $index + 1 }}</td>

                                    <td class="fw-semibold">{{ $duka->name }}</td>

                                    <td>{{ $duka->location }}</td>

                                    <td>{{ $duka->manager_name }}</td>

                                    <!-- Active Plan -->
                                    <td>
                                        @if ($duka->activeSubscription && $duka->activeSubscription->plan)
                                            <span class="badge bg-success">
                                                {{ $duka->activeSubscription->plan->name }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('messages.none') }}</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if ($duka->activeSubscription)
                                            {{ $duka->activeSubscription->end_date->format('d M Y') }}
                                        @else
                                            —
                                        @endif
                                    </td>

                                    <!-- Product Count -->
                                    <td>{{ $duka->products_with_stock_count ?? $duka->products->count() }}</td>

                                    <!-- Stock Count -->
                                    <td>{{ $duka->stocks->count() }}</td>

                                    <!-- Category Count -->
                                    <td>{{ $duka->productCategories->count() }}</td>

                                    <td class="text-end">
                                        <a href="{{ route('duka.show', Crypt::encrypt($duka->id)) }}"
                                            class="btn btn-primary btn-sm px-3 me-1">
                                            {{ __('messages.view') }}
                                        </a>
                                        <a href="{{ route('duka.change.plan', Crypt::encrypt($duka->id)) }}"
                                            class="btn btn-outline-warning btn-sm px-3">
                                            {{ __('messages.change_plan') }}
                                        </a>
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
