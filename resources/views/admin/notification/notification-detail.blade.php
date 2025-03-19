@extends('layouts.contentLayoutMaster')
@section('title','Coverage Details')
@section('content')
    <section id="description" class="card">
        <div class="card-header">
            <h4 class="card-title"></h4>
        </div>
        <div class="card-content">
            <div class="card-body">
                <section id="nav-justified">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card overflow-hidden">
                                <div class="card-header">
                                    <h4 class="card-title">Notification Details</h4>
                                </div>
                                <div class="card-content">
                                    <div class="card-body">
                                        <p><b>UUID</b> : {{$notification->uuid}}</p>
                                        <p><b>Seen</b> : {{$notification->is_read==1?'Yes':'No'}}</p>
                                        <p><b>Receiver</b> : <a href="{{route('admin.User.show',$notification->user->uuid ?? '')}}">{{($notification->user->name ?? '')}}</a></p>
                                        <p><b>Title</b> : {{$notification->title}}</p>
                                        <p><b>Text</b> : {!! str_replace('\n','<br>',$notification->text) !!}</p>
                                        <p><b>Full Text</b> : {!! str_replace('\n','<br>',$notification->full_text) !!}</p>
                                        <p><b>Created At</b> : {{ \Carbon\Carbon::parse($notification->created_at)->format('d/m/Y H:i A') }}</p>
                                        <br>
                                        <br>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </section>
@endsection
