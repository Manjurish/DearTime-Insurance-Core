@extends('layouts/fullLayoutMaster')

@section('title', 'Login')

@section('pageStyle')

    <link rel="stylesheet" href="{{ asset('css/pages/authentication.css') }}">

    <style>
        .eye{
            right: 5px !important;
            left: auto !important;
            width: 70px;
            height: calc(1.25em + 1.3rem);
            line-height:2.5rem;
        }
    </style>
@endsection
@section('pageScript')
<script>
    var show = false;
    $(".eye i").on("click",function(e){
        if(!show) {
            show = true;
            $("input[name=password]").prop("type", "text");
            $(".eye").find("i").removeClass("icon-eye").addClass("icon-eye-off");
        }else{
            show = false;
            $("input[name=password]").prop("type", "password");
            $(".eye").find("i").addClass("icon-eye").removeClass("icon-eye-off");
        }

    });
    $("#loginForm").on("submit",function(e){
        return validate();

    });
    function validate(){
        Validation.clearAllValidation();
        var email = $("input[name=username]");
        var password = $("input[name=password]");
        var domain = $("input[name=domain]");
        if(domain.val().length == 0){
            Validation.setInvalid(domain,"{{__('web/auth.required')}}");
            return false;
        }
        if(email.val().length == 0){
            Validation.setInvalid(email,"{{__('web/auth.required')}}");
            return false;
        }
        if(password.val().length == 0){
            Validation.setInvalid(password,"{{__('web/auth.required')}}");
            return false;
        }
        return true;
    }
</script>
@endsection

@section('content')

    <section class="row flexbox-container">
        <div class="col-xl-8 col-11 d-flex justify-content-center">
            <div class="card bg-authentication rounded-0 mb-0">
                <div class="row m-0">
                    <div class="col-lg-6 d-lg-block d-none text-center align-self-center px-1 py-0">
                        <img src="{{asset('images/login.svg')}}" style="width: 300px" alt="DearTime">
                    </div>
                    <div class="col-lg-6 col-12 p-0">
                        <div class="card rounded-0 mb-0 px-2">
                            <div class="card-header pb-1">
                                <div class="card-title">
                                </div>
                            </div>
                            <h4 class="mb-0 text-center font-weight-bold">@lang('web/auth.login')</h4>
                            <br>
                            <p class="px-2  text-center">@lang('web/lang.select_lang')</p>
                            <div class="justify-content-center align-items-center d-flex">
                                <div class="mt-0 mb-1 w-50 d-flex justify-content-around align-items-center">
                                    <span @if(app()->getLocale() == 'en') class="font-weight-bold" @endif><a class="langchg" href="?set_locale=en">EN</a> </span>
                                    <span @if(app()->getLocale() == 'bm') class="font-weight-bold" @endif><a class="langchg" href="?set_locale=bm">BM</a> </span>
                                    <span @if(app()->getLocale() == 'ch') class="font-weight-bold" @endif><a class="langchg" href="?set_locale=ch">CH</a> </span>
                                </div>
                            </div>
                            <hr>
                            <p class="px-2">@lang('web/auth.welcome_back')</p>
                            <div class="card-content">
                                <div class="card-body pt-1">
                                    <form action="{{route('partner.auth.login')}}" method="post" id="loginForm">
                                        @csrf

                                        <fieldset class="form-label-group form-group position-relative has-icon-left">
                                            <input type="text" class="form-control @error('domain') is-invalid @enderror" name="domain" id="domain" placeholder="@lang('web/auth.domain')">
                                            <div class="form-control-position">
                                                <i class="feather icon-user"></i>
                                            </div>
                                            <label for="user-name">@lang('web/auth.domain')</label>
                                            @error('domain')
                                            <div class="invalid-feedback">
                                                {{$message}}
                                            </div>
                                            @enderror

                                        </fieldset>

                                        <fieldset class="form-label-group form-group position-relative has-icon-left">
                                            <input type="text" class="form-control @error('username') is-invalid @enderror" name="username" id="user-name" placeholder="@lang('web/auth.username')">
                                            <div class="form-control-position">
                                                <i class="feather icon-user"></i>
                                            </div>
                                            <label for="user-name">@lang('web/auth.username')</label>
                                            @error('username')
                                                <div class="invalid-feedback">
                                                    {{$message}}
                                                </div>
                                            @enderror

                                        </fieldset>


                                        <fieldset class="form-label-group position-relative has-icon-left">
                                            <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" id="user-password" placeholder="@lang('web/auth.password')">
                                            <div class="form-control-position">
                                                <i class="feather icon-lock"></i>
                                            </div>
                                            <div class="form-control-position eye d-flex justify-content-center align-items-center">
                                                <i class="feather icon-eye"></i>
                                            </div>
                                            <label for="user-password">@lang('web/auth.password')</label>
                                            @error('password')
                                            <div class="invalid-feedback">
                                                {{$message}}
                                            </div>
                                            @enderror
                                        </fieldset>
                                        <div class="form-group d-flex justify-content-between align-items-center">
                                            <div class="text-left">
                                                <fieldset class="checkbox">
                                                    <div class="vs-checkbox-con vs-checkbox-primary">
                                                        <input type="checkbox">
                                                        <span class="vs-checkbox">
                                                                        <span class="vs-checkbox--check">
                                                                            <i class="vs-icon feather icon-check"></i>
                                                                        </span>
                                                                    </span>
                                                        <span class="">@lang('web/auth.remember')</span>
                                                    </div>
                                                </fieldset>
                                            </div>
                                            <div class="text-right"><a href="{{route('password.request')}}" class="card-link">@lang('web/auth.forgot')</a></div>
                                        </div>
                                        <a href="{{route('partner.auth.register')}}" class="btn btn-outline-primary float-left btn-inline waves-effect waves-light">@lang('web/auth.register')</a>
                                        <button type="submit" class="btn btn-primary float-right btn-inline waves-effect waves-light">@lang('web/auth.login')</button>
                                    </form>
                                </div>
                            </div>
                            <div class="login-footer">
                                <div class="divider">
                                    <div class="divider-text">@lang('web/auth.or')</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
