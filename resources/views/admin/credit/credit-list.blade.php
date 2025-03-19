@extends('layouts.contentLayoutMaster')
@section('title',__('web/messages.credit_list'))
@section('content')
    @include('panels.loading')

    <section class="mb-2">
        <livewire:tables.credit-table/>
    </section>
@endsection

