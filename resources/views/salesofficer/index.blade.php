@extends('layouts.officer')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-body">
                    @livewire('officer-sales-index')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
