@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Sale Process for Duka</h4>
                </div>
                <div class="card-body">
                    @livewire('sale-process', ['dukaId' => $dukaId])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
