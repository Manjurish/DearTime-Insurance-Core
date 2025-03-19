@extends('layouts.contentLayoutMaster')
@section('title',__('web/messages.refund_list'))
@section('content')
    @include('panels.loading')

    <section class="mb-2">
        <livewire:refunds.refunds></livewire:refunds.refunds>
    </section>

@endsection

