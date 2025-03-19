@extends('layouts.contentLayoutMaster')
@section('title',__('web/messages.notification_list'))
@section('content')
    @include('panels.loading')

    <section class="mb-2">
        <livewire:tables.notification-table></livewire:tables.notification-table>
    </section>

@endsection

