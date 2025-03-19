@extends('layouts.contentLayoutMaster')
@section('title', $title ?? '')
@section('content')
    <section>
        <div class="row ">
            <div class="col-12">
                <div class="card pb-1">
                    <div class="card-header">
                        <h4 class="card-title">{{$title ?? ''}}</h4>

                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            {!! $content  ?? '' !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
