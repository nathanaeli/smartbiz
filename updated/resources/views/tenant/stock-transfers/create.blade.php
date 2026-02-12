@extends('layouts.app')

@section('title', 'Stock Transfer')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            @livewire('stock-transfer-create')
        </div>
    </div>
</div>
@endsection
