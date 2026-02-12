@extends('layouts.app')

@section('title', $duka->name)
@section('content')
    @livewire('show-duka', ['duka' => $duka])
@endsection