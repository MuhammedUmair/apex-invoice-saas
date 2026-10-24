@extends('layouts.app')

@section('content')
    <livewire:invoice-builder :invoice-id="$invoice->id" />
@endsection
