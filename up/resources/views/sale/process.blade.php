@extends('layouts.app')

@section('content')
@livewire('sale-process', ['dukaId' => $dukaId])
@endsection