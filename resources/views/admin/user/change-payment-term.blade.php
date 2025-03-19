@extends('layouts.contentLayoutMaster')
@section('title','Particular Change')
@section('content')
    @include('panels.loading')

    <livewire:change-payment-term :profile="$profile"/>

    <section class="mb-2">
        <h2>{{ __('web/messages.actions') }}</h2>
        <livewire:tables.actions-table type="{{ App\Helpers\Enum::ACTION_TABLE_TYPE_PAYMENT_TERM }}"/>
    </section>

    <br>

    <section class="mb-2">
        <h2>{{ __('web/messages.particular_change') }}</h2>
        <livewire:tables.particular-change-table type="{{ App\Helpers\Enum::ACTION_TABLE_TYPE_PAYMENT_TERM }}"/>
    </section>
@endsection
