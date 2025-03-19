@extends('layouts.contentLayoutMaster')
@section('title', __('web/clinic.panel_clinics'))
@section('content')
    <div class="clearfix"></div>
    <div class="row match-height">
        <div class="col-md-6 col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{__('web/clinic.panel_clinics')}}</h4>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <form class="form form-horizontal" action="{{route('userpanel.clinic.create')}}" method="post">
                            @csrf
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group row">
                                            <div class="col-md-4">
                                                <span>Email</span>
                                            </div>
                                            <div class="col-md-8">
                                                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" placeholder="E-mail Address" value="{{old('email')}}">
                                                @error('email')
                                                <div class="invalid-feedback">
                                                    {{$message}}
                                                </div>
                                                @enderror
                                            </div>

                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group row">
                                            <div class="col-md-4">
                                                <span>NRIC</span>
                                            </div>
                                            <div class="col-md-8">
                                                <input type="text" class="form-control @error('nric') is-invalid @enderror" name="nric" placeholder="NRIC" value="{{old('nric')}}">
                                                @error('nric')
                                                <div class="invalid-feedback">
                                                    {{$message}}
                                                </div>
                                                @enderror
                                            </div>

                                        </div>
                                    </div>
                                    <div class="col-md-8 offset-md-4">
                                        <button type="submit" class="btn btn-primary mr-1 mb-1 waves-effect waves-light">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{__('web/clinic.existing_uw')}}</h4>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <form class="form form-horizontal">
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="list-group">

                                            @foreach($uws as $uw)
                                                <a href="{{route('userpanel.clinic.fill',$uw->uuid)}}" class="list-group-item list-group-item-action ">
                                                    <div class="d-flex w-100 justify-content-between">
                                                        <h5 class="mb-1">{{$uw->individual->name}}</h5>
                                                        <small>{{$uw->created_at->diffForHumans()}}</small>
                                                    </div>
                                                    <p class="mb-1">
                                                        NRIC  : {{$uw->individual->nric}}
                                                    </p>
                                                    <p>
                                                        Email : {{$uw->individual->user->email}}
                                                    </p>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('mystyle')


@endsection
@section('myscript')

@endsection
