@extends('layouts.contentLayoutMaster')
@section('title', __('web/notification.notification'))
@section('content')

<section id="description" class="card">
    <div class="card-header">
        <h4 class="card-title">{{__('web/notification.notification')}}</h4>
    </div>
    <div class="card-content">
        <div class="card-body">
            <div class="card-text">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <td>{{__('web/notification.message')}}</td>
                        <td>{{__('web/notification.time')}}</td>
                    </tr>
                    </thead>
                    @foreach(\App\Notification::where("user_id",auth()->id())->orderBy("created_at","desc")->get() as $notification)
                        @php
                            $text = $notification->full_text;
                            $text = str_replace("\n","<br>",$text);
                        @endphp
                        <tr>
                            <td>
                                <p><span class="@if($notification->is_read != '1') font-weight-bold @endif">{{$notification->title}} @if($notification->is_read != '1') ({{__('web/notification.unread')}}) @endif</span></p>
                                <p>{!! $text !!}</p>
                                <a class="openPage btn btn-primary white" data-src="{{$notification->link}}?wb=1">{{__('web/notification.details')}}</a>
                            </td>
                            <td>{{$notification->created_at->ago()}}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
