@extends('layouts.app')

@section('title', $duka->name . ' - Inventory')

@section('content')
<div class="container-fluid py-4 card">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 pb-4 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2 small">
                    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('duka.show', $duka->id) }}" class="text-decoration-none">{{ $duka->name }}</a></li>
                    <li class="breadcrumb-item active">Inventory Catalog</li>
                </ol>
            </nav>
            <h1 class="h2 fw-bold text-dark mb-1">Product Inventory</h1>
            <p class="text-muted small mb-0">Manage stock, prices, and product details for {{ $duka->name }}.</p>
        </div>
        <div class="mt-3 mt-md-0 d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-outline-primary btn-sm rounded-2 dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download me-1" viewBox="0 0 16 16">
                        <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z" />
                        <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z" />
                    </svg>
                    Export
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="exportDropdown">
                    <li>
                        <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('duka.inventory.export.excel', $duka->id) }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-excel text-success me-2" viewBox="0 0 16 16">
                                <path d="M5.884 6.68a.5.5 0 1 0-.768.64L7.349 10l-2.233 2.68a.5.5 0 0 0 .768.64L8 10.781l2.116 2.54a.5.5 0 0 0 .768-.641L8.651 10l2.233-2.68a.5.5 0 0 0-.768-.64L8 9.219l-2.116-2.54z" />
                                <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z" />
                            </svg>
                            Export Excel
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('duka.inventory.export.pdf', $duka->id) }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-pdf text-danger me-2" viewBox="0 0 16 16">
                                <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z" />
                                <path d="M4.603 14.087a.81.81 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.68 7.68 0 0 1 1.482-.645 19.697 19.697 0 0 0 1.062-2.227 7.269 7.269 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .477.365c.088.164.12.356.127.538.007.188-.012.396-.047.614-.084.51-.27 1.134-.52 1.794a10.954 10.954 0 0 0 .98 1.686 5.753 5.753 0 0 1 1.334.05c.364.066.734.195.96.465.12.144.193.32.2.518.007.192-.047.382-.138.563a1.04 1.04 0 0 1-.354.416.856.856 0 0 1-.51.138c-.331-.014-.654-.196-.933-.417a5.712 5.712 0 0 1-.911-.95 11.651 11.651 0 0 0-1.997.406 11.307 11.307 0 0 1-1.02 1.51c-.292.35-.609.656-.927.787a.793.793 0 0 1-.58.029zm1.379-1.901c-.166.076-.32.156-.459.238-.328.194-.541.383-.647.545-.094.145-.096.25-.04.361.01.022.02.036.026.044a.426.426 0 0 0 .017-.055c.016-.053.035-.118.06-.184a6.914 6.914 0 0 0 .053-.25c.001-.006.001-.012.002-.018l.008-.065c.03-.186.07-.374.113-.531.026-.097.051-.186.08-.266.002-.007.005-.013.007-.02l.011-.029.006-.015.38.016zm3.322 1.5c.092.106.2.2.328.29.354.246.772.32 1.096.198.157-.06.223-.19.235-.296.009-.077-.023-.162-.084-.236a2.63 2.63 0 0 0-.256-.242c-.012-.01-.025-.019-.038-.028-.158-.112-.353-.213-.578-.291a13.34 13.34 0 0 1-.703.605z" />
                            </svg>
                            Export PDF
                        </a>
                    </li>
                </ul>
            </div>
            <a href="{{ route('duka.show', $duka->id) }}" class="btn btn-outline-secondary btn-sm rounded-2 px-3">
                <i class="ri-arrow-left-line me-1"></i> Back to Duka
            </a>
        </div>
    </div>

    @livewire('duka-products', ['dukaId' => $duka->id])
</div>
@endsection