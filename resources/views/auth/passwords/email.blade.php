@extends('layouts/fullLayoutMaster')
@section('title', 'Forgot Password')
@section('pageStyle')
    <link rel="stylesheet" href="{{ asset('css/pages/authentication.css') }}">
@endsection
@section('pageScript')
    <script>
        $("form").on("submit",function (e) {
            username = $("input[name=email]");
            Validation.clearAllValidation();
            if(Validation.empty(username)){
                Validation.setInvalid(username,"{{__('web/auth.required')}}");
                return false;
            }
            return  true;
        })
    </script>
@endsection
@section('content')

    <section class="row flexbox-container">
        <div class="col-xl-7 col-md-9 col-10 d-flex justify-content-center px-0">
            <div class="card bg-authentication rounded-0 mb-0">
                <div class="row m-0">
                    <div class="col-lg-6 d-lg-block d-none text-center align-self-center">
                        <img src="{{asset('images/forgot.svg')}}" style="width: 200px" alt="DearTime">
                    </div>
                    <div class="col-lg-6 col-12 p-0">
                        <div class="card rounded-0 mb-0 px-2 py-1">
                            <div class="card-header pb-1">
                                <div class="card-title">
                                    <h4 class="mb-0">Recover your password</h4>
                                </div>
                            </div>
                            <p class="px-2 mb-0">Please enter your email address and we'll send you instructions on how to reset your password.</p>
                            <div class="card-content">
                                <div class="card-body">
                                    @if (session('status'))
                                        <div class="alert alert-success" role="alert">
                                            {{ session('status') }}
                                        </div>
                                    @endif
                                    <form method="post" action="{{ route('password.email') }}">
                                        @csrf
                                        <fieldset class="form-label-group form-group position-relative has-icon-left">
                                            <input type="text" class="form-control @error('email') is-invalid @enderror" name="email" id="user-name" placeholder="Email Address">
                                            <label for="user-name">Email Address</label>
                                            <div class="form-control-position">
                                                <i class="feather icon-mail"></i>
                                            </div>
                                            @error('email')
                                            <div class="invalid-feedback">
                                                {{$message}}
                                            </div>
                                            @enderror

                                        </fieldset>

                                        <div class="float-md-left d-block mb-1">
                                            <a href="{{route('login')}}" class="btn btn-outline-primary btn-block px-75 waves-effect waves-light">Back to Login</a>
                                        </div>
                                        <div class="float-md-right d-block mb-1">
                                            <button type="submit" class="btn btn-primary btn-block px-75 waves-effect waves-light">Recover Password</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
