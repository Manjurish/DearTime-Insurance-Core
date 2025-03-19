@extends('layouts.contentLayoutMaster')
@section('title', __('web/promote.promotion'))
@section('content')
    <section>
        <div class="row match-height">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{__('web/promote.promotion')}}</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <div class="col-md-12 d-flex align-items-center justify-content-center">
                                <div class="media d-flex align-items-center flex-column ">
                                    <a class="media-left" href="#">
                                        <img class="rounded-circle mr-2" src="{{auth()->user()->profile->avatar}}" alt="User avatar" height="100" width="100">
                                    </a>
                                    <div class="media-body mt-1  d-flex align-items-center justify-content-center flex-column">
                                        <h5 class="media-heading mb-0 text-center">{{__('web/promote.my_promote_code')}}</h5>
                                        <a class="text-muted text-center" href="#"><small>DT112123</small></a>
                                    </div>
                                </div>

                            </div>
                            <div class="mt-3 d-flex justify-content-center align-items-center">
                                <a href="#" class="btn btn-primary m-1 assist_to_buy">{{__('web/promote.assist_to_buy')}}</a>
                                <a href="{{route('userpanel.promote.myPromoted')}}" class="btn btn-primary m-1">{{__('web/promote.my_promoted')}}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{__('web/promote.promotion')}}</h4>
                </div>
                <div class="card-content">
                    <div class="card-body">

                        <ul class="list-group ">
                            <li class="list-group-item d-flex p-2">
                                <span>Buy Deartime insurance</span>
                                <svg class="position-absolute" style="right: 10px;top: 15px" width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="18" cy="18" r="18" fill="#F5F6FA"/>
                                    <path d="M23.1564 15.5026L19.8079 12.1495C19.609 11.95 19.2868 11.9505 19.0879 12.149C18.8889 12.3474 18.8889 12.6696 19.0874 12.8691L21.5738 15.3585H16.4757C14.5654 15.3585 13.0066 16.9127 13 18.8246V23.4911C13 23.7725 13.2275 24 13.5089 24C13.7903 24 14.0178 23.7725 14.0178 23.4911V18.8261C14.0223 17.475 15.1251 16.3764 16.4757 16.3764H21.5636L19.0868 18.8562C18.8884 19.0556 18.8884 19.3773 19.0874 19.5762C19.1866 19.675 19.3169 19.7248 19.4471 19.7248C19.5774 19.7248 19.7082 19.675 19.8074 19.5757L23.1559 16.2221C23.3549 16.0232 23.3549 15.701 23.1564 15.5026Z" fill="#090909"/>
                                </svg>
                            </li>
                            <li class="list-group-item d-flex p-2">
                                <span>Buy Deartime insurance</span>
                                <svg class="position-absolute" style="right: 10px;top: 15px" width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="18" cy="18" r="18" fill="#F5F6FA"/>
                                    <path d="M23.1564 15.5026L19.8079 12.1495C19.609 11.95 19.2868 11.9505 19.0879 12.149C18.8889 12.3474 18.8889 12.6696 19.0874 12.8691L21.5738 15.3585H16.4757C14.5654 15.3585 13.0066 16.9127 13 18.8246V23.4911C13 23.7725 13.2275 24 13.5089 24C13.7903 24 14.0178 23.7725 14.0178 23.4911V18.8261C14.0223 17.475 15.1251 16.3764 16.4757 16.3764H21.5636L19.0868 18.8562C18.8884 19.0556 18.8884 19.3773 19.0874 19.5762C19.1866 19.675 19.3169 19.7248 19.4471 19.7248C19.5774 19.7248 19.7082 19.675 19.8074 19.5757L23.1559 16.2221C23.3549 16.0232 23.3549 15.701 23.1564 15.5026Z" fill="#090909"/>
                                </svg>
                            </li>
                            <li class="list-group-item d-flex p-2">
                                <span>Buy Deartime insurance</span>
                                <svg class="position-absolute" style="right: 10px;top: 15px" width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="18" cy="18" r="18" fill="#F5F6FA"/>
                                    <path d="M23.1564 15.5026L19.8079 12.1495C19.609 11.95 19.2868 11.9505 19.0879 12.149C18.8889 12.3474 18.8889 12.6696 19.0874 12.8691L21.5738 15.3585H16.4757C14.5654 15.3585 13.0066 16.9127 13 18.8246V23.4911C13 23.7725 13.2275 24 13.5089 24C13.7903 24 14.0178 23.7725 14.0178 23.4911V18.8261C14.0223 17.475 15.1251 16.3764 16.4757 16.3764H21.5636L19.0868 18.8562C18.8884 19.0556 18.8884 19.3773 19.0874 19.5762C19.1866 19.675 19.3169 19.7248 19.4471 19.7248C19.5774 19.7248 19.7082 19.675 19.8074 19.5757L23.1559 16.2221C23.3549 16.0232 23.3549 15.701 23.1564 15.5026Z" fill="#090909"/>
                                </svg>
                            </li>
                        </ul>

                    </div>
                </div>
            </div>
        </div>
        </div>

        <div class="row match-height">
            <div class="col-md-6">
                <div class="row ">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">{{__('web/promote.activity')}}</h4>
                            </div>
                            <div class="card-content">
                                <div class="card-body">
                                    <ul class="list-group notification">
                                        <li class="list-group-item d-flex pt-1 p-1">
                                            <div>
                                                <p>ThanksGiving</p>
                                                <p class="text-muted">this month</p>
                                                <div class="badge badge-primary badge-md mr-1 mb-1 position-absolute" style="right: 0px;top:10px">
                                                    RM 102,000
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-group-item d-flex pt-1 p-1">
                                            <div>
                                                <p>ThanksGiving</p>
                                                <p class="text-muted">this month</p>
                                                <div class="badge badge-primary badge-md mr-1 mb-1 position-absolute" style="right: 0px;top:10px">
                                                    RM 102,000
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-group-item d-flex pt-1 p-1">
                                            <div>
                                                <p>ThanksGiving</p>
                                                <p class="text-muted">this month</p>
                                                <div class="badge badge-primary badge-md mr-1 mb-1 position-absolute" style="right: 0px;top:10px">
                                                    RM 102,000
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
            <div class="row ">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{__('web/promote.notice')}}</h4>
                        </div>
                        <div class="card-content">
                            <div class="card-body">
                                <ul class="list-group notification">
                                    <li class="list-group-item d-flex align-items-center pt-1 p-1 ">
                                        <img class="mr-2" src="{{auth()->user()->profile->avatar}}" alt="User avatar" height="100" width="100">
                                        <div>
                                            <p>What is the benefits of being promoter ?</p>
                                            <span class="text-muted">this month</span>
                                        </div>
                                    </li>
                                    <li class="list-group-item d-flex align-items-center pt-1 p-1 ">
                                        <img class="mr-2" src="{{auth()->user()->profile->avatar}}" alt="User avatar" height="100" width="100">
                                        <div>
                                            <p>What is the benefits of being promoter ?</p>
                                            <span class="text-muted">this month</span>
                                        </div>
                                    </li>
                                    <li class="list-group-item d-flex align-items-center pt-1 p-1 ">
                                        <img class="mr-2" src="{{auth()->user()->profile->avatar}}" alt="User avatar" height="100" width="100">
                                        <div>
                                            <p>What is the benefits of being promoter ?</p>
                                            <span class="text-muted">this month</span>
                                        </div>
                                    </li>

                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </section>
    @include('user.user_modal',['hide'=>['relation_ship','nominee_type','allocate_percentage'],'title'=>__('web/promote.add_new_promoter'),'show'=>['personal_income','household_income','occ']])

@endsection
@section('mystyle')
    <link rel="stylesheet" type="text/css" href="{{asset('vendors/css/pickers/pickadate/pickadate.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('vendors/css/extensions/nouislider.min.css')}}">
@endsection
@section('myscript')
    <script src="{{asset('vendors/js/pickers/pickadate/picker.js')}}"></script>
    <script src="{{asset('vendors/js/pickers/pickadate/picker.date.js')}}"></script>
    <script src="{{asset('vendors/js/extensions/nouislider.min.js')}}"></script>
    <script>
        $(".assist_to_buy").on("click",function (e) {
            Swal.fire({
                title: "{{__('web/promote.assist_to_buy_dialog')}}",
                text: "{{__('web/promote.assist_to_buy_dialog_desc')}}",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '{{__('web/auth.individual')}}',
                cancelButtonText: '{{__('web/auth.group')}}',
                confirmButtonClass: 'btn btn-primary',
                cancelButtonClass: 'btn btn-primary ml-1',
                buttonsStyling: false,
            }).then(function (result) {
                if (result.value) {
                    $("#add-nominee-modal").modal('show')
                }
            });
        });
        $("#add-nominee-form").on("submit",function(e){

            Validation.clearAllValidation();
            var name = $("input[name=name]");
            var email = $("input[name=email]");
            var passport = $("input[name=passport]");
            var dob = $("input[name=dob]");
            var gender = $("input[name=gender]");
            var nationality = $("select[name=nationality]");
            var job = $("select[name=job]");

            if(name.val() == '') {
                Validation.setInvalid(name, '@lang('web/auth.required')');
                return false;
            }
            if(!Validation.isValidFullName(name)){
                Validation.setInvalid(name, '@lang('web/profile.full_name_error')');
                return false;
            }
            if(Validation.empty(email)) {
                Validation.setInvalid(email, '{{__('web/auth.required')}}');
                return false;
            }
            if(!Validation.validateEmail(email.val())){
                Validation.setInvalid(email, '{{__('web/auth.email_invalid')}}');
                return false;
            }
            if(email.val() == '{{auth()->user()->email}}'){
                Validation.setInvalid(email, '{{__('web/beneficiary.your_email')}}');
                return false;
            }

            if(Validation.empty(nationality)) {
                Validation.setInvalid(nationality, '{{__('web/auth.required')}}');
                return false;
            }

            if(Validation.empty(job)) {
                Validation.setInvalid(job, '{{__('web/auth.required')}}');
                return false;
            }

            if(passport.val() == '' || (nationality.val() == 'Malaysian' && !isValidMykad(passport.val(),false))){
                Validation.setInvalid(passport,'@lang('web/auth.required')');
                return false;
            }
            if((passport_expiry_date.val() == '' || passport_expiry_date.val().length < 10) && nationality.val() != 'Malaysian'){
                Validation.setInvalid(passport_expiry_date,'@lang('web/auth.required')');
                return false;
            }
            if(dob.val() == '' || dob.val().length < 10){
                Validation.setInvalid(dob,'@lang('web/auth.required')');
                return false;
            }
            $(".loading").show();
            $.post("{{route('wb-api.addPromoter')}}",{_token:'{{csrf_token()}}',type:'individual',name : name.val(),passport:passport.val(),dob:dob.val(),nationality:nationality.val(),email:email.val(),gender:gender.val(),occ:job.val(),household_income:$("input[name=household_income]").val(),personal_income:$("input[name=personal_income]").val()},function (e) {
               console.log(e);
                $(".loading").hide();
                if(e.status == 'success') {
                    $("#add-nominee-modal").modal('hide');
                    window.location = e.data.next_page_url;
                }else{
                    Swal.fire({
                        title: 'Error',
                        text: e.data || 'something wrong happend',
                        type: 'info',
                        showCancelButton: false,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Ok',
                        confirmButtonClass: 'btn btn-primary',
                        buttonsStyling: false,
                    });
                }

            }).fail(function (e) {
                console.log(e);
                if(e.status == 422){
                    let response = JSON.parse(e.responseText);
                    Validation.parseInvalidRequest(response.errors);
                }
                $(".loading").hide();
            });
            return false;

        });

    </script>
    @include('user.user_modal_script',['show'=>['personal_income','household_income','occ']])
@endsection
