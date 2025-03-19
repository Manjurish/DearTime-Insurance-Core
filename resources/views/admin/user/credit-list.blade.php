@extends('layouts.contentLayoutMaster')
@section('title',__('web/messages.credit_list'))
@section('content')
    @include('panels.loading')

    <section class="mb-2">
        @if($promote)
            <livewire:credit.sum :user="$user" />
        @endif
        <livewire:tables.credit-table :user="$user" :promote="$promote"/>
    </section>
@endsection

