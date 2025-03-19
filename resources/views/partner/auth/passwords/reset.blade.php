@extends('layouts/fullLayoutMaster')

@section('title', 'Reset Password')

@section('pageStyle')

    <link rel="stylesheet" href="{{ asset('css/pages/authentication.css') }}">

@endsection
@section('pageScript')
    <script>
        $("form").on("submit",function (e) {
            Validation.clearAllValidation();

            username = $("input[name=email]");
            if(Validation.empty(username)){
                Validation.setInvalid(username,"{{__('web/auth.required')}}");
                return false;
            }
            password = $("input[name=password]");
            if(Validation.empty(password) || password.val().length < 8){
                Validation.setInvalid(password,"{{__('web/auth.password_gt_error')}}");
                return false;
            }
            password_confirm = $("input[name=password_confirmation]");
            if(Validation.empty(password_confirm) || password.val().length < 8 || password.val() != password_confirm.val()){
                Validation.setInvalid(password_confirm,"{{__('web/auth.password_same_error')}}");
                return false;
            }
            return  true;
        })
    </script>
@endsection

@section('content')
    <section class="row flexbox-container">
        <div class="col-xl-7 col-10 d-flex justify-content-center">
            <div class="card bg-authentication rounded-0 mb-0 w-100">
                <div class="row m-0">
                    <div class="col-lg-6 d-lg-block d-none text-center align-self-center p-0">
                        <img src="{{asset('images/forgot.svg')}}" style="width: 200px" alt="DearTime">
                    </div>
                    <div class="col-lg-6 col-12 p-0">
                        <div class="card rounded-0 mb-0 px-2">
                            <div class="card-header pb-1">
                                <div class="card-title">
                                    <h4 class="mb-0">Reset Password</h4>
                                </div>
                            </div>
                            <p class="px-2">Please enter your new password.</p>
                            <div class="card-content">
                                <div class="card-body pt-1">
                                    <form method="POST" action="{{ route('password.update') }}">
                                        @csrf

                                        <input type="hidden" name="token" value="{{ $token }}">

                                        <fieldset class="form-label-group has-icon-left">
                                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" placeholder="Enter Your current E-mail" autocomplete="email" autofocus>
                                            <div class="form-control-position">
                                                <i class="feather icon-mail"></i>
                                            </div>
                                            @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </fieldset>

                                        <fieldset class="form-label-group has-icon-left">
                                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Enter Your New password" autocomplete="new-password">
                                            <div class="form-control-position">
                                                <i class="feather icon-lock"></i>
                                            </div>
                                            @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </fieldset>

                                        <fieldset class="form-label-group has-icon-left">
                                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="Confirm Your New password" autocomplete="new-password">
                                            <div class="form-control-position">
                                                <i class="feather icon-lock"></i>
                                            </div>
                                        </fieldset>
                                        <div class="row pt-2">
                                            <div class="col-12 col-md-6 mb-1">
                                                <a href="{{route('login')}}" class="btn btn-outline-primary btn-block px-0 waves-effect waves-light">Go Back to Login</a>
                                            </div>
                                            <div class="col-12 col-md-6 mb-1">
                                                <button type="submit" class="btn btn-primary btn-block px-0 waves-effect waves-light">Reset</button>
                                            </div>
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
