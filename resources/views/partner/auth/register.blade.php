@extends('layouts/fullLayoutMaster')

@section('title', 'Register')

@section('pageStyle')

    <link rel="stylesheet" href="{{ asset('css/pages/authentication.css') }}">

@endsection
@section('pageScript')
    <script>
        var mobileVerified = false;
        var verificationHashTime = 0;
        var verificationHash = '';

        var emailVerified = false;
        var emailVerificationHash = '';
        var emailVerificationHashTime = 0;



        $(document).ready(function () {


            var token = $("input[name=mobile_token]").val();
            var email_token = $("input[name=email_token]").val();
            var type = $("select[name=type]").val();
            var mobile = $("input[name=mobile]").val();
            var name = $("input[name=name]").val();

            if(token.length > 0 && type != 'corporate' && !Validation.is($("input[name=mobile]"),'invalid')){
                $("input[name=mobile]").prop("readonly",true);
                mobileVerified = true;
                verificationHash = token;
                Validation.setValid("input[name=mobile]");
            }else{
                $("input[name='mobile']").inputmask("099-99999999",{placeholder:" ", clearMaskOnLostFocus: true });
                $("input[name='mobile']").val('');
                mobileVerified = false;
                verificationHashTime = 0;
                verificationHash = '';
            }
            if(email_token.length > 0 && !Validation.is($("input[name=email]"),'invalid')){
                $("input[name=email]").prop("readonly",true);
                emailVerified = true;
                emailVerificationHash = token;
                Validation.setValid("input[name=email]");
            }else{
                $("input[name='email']").val('');
                emailVerified = false;
                emailVerificationHashTime = 0;
                emailVerificationHash = '';
            }

            @if(true)
            $("[data-value=corporate]").click();
            $("select[name=type]").change();
            $("input[name=mobile]").val(mobile);
            $("input[name='mobile_number']").val(mobile);
            $("input[name=name]").val(name);
            @endif

            @if(!empty($email))
                @if(!empty($token))
                    $("input[name=email]").prop("readonly",true);
                    emailVerified = true;
                    emailVerificationHash = '{{$token}}';
                    emailVerificationHashTime = new Date().getTime();
                    $("input[name=email_token]").val('{{$token}}');
                    Validation.setValid("input[name=email]");

            @endif
            $("input[name=email]").val('{{$email}}').keyup();
            @endif
            @if(!empty($name))
            $("input[name=name]").val('{{$name}}').keyup();
            @endif

        });
        $("input[name=name]").on("keyup",function (e) {
            if($("select[name=type]").val() == 'corporate')
                return;


            if(!Validation.isValidFullName(this)){
                Validation.setInvalid($(this), '@lang('web/profile.full_name_error')');

            }else{
                Validation.clearValidation($(this));
            }
        });
        $("input[name='verification-code']").inputmask("9 9 9 9 9");
        $("input[name='email-verification-code']").inputmask("9 9 9 9 9");
        $("select[name=type]").on("change",function(e){
            $("input[name='name']").prop("disabled",false).val('');
            $("input[name='mobile']").prop("disabled",false).val('');
            $("input[name='mobile_token']").val('');
            $("input[name='mobile_number']").val('');
            // Validation.clearValidation("input[name='mobile']");
            // Validation.clearValidation("input[name='name']");
            // mobileVerified = false;
            // verificationHash = '';
            // verificationHashTime = 0;


            if($(this).val() == 'individual'){
                $("div[data-value=individual]").addClass('selected');
                $("input[name='name']").attr('placeholder','@lang('web/auth.full_name')');
                $("input[name='mobile']").attr('placeholder','@lang('web/auth.mobile')');
                $(".rg_usr").removeClass("icon-users").addClass("icon-user");
                $(".rg_tel").removeClass("fa-building-o").removeClass('fa').addClass("icon-smartphone").addClass('feather');
                $("#name-label").html('@lang('web/auth.full_name')');
                $("#mobile-label").html('@lang('web/auth.mobile')');
                if(!mobileVerified)
                    $("input[name='mobile']").inputmask("099-99999999",{placeholder:" ", clearMaskOnLostFocus: true });
                var mobile = replaceAll($("input[name='mobile']").val()," ","");
                if(!mobileVerified && mobile.length > 10)
                    $(".verify-btn").show();
            }else{
                $("div[data-value=corporate]").addClass('selected');
                $("input[name='name']").attr('placeholder','@lang('web/auth.company_name')');
                $("input[name='mobile']").attr('placeholder','@lang('web/auth.company_reg_no')');
                $(".rg_usr").addClass("icon-users").removeClass("icon-user");
                $(".rg_tel").addClass("fa-building-o").addClass('fa').removeClass("icon-smartphone").removeClass('feather');
                $("#name-label").html('@lang('web/auth.company_name')');
                $("#mobile-label").html('@lang('web/auth.company_reg_no')');
                $("input[name='mobile']").inputmask('9999999-a',{placeholder:" ", clearMaskOnLostFocus: true });
                $(".verify-btn").hide();
            }
        })
        $("input[name='mobile']").on("keyup",function (e) {
            var type = $("select[name=type]").val();
            if(type == 'corporate'){
                $("input[name=mobile_number]").val(replaceAll(replaceAll($(this).val()," ",""),"-",""));
            }
            if(mobileVerified)
                return;
            var value = replaceAll(replaceAll($(this).val()," ",""),"-","");
            if(type == "individual") {
                if (value.length < 10)
                    $(".verify-btn").hide();
                else
                    $(".verify-btn").show();
            }

        });
        $("input[name='email']").on("keyup",function (e) {
            if(emailVerified)
                return;

                if (!Validation.validateEmail($(this).val()))
                    $(".email-verify-btn").hide();
                else
                    $(".email-verify-btn").show();


        });
        $('#email-verification-modal').on('shown.bs.modal', function () {
            $("input[name=verification-code]").trigger('focus')
        });
        $('#verification-modal').on('shown.bs.modal', function () {
            $("input[name=verification-code]").trigger('focus')
        });

        $(".verify-btn").on("click",function (e) {
            if(mobileVerified)
                return;
            var mobile = replaceAll($("input[name='mobile']").val(),"_","");
            if(mobile.length < 11)
                return $(".verify-btn").hide();

            mobile = replaceAll(mobile,"-","");
            if((parseInt(verificationHashTime + 60000) > parseInt(new Date().getTime()))){

                $("#verification-modal").modal('show');



            }else {
                $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>').prop("disabled",true);
                Validation.clearValidation($("#user-mobile"));
                $.post("{{route('api.verification.send')}}",{mobile_no : '+60'+mobile},function(res){
                    if(res.status == 'error'){
                        Validation.setInvalid($("#user-mobile"),res.message);
                    }else {
                        $(".verify-btn").html('@lang('web/auth.verify')').prop("disabled", false);
                        verificationHashTime = new Date().getTime();
                        verificationHash = res.data.id;
                        $("#verification-modal").modal('show');
                        $("input[name=verification-code]").focus();
                    }
                });

            }

        });
        $(".email-verify-btn").on("click",function (e) {
            if(emailVerified)
                return;
            var email =$("input[name='email']").val();
            if(!Validation.validateEmail(email))
                return $(".email-verify-btn").hide();

            if((parseInt(emailVerificationHashTime + 60000) > parseInt(new Date().getTime()))){

                $("#email-verification-modal").modal('show');



            }else {
                $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>').prop("disabled",true);
                $.post("{{route('api.verification.send')}}",{email : email,type:'email'},function(res){
                    $(".email-verify-btn").html('@lang('web/auth.verify')').prop("disabled",false);
                    if(res.status == 'error'){
                        Validation.setInvalid($("input[name=email]"),res.message);
                        return true;
                    }
                    emailVerificationHashTime = new Date().getTime();
                    emailVerificationHash = res.data.id;
                    $("#email-verification-modal").modal('show');
                    $("input[name=email-verification-code]").val('').focus();
                });

            }

        })
        $("#verify-phone-form").on("submit",function(e){
            Validation.clearValidation("input[name=verification-code]");

            if(verificationHash == '') {
                Validation.setInvalid("input[name=verification-code]", "@lang('web/auth.verification_invalid')");
                return false;
            }

            var code = $("input[name=verification-code]").val();
            code = replaceAll(code," ","");
            code = replaceAll(code,"_","");

            if(code.length < 5) {
                Validation.setInvalid("input[name=verification-code]", "@lang('web/auth.verification_invalid')");
                return  false;
            }


            $(".verify-phone").html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>').prop("disabled",true);
            $.post("{{route('api.verification.validate')}}",{mobile_id : verificationHash,mobile_code : code},function(res){
                if(res.status == 'error') {
                    $(".verify-btn").html('@lang('web/auth.verify')').prop("disabled",false);
                    Validation.setInvalid("input[name=verification-code]", res.message);
                    $(".verify-phone").html('@lang('web/auth.verify')').prop("disabled",false);
                    $("input[name=verification-code]").val('');
                    return false;
                }

                $(".verify-btn").hide();
                Validation.setValid("input[name=mobile]");
                $("input[name=mobile]").prop("readonly",true);
                $("input[name=mobile_token]").val(res.data.token);

                $("input[name=mobile_number]").val('+60'+ replaceAll(replaceAll($("input[name=mobile]").val()," ",""),"-",""));

                $(".verify-phone").html('@lang('web/auth.verify')').prop("disabled",false);
                $("input[name=verification-code]").val('');
                $("#verification-modal").modal('hide');
                mobileVerified = true;
            });
            return false;
        });
        $("#verify-email-form").on("submit",function(e){
            Validation.clearValidation("input[name=email-verification-code]");

            if(emailVerificationHash == '') {
                Validation.setInvalid("input[name=email-verification-code]", "@lang('web/auth.verification_invalid')");
                return false;
            }

            var code = $("input[name=email-verification-code]").val();
            code = replaceAll(code," ","");
            code = replaceAll(code,"_","");

            if(code.length < 5) {
                Validation.setInvalid("input[name=email-verification-code]", "@lang('web/auth.verification_invalid')");
                return  false;
            }


            $(".verify-email").html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>').prop("disabled",true);
            $.post("{{route('api.verification.validate')}}",{mobile_id : emailVerificationHash,mobile_code : code},function(res){
                if(res.status == 'error') {
                    $(".email-verify-btn").html('@lang('web/auth.verify')').prop("disabled",false);
                    Validation.setInvalid("input[name=email-verification-code]", res.message);
                    $(".verify-email").html('@lang('web/auth.verify')').prop("disabled",false);
                    $("input[name=email-verification-code]").val('');
                    return false;
                }

                $(".email-verify-btn").hide();
                Validation.setValid("input[name=email]");
                $("input[name=email]").prop("readonly",true);
                $("input[name=email_token]").val(res.data.token);

                $(".verify-email").html('@lang('web/auth.verify')').prop("disabled",false);
                $("input[name=email-verification-code]").val('');
                $("#email-verification-modal").modal('hide');
                emailVerified = true;
            });
            return false;
        });
        $("#registerForm").on("submit",function(e){
            var type = $("select[name=type]");
            var name = $("input[name=name]");
            var mobile = $("input[name=mobile]");
            var mobile_token = $("input[name=mobile_token]");
            var email = $("input[name=email]");
            var password = $("input[name=password]");
            Validation.clearAllValidation();


            if(name.val() == '') {
                Validation.setInvalid(name, '@lang('web/auth.required')');
                return false;
            }
            if(!Validation.isValidFullName(this)){
                Validation.setInvalid(name, '@lang('web/profile.full_name_error')');
                return false;
            }

            if(mobile.val() == '') {
                Validation.setInvalid(mobile, '@lang('web/auth.required')');
                return false;
            }

            if(mobile_token.val() == '' && type.val() == 'individual') {
                Validation.setInvalid(mobile, '@lang('web/auth.verification_required')');
                return false;
            }



            if(mobile_token.val() != '')
                Validation.setValid(mobile);

            if(email.val() == '') {
                Validation.setInvalid(email, '@lang('web/auth.required')');
                return false;
            }
            if(!Validation.validateEmail(email.val())){
                Validation.setInvalid(email, '@lang('web/auth.email_invalid')');
                return false;
            }

            if(password.val() == '' || password.val().length < 8) {
                Validation.setInvalid(password, '@lang('web/auth.password_gt_error')');
                return  false;
            }

            // var format = /[ `!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~]/;

            // alert(password.val());
            // if(format.test(password.val()) == false) {
            //     Validation.setInvalid(password, '@lang('web/auth.password_strength_notmached')');
            //     return  false;
            // }

            
            // return false;

            $(".register-btn").html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>').prop("disabled",true);
            return  true;


        });
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
        $(".type-selector").on("click",function(e){
            $(".type-selector,.selected").removeClass('selected');
            $(this).addClass('selected');
            if($(this).data('value') != $("select[name=type]").val())
                $("select[name=type]").val($(this).data('value')).change();
        })
        $(".changeLng a").on("click",function () {
            $(".loading").show();
        })
    </script>
@endsection
@section('content')
    @include('panels.loading')
    <section class="row flexbox-container">
        <div class="col-xl-8 col-11 d-flex justify-content-center">
            <div class="card bg-authentication rounded-0 mb-0">
                <div class="row m-0">
                    <div class="col-lg-6 d-lg-block d-none text-center align-self-center px-1 py-0">
                        <img src="{{asset('images/register.svg')}}" style="width: 300px" alt="DearTime">
                    </div>
                    <div class="col-lg-6 col-12 p-0">
                        <div class="card rounded-0 mb-0 px-2">
                            <div class="card-header pb-1">
                                <div class="card-title">

                                </div>
                            </div>
                            <h4 class="mb-0 text-center font-weight-bold">@lang('web/auth.register')</h4>
                            <br>
                            <p class="px-2 text-center">@lang('web/lang.select_lang')</p>
                            <div class="justify-content-center align-items-center d-flex">
                                <div class="mt-0 mb-1 w-50 d-flex justify-content-around align-items-center">
                                    <span class="@if(app()->getLocale() == 'en')  font-weight-bold @endif changeLng"><a href="?set_locale=en">EN</a> </span>
                                    <span class="@if(app()->getLocale() == 'bm') font-weight-bold @endif changeLng"><a href="?set_locale=bm">BM</a> </span>
                                    <span class="@if(app()->getLocale() == 'ch') font-weight-bold @endif changeLng"><a href="?set_locale=ch">CH</a> </span>
                                </div>
                            </div>
                            <hr>
                            <div class="card-content">
                                <div class="card-body pt-1">
                                    <style>
                                        .type-selector{
                                            border: 1px solid transparent;
                                            border-radius: 20px;
                                            max-width: 160px;
                                            height: 175px;
                                        }
                                        .selected{
                                            box-shadow: 0px 5px 30px rgba(0, 0, 0, 0.07);
                                            border: 1px solid #ccc !important;
                                            transition : all 0.5s ease-in-out;
                                            color: #000;
                                        }

                                    </style>


                                    <form id="registerForm" action="{{route('partner.auth.register')}}" method="post">
                                        @csrf
                                        <div class="row mb-2 d-none">
                                            <div class="col-6 p-0 m-0">
                                                <div data-value="individual" class="type-selector selected  p-25 d-flex align-items-center justify-content-center flex-column"></div>
                                            </div>
                                            <div class="col-6 p-0">
                                                <div data-value="corporate" class="type-selector p-25 d-flex align-items-center justify-content-center flex-column"></div>
                                            </div>
                                        </div>
                                        <fieldset class="form-label-group form-group position-relative has-icon-left d-none">
                                            <select class="form-control @error('type') is-invalid @enderror" name="type">
                                                <option @if(old('type') == 'individual') selected @endif value="individual">{{__('web/auth.individual')}}</option>
                                                <option @if(old('type') == 'corporate') selected @endif value="corporate">{{__('web/auth.group')}}</option>
                                            </select>
                                            <div class="form-control-position">
                                                <i class="feather icon-users"></i>
                                            </div>
                                            @error('type')
                                            <div class="invalid-feedback">
                                                {{$message}}
                                            </div>
                                            @enderror

                                        </fieldset>
                                        <fieldset class="form-label-group form-group position-relative has-icon-left">
                                            <input value="{{old('name')}}" autocomplete="off" type="text" id="user-name" class="form-control @error('name') is-invalid @enderror" name="name" placeholder="@lang('web/auth.full_name')">
                                            <div class="form-control-position">
                                                <i class="feather icon-user rg_usr"></i>
                                            </div>
                                            <label for="user-name" id="name-label">@lang('web/auth.full_name')</label>
                                            @error('name')
                                            <div class="invalid-feedback">
                                                {{$message}}
                                            </div>
                                            @enderror

                                        </fieldset>
                                        <input type="hidden"  name="mobile_token" value="{{old('mobile_token')}}">
                                        <input type="hidden" name="email_token" value="{{old('email_token')}}">
                                        <input type="hidden" name="_email" value="{{old('_email')}}">
                                        <input type="hidden" name="mobile_number" value="{{old('mobile_number')}}">
                                        <style>
                                            .verify-btn ,.email-verify-btn{
                                                right: 0 !important;
                                                left: auto !important;
                                                width: 70px;
                                                background: #222930;
                                                color: #fff;
                                                border-radius: 0 5px  5px 0;
                                                top: 1px;
                                                display: none;
                                                cursor: pointer;
                                                height: calc(1.25em + 1.3rem);
                                                line-height:2.8rem;
                                            }
                                            .eye{
                                                right: 5px !important;
                                                left: auto !important;
                                                width: 70px;
                                                height: calc(1.25em + 1.3rem);
                                                line-height:2.7rem;
                                            }
                                        </style>
                                        <fieldset class="form-label-group form-group position-relative has-icon-left">
                                            <input autocomplete="off" value="{{old('mobile')}}" type="text" id="user-mobile" class="form-control @error('mobile') is-invalid @enderror" name="mobile" placeholder="@lang('web/auth.mobile')">

                                            <div class="form-control-position">
                                                <i class="feather icon-phone rg_tel"></i>
                                            </div>
                                            <div class="form-control-position verify-btn">
                                                <span>@lang('web/auth.verify')</span>
                                            </div>
                                            <label for="user-mobile" id="mobile-label">@lang('web/auth.mobile')</label>
                                            @error('mobile')
                                            <div class="invalid-feedback">
                                                {{$message}}
                                            </div>
                                            @enderror

                                        </fieldset>
                                        <fieldset class="form-label-group form-group position-relative has-icon-left">
                                            <input autocomplete="off" value="{{old('email')}}" type="text" id="user-email" class="form-control @error('email') is-invalid @enderror" name="email" placeholder="@lang('web/auth.email')">
                                            <div class="form-control-position">
                                                <i class="feather icon-user"></i>
                                            </div>
                                            <div class="form-control-position email-verify-btn">
                                                <span>@lang('web/auth.verify')</span>
                                            </div>
                                            <label for="user-email">@lang('web/auth.email')</label>
                                            @error('email')
                                                <div class="invalid-feedback">
                                                    {{$message}}
                                                </div>
                                            @enderror

                                        </fieldset>
                                        <fieldset class="form-label-group position-relative has-icon-left">
                                            <input autocomplete="off" type="password" class="form-control @error('password') is-invalid @enderror" name="password" id="user-password" placeholder="@lang('web/auth.password')">
                                            <div class="form-control-position">
                                                <i class="feather icon-lock"></i>
                                            </div>
                                            <div class="form-control-position eye justify-content-center align-items-center d-flex">
                                                <i class="feather icon-eye"></i>
                                            </div>
                                            <label for="user-password">@lang('web/auth.password')</label>
                                            @error('password')
                                            <div class="invalid-feedback">
                                                {{$message}}
                                            </div>
                                            @enderror
                                        </fieldset>
                                        <a href="{{route('login')}}" class="btn btn-outline-primary float-left btn-inline waves-effect waves-light">@lang('web/auth.login')</a>
                                        <button type="submit" class="btn btn-primary float-right btn-inline waves-effect waves-light register-btn">@lang('web/auth.register')</button>
                                    </form>
                                </div>
                            </div>
                            <div class="login-footer">
                                <div class="divider">
                                    <div class="divider-text">@lang('web/auth.or')</div>
                                </div>
                                <div class="footer-btn d-flex justify-content-center align-items-center">
                                    <a href="{{route('oauth.redirect','facebook')}}" class="btn btn-facebook waves-effect waves-light"><span class="fa fa-facebook"></span></a>
                                    {{--<a href="#" class="btn btn-twitter white waves-effect waves-light"><span class="fa fa-twitter"></span></a>--}}
                                    <a href="{{route('oauth.redirect','google')}}" class="btn btn-google waves-effect waves-light"><span class="fa fa-google"></span></a>
                                    {{--<a href="#" class="btn btn-github waves-effect waves-light"><span class="fa fa-github-alt"></span></a>--}}
                                </div>

                            </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade text-left" id="verification-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel160" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xs modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary white">
                        <h5 class="modal-title" id="myModalLabel160">@lang('web/auth.verification_code')</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="verify-phone-form">
                        <div class="modal-body">
                            <label class="text-center">@lang('web/auth.verification_code_desc')</label>

                            <div class="form-group m-1">
                                <input autocomplete="off" name="verification-code" type="text" placeholder="@lang('web/auth.verification_code')" class="form-control text-center">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary verify-phone">@lang('web/auth.verify')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal fade text-left" id="email-verification-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel160" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xs modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary white">
                        <h5 class="modal-title" id="myModalLabel160">@lang('web/auth.verification_code')</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="verify-email-form">
                        <div class="modal-body">
                            <label class="text-center">@lang('web/auth.verification_code_desc_email')</label>

                            <div class="form-group m-1">
                                <input autocomplete="off" name="email-verification-code" type="text" placeholder="@lang('web/auth.verification_code')" class="form-control text-center">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary verify-email">@lang('web/auth.verify')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
