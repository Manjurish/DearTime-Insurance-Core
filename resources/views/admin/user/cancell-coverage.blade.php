@extends('layouts.contentLayoutMaster')
@section('title',__('web/messages.cancel_coverage'))
@section('content')
    @include('panels.loading')

    <livewire:coverage.cancell :profile="$profile"/>
@endsection
