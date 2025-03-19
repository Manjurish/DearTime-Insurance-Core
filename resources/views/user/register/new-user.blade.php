@extends('layouts/fullLayoutMaster')

@section('title', 'Register')

@section('pageStyle')

    <link rel="stylesheet" href="{{ asset('css/pages/authentication.css') }}">

@endsection
@section('pageScript')
    <script>

        $(".openPage").on('click',function (e) {
            $(".loading").show();
            $.get($(this).data('src'),{},function(d){
                $(".loading").hide();
                $("#pageModal .modal-body").html(d);
                $("#pageModal").modal();
            });

        })
    </script>
    <script>
        var mobileVerified = false;
        var verificationHashTime = 0;
        var verificationHash = '';
        var emailVerified = false;
        var emailVerificationHash = '';
        var emailVerificationHashTime = 0;
        var _email = '';
        var _mobile = '';

        $(document).ready(function () {
            // This line always show corporate
            $(".type-selector[data-value=corporate]").click();
            @if(request()->input('type') == 'corporate')
            $(".type-selector[data-value=corporate]").click();
                @endif
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
                $("input[name='name']");
                $("input[name='name']").val('');
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

            @if(old('type') == 'corporate')
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
            Validation.clearValidation("input[name='mobile']");
            Validation.clearValidation("input[name='name']");
            mobileVerified = false;
            verificationHash = '';
            verificationHashTime = 0;


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

                mobile = replaceAll(mobile,"-","");

                if(!mobileVerified && mobile.length >= 10)
                    $(".verify-btn").show();
            }else{
                $("div[data-value=corporate]").addClass('selected');
                $("input[name='name']").attr('placeholder','@lang('web/auth.company_name')');
                $("input[name='mobile']").attr('placeholder','@lang('web/auth.company_reg_no')');
                $("input[name=mobile]").prop("readonly",false);
                $(".rg_usr").addClass("icon-users").removeClass("icon-user");
                $(".rg_tel").addClass("fa-building-o").addClass('fa').removeClass("icon-smartphone").removeClass('feather');
                $("#name-label").html('@lang('web/auth.company_name')');
                $("#mobile-label").html('@lang('web/auth.company_reg_no')');
                $("input[name='name']");
                $(".verify-btn").hide();
            }
        });
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

               // if (!Validation.validateEmail($(this).val()))
                    //$(".email-verify-btn").hide();
                //else
                   // $(".email-verify-btn").show();


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

            if((parseInt(verificationHashTime + 60000) > parseInt(new Date().getTime())) && _mobile == mobile){

                $("#verification-modal").modal('show');



            }else {
                $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>').prop("disabled",true);
                Validation.clearValidation($("#user-mobile"));
                $.post("{{route('api.verification.send')}}",{mobile : mobile},function(res){
                    _mobile = mobile;
                    $(".verify-btn").html('@lang('web/auth.verify')').prop("disabled", false);

                    if(res.status == 'error'){
                        Validation.setInvalid($("#user-mobile"),res.message);
                    }else {
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

            if((parseInt(emailVerificationHashTime + 60000) > parseInt(new Date().getTime())) && _email == email){

                $("#email-verification-modal").modal('show');



            }else {
                $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>').prop("disabled",true);
                $.post("{{route('api.verification.send')}}",{email : email,type:'email'},function(res){
                    _email = email;
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
            var accept = $("input[name=accept]");
            Validation.clearAllValidation();

            if(!accept.is(":checked")) {
                Validation.setInvalid(accept.parents('.vs-checkbox-con'), '@lang('web/auth.required')');
                return false;
            }

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
                            <div class="card-content pb-3">
                                <div class="card-body pt-1">
                                    <style>
                                        .vs-checkbox-con input{
                                            width: 8% !important;
                                        }
                                        .invalid-feedback{
                                            display: block;
                                        }
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
                                        .selected path {
                                            stroke : #000
                                        }

                                    </style>


                                    <form id="registerForm" action="{{route('userpanel.account.store-user')}}" method="post">
                                        @csrf
                                        <div class="row mb-2">
{{--                                            <div class="col-6 p-0 m-0">--}}
{{--                                                <div data-value="individual" class="type-selector selected  p-25 d-flex align-items-center justify-content-center flex-column">--}}
{{--                                                    <svg class="mt-75" width="49" height="57" viewBox="0 0 49 57" fill="none" xmlns="http://www.w3.org/2000/svg">--}}
{{--                                                        <path d="M34.2029 16.995C34.2029 16.995 35.4369 15.8053 36.4811 17.2834C38.3067 19.8522 34.0203 23.3404 34.0203 23.3404M13.3155 16.387C13.3155 16.387 11.7131 8.8024 14.3061 4.94696C17.4235 0.304894 24.5905 1.04296 24.5905 1.04296C24.5905 1.04296 32.0586 0.304894 35.176 4.94696C37.7689 8.8024 36.1665 16.387 36.1665 16.387M15.386 16.995C15.386 16.995 14.152 15.8053 13.1078 17.2834C11.2822 19.8522 15.5686 23.3404 15.5686 23.3404M17.2584 35.3243C17.2584 35.3243 6.45925 36.5771 4.2062 39.1215C1.77834 41.8601 -0.989419 48.26 3.05054 52.096C7.16819 56 24.5905 56 24.5905 56C24.5905 56 42.3236 56 46.4412 52.096C50.4812 48.26 47.7037 41.8699 45.2856 39.1215C43.0325 36.5771 32.2334 35.3243 32.2334 35.3243M17.2584 35.3243C19.4532 34.9941 20.4243 30.6239 20.4243 30.6239M17.2584 35.3243C16.1998 36.2566 14.6751 36.5868 18.045 40.9181C21.4149 45.2494 24.5808 45.4533 24.5808 45.4533C24.5808 45.4533 28.0575 45.2397 31.4274 40.9181C34.7972 36.5965 33.2725 36.2663 32.214 35.3243C29.1646 34.994 29.1646 30.6239 29.1646 30.6239M24.5905 32.2653C24.5905 32.2653 18.9967 32.2653 16.8893 26.7784C15.5103 23.1948 15.151 19.9512 15.3841 16.9407C15.4909 15.5714 15.8988 14.8819 16.0444 13.2795C16.1513 12.085 15.9376 12.454 16.0444 10.9779C16.1513 9.50172 16.4912 7.39434 19.424 6.65627C21.813 6.05416 23.2892 7.3652 24.5905 7.39434C25.9695 7.42347 27.6787 6.05416 30.0678 6.65627C32.9909 7.39434 33.3405 9.50172 33.4473 10.9779C33.5542 12.454 33.3405 12.0752 33.4473 13.2795C33.593 14.8916 34.0009 15.5714 34.1077 16.9407C34.3408 19.9512 33.9815 23.1948 32.6024 26.7784C30.4854 32.2653 24.5905 32.2653 24.5905 32.2653Z" stroke="#ACB1CA" stroke-miterlimit="10"/>--}}
{{--                                                    </svg>--}}
{{--                                                    <p class="mt-1 font-weight-bold">@lang('web/auth.individual')</p>--}}
{{--                                                    <p class="text-center font-size-xsmall">@lang('web/auth.individual_desc')</p>--}}



{{--                                                </div>--}}
{{--                                            </div>--}}
                                            <div class="col-12 p-0">
                                                <div data-value="corporate" class="m-0 m-auto selected type-selector p-25 d-flex align-items-center justify-content-center flex-column">
                                                    <svg class="mt-75" width="77" height="57" viewBox="0 0 77 57" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M47.7577 16.995C47.7577 16.995 48.9918 15.8052 50.036 17.2834C51.8616 19.8522 47.5752 23.3403 47.5752 23.3403M26.6179 16.387C26.6179 16.387 24.9864 8.8024 27.6182 4.94696C30.7841 0.304894 38.058 1.04296 38.058 1.04296C38.058 1.04296 45.6426 0.304894 48.8085 4.94696C51.4403 8.8024 49.8088 16.387 49.8088 16.387M28.9409 16.995C28.9409 16.995 27.7068 15.8052 26.6626 17.2834C24.8371 19.8522 29.1235 23.3403 29.1235 23.3403M33.4062 31.2067C33.4062 31.2067 32.5225 34.5086 28.7933 35.3244M28.7933 35.3244C28.7933 35.3244 28.1523 36.5868 31.5222 40.9181C34.9018 45.2397 38.058 45.4534 38.058 45.4534C38.058 45.4534 41.5347 45.2397 44.9045 40.9181C48.2841 36.5966 47.6334 35.3244 47.6334 35.3244C43.205 34.023 43.0205 31.2067 43.0205 31.2067M28.7933 35.3244C28.7933 35.3244 20.9076 36.5771 18.6545 39.1215C16.2267 41.8601 13.4589 48.26 17.4989 52.096C21.6165 56 38.0677 56 38.0677 56C38.0677 56 54.8296 56 58.9473 52.096C62.9872 48.26 60.2098 41.8699 57.7916 39.1215C55.5386 36.5771 47.6529 35.3243 47.6529 35.3243M20.6648 21.5341C20.6648 21.5341 23.0052 14.9594 21.3931 12.5996C19.4605 9.76381 17.8286 9.27392 15.0127 9.2394C12.1295 9.20405 10.3706 9.76381 8.43805 12.5996C6.82596 14.9594 9.12757 21.5341 9.12757 21.5341M16.4986 28.6526C16.4986 28.6526 17.9553 30.624 20.6745 31.1678M20.6745 31.1678C20.6745 31.1678 21.0726 31.9447 19.0041 34.5863C16.9453 37.2278 15.003 37.3637 15.003 37.3637C15.003 37.3637 12.8762 37.2375 10.8174 34.5863C8.75853 31.9447 8.87751 32.629 9.14699 31.1678C12.614 30.624 13.0996 28.6526 13.0996 28.6526M20.6745 31.1678C20.6745 31.1678 25.4914 31.935 26.8704 33.4888C27.2394 33.9064 27.6279 34.4697 27.9581 35.1106M16.6248 43.7732C15.644 43.8121 15.0127 43.8121 15.0127 43.8121C15.0127 43.8121 4.76715 43.8121 2.25188 41.4231C-0.214821 39.0826 1.47496 35.1689 2.96082 33.4888C4.33984 31.935 9.15672 31.1678 9.15672 31.1678M55.8397 21.5341C55.8397 21.5341 53.4992 14.9594 55.1113 12.5996C57.0439 9.76381 58.6758 9.27392 61.4917 9.2394C64.3749 9.20405 66.1338 9.76381 68.0664 12.5996C69.6785 14.9594 67.3768 21.5341 67.3768 21.5341M60.0058 28.6526C60.0058 28.6526 58.5491 30.624 55.8299 31.1678M55.8299 31.1678C55.8299 31.1678 55.4318 31.9447 57.5003 34.5863C59.5591 37.2278 61.5014 37.3637 61.5014 37.3637C61.5014 37.3637 63.6282 37.2375 65.6871 34.5863C67.7459 31.9447 67.6269 32.629 67.3574 31.1678C63.8904 30.624 63.4048 28.6526 63.4048 28.6526M55.8299 31.1678C55.8299 31.1678 51.0131 31.935 49.634 33.4888C49.265 33.9064 48.8765 34.4697 48.5464 35.1106M59.8796 43.7732C60.8605 43.8121 61.4917 43.8121 61.4917 43.8121C61.4917 43.8121 71.7373 43.8121 74.2525 41.4231C76.7192 39.0826 75.0294 35.1689 73.5436 33.4888C72.1646 31.935 67.3477 31.1678 67.3477 31.1678M38.058 32.2653C38.058 32.2653 32.4642 32.2653 30.3568 26.7783C28.9778 23.1948 28.6184 19.9512 28.8515 16.9406C28.9583 15.5713 29.3662 14.8818 29.5119 13.2794C29.6187 12.0849 29.4051 11.4828 29.5119 10.0067C29.6187 8.53053 29.9586 6.42315 32.8915 5.68508C35.2805 5.08297 36.7566 6.39401 38.058 6.42315C39.437 6.45228 41.1462 5.08297 43.5352 5.68508C46.4583 6.42315 46.808 8.53053 46.9148 10.0067C47.0216 11.4828 46.808 12.0752 46.9148 13.2794C47.0605 14.8915 47.4683 15.5713 47.5752 16.9406C47.8082 19.9512 47.4489 23.1948 46.0699 26.7783C43.9625 32.2653 38.058 32.2653 38.058 32.2653ZM15.0127 29.3032C15.0127 29.3032 18.4311 29.3032 19.7228 25.9528C20.5677 23.758 20.791 21.7768 20.6453 19.9414C20.5774 19.1062 20.3346 18.6886 20.2472 17.698C20.1792 16.9697 20.3152 17.5718 20.2472 16.6686C20.1792 15.7655 19.9753 14.4738 18.1884 14.0271C16.7316 13.6581 15.8285 14.4641 15.0321 14.4738C14.1872 14.4933 13.1384 13.6581 11.6817 14.0271C9.89477 14.4738 9.68113 15.7655 9.62286 16.6686C9.55488 17.5718 9.69084 16.96 9.62286 17.698C9.53546 18.6789 9.28295 19.0965 9.22469 19.9414C9.07901 21.7768 9.30239 23.7677 10.1473 25.9528C11.4001 29.3032 15.0127 29.3032 15.0127 29.3032ZM61.4917 29.3032C61.4917 29.3032 58.0733 29.3032 56.7817 25.9528C55.9368 23.758 55.7134 21.7768 55.8591 19.9414C55.927 19.1062 56.1698 18.6886 56.2572 17.698C56.3252 16.9697 56.1893 17.5718 56.2572 16.6686C56.3252 15.7655 56.5292 14.4738 58.3161 14.0271C59.7728 13.6581 60.6759 14.4641 61.4723 14.4738C62.3172 14.4933 63.366 13.6581 64.8227 14.0271C66.6096 14.4738 66.8233 15.7655 66.8816 16.6686C66.9495 17.5718 66.8136 16.96 66.8816 17.698C66.969 18.6789 67.2215 19.0965 67.2797 19.9414C67.4254 21.7768 67.202 23.7677 66.3571 25.9528C65.1044 29.3032 61.4917 29.3032 61.4917 29.3032Z" stroke="#ACB1CA" stroke-miterlimit="10"/>
                                                    </svg>
                                                    <p class="mt-1 font-weight-bold">@lang('web/auth.group')</p>
                                                    <p class="text-center font-size-xsmall">@lang('web/auth.group_desc')</p>


                                                </div>
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
                                        <input type="hidden" name="pid" value="{{$pid ?? null}}">
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
                                            <input autocomplete="off" value="{{old('email')}}" type="text" id="user-email" class="form-control @error('email') is-invalid @enderror" name="email" style="padding-right: 75px" placeholder="@lang('web/auth.email')">
                                            <div class="form-control-position">
                                                <i class="feather icon-user"></i>
                                            </div>
                                            {{-- <div class="form-control-position email-verify-btn">
                                                <span>@lang('web/auth.verify')</span>
                                            </div> --}}
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
                                        <fieldset class="mb-2 d-flex flex-row">
                                            <div class="vs-checkbox-con vs-checkbox-primary">
                                                <input name="accept" type="checkbox" value="1">
                                                <span class="vs-checkbox vs-checkbox-lg" style="width: 60px;">
                                                  <span class="vs-checkbox--check">
                                                    <i class="vs-icon feather icon-check"></i>
                                                  </span>
                                                </span>
                                                <div class="d-flex flex-wrap privacy" style="max-width: 300px">
                                                    <p >
                                                        {{__('web/auth.i_have_read')}}
                                                        <a href="#" class="openPage" data-src="{{route('page.index',['importantNotice','mobile'=>'1'])}}">{{__('web/auth.important_notice')}}</a>
                                                        ,
                                                        {{__('web/auth.and_agreed')}}
                                                        <a href="#" class="openPage" data-src="{{route('page.index',['termsOfUse','mobile'=>'1'])}}">{{__('web/auth.term_of_use')}}</a>
                                                        {{__('web/auth.and')}}
                                                        <a href="#" class="openPage" data-src="{{route('page.index',['privacyStatement','mobile'=>'1'])}}">{{__('web/auth.privacy_statement')}}</a> .
                                                    </p>
                                                </div>
                                            </div>
                                            <style>
                                                .privacy a{
                                                    text-decoration: underline;
                                                }
                                            </style>


                                        </fieldset>
                                        <a href="{{route('userpanel.dashboard.main')}}" class="btn btn-outline-primary float-left btn-inline waves-effect waves-light">@lang('web/auth.back')</a> 
                                        <button type="submit" class="btn btn-primary float-right btn-inline waves-effect waves-light register-btn">@lang('web/auth.register')</button>
                                    </form>
                                </div>
                            </div>
                            {{--<div class="login-footer">
                                <div class="divider">
                                    <div class="divider-text">@lang('web/auth.or')</div>
                                </div>
                                <div class="footer-btn d-flex justify-content-center align-items-center">
                                    <a href="{{route('oauth.redirect','facebook')}}" class="btn btn-facebook waves-effect waves-light"><span class="fa fa-facebook"></span></a>
                                    --}}{{--<a href="#" class="btn btn-twitter white waves-effect waves-light"><span class="fa fa-twitter"></span></a>--}}{{--
                                    <a href="{{route('oauth.redirect','google')}}" class="btn btn-google waves-effect waves-light"><span class="fa fa-google"></span></a>
                                    --}}{{--<a href="#" class="btn btn-github waves-effect waves-light"><span class="fa fa-github-alt"></span></a>--}}{{--
                                </div>

                            </div>--}}

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
                            <label class="text-center">@lang('web/auth.verification_code_desc_email')</label>

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
    <div class="modal fade" id="pageModal" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalScrollableTitle">Information</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                </div>
            </div>
        </div>
    </div>
@endsection
