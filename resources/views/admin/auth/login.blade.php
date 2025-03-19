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
                                    <h4 class="mb-0"> Staff / Admin - Internal - Login </h4>
                                </div>
                            </div>
                            <p class="px-2">Welcome back, please login to your account.</p>
                            <div class="card-content">
                                <div class="card-body pt-1">
                                    <form action="{{route('admin.auth.login')}}" method="post">
                                        @csrf

                                        <fieldset class="form-label-group form-group position-relative has-icon-left">
                                            <input type="text" class="form-control @error('email') is-invalid @enderror" name="email" id="user-name" placeholder="Email" required="">
                                            <div class="form-control-position">
                                                <i class="feather icon-user"></i>
                                            </div>
                                            <label for="user-name">Email</label>
                                            @error('email')
                                                <div class="invalid-feedback">
                                                    {{$message}}
                                                </div>
                                            @enderror

                                        </fieldset>


                                        <fieldset class="form-label-group position-relative has-icon-left">
                                            <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" id="user-password" placeholder="Password" required="">
                                            <div class="form-control-position">
                                                <i class="feather icon-lock"></i>
                                            </div>
                                            <div class="form-control-position eye d-flex justify-content-center align-items-center">
                                                <i class="feather icon-eye"></i>
                                            </div>
                                            <label for="user-password">Password</label>
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
                                                        <span class="">Remember me</span>
                                                    </div>
                                                </fieldset>
                                            </div>
                                            <div class="text-right"><a href="{{route('admin.password.request')}}" class="card-link">Forgot Password?</a></div>
                                        </div>
                                        {{--<a href="auth-register.html" class="btn btn-outline-primary float-left btn-inline waves-effect waves-light">Register</a>--}}
                                        <button type="submit" class="btn btn-primary float-right btn-inline waves-effect waves-light">Login</button>
                                    </form>
                                </div>
                            </div>
                            <div class="login-footer">
                                <div class="divider">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
