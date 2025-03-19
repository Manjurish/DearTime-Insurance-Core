@extends('layouts.contentLayoutMaster')
@section('title', __('web/promote.my_promoted'))
@section('content')
    <section>
        <div class="row match-height">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{__('web/promote.my_promoted')}}</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">

                            <div class="col-12">
                                <div class="row nominees">
                                    @foreach($promoteds as $promoted)
                                        <div class="col-lg-4 col-sm-12">
                                            <div class="card text-white bg-gradient-dark bg-white text-left">
                                                <div class="card-content d-flex">
                                                    <div class="card-body">
                                                        <img src="{{(($promoted->profile->gender ?? 'Male') == 'Male' ? asset('images/male.svg') : asset('images/female.svg'))}}" alt="" width="100" height="100" class="float-left px-1">


                                                        <h4 class="card-title text-white mt-1">{{$promoted->profile->name ?? 'New User'}}</h4>
                                                        <p class="card-text mb-0">{{__('web/auth.email')}} : {{$promoted->email}}</p>
                                                        <p class="card-text mb-0">{{__('web/auth.type')}} : {{$promoted->type}}</p>
                                                        <p class="card-text mb-3">{{__('web/auth.status')}} : {{empty($promoted->password) ? 'Pending' : 'Registered'}}</p>

                                                        @if($promoted->isPendingPromoted())
                                                        <a href="{{route('userpanel.promote.medicalSurvey',[$promoted->uuid])}}" class=" mr-1 mt-1 mb-1  position-absolute" style="bottom: 0px;right: 0px">
                                                            <i class="feather icon-edit-2 white font-size-large"></i>
                                                        </a>
                                                        @endif

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                </div>

                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('user.user_modal',['hide'=>['relation_ship','nominee_type','allocate_percentage']])

    </section>
    @include('user.user_modal',['hide'=>['relation_ship','nominee_type','allocate_percentage']])

@endsection
@section('mystyle')
    <link rel="stylesheet" type="text/css" href="{{asset('vendors/css/pickers/pickadate/pickadate.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('vendors/css/extensions/nouislider.min.css')}}">
@endsection
@section('myscript')
    <script src="{{asset('vendors/js/pickers/pickadate/picker.js')}}"></script>
    <script src="{{asset('vendors/js/pickers/pickadate/picker.date.js')}}"></script>
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
            $.post("{{route('wb-api.addPromoter')}}",{_token:'{{csrf_token()}}',type:'individual',name : name.val(),passport:passport.val(),dob:dob.val(),nationality:nationality.val(),email:email.val(),gender:gender.val(),},function (e) {
               console.log(e);
                $(".loading").hide();
                if(e.status == 'success') {
                    $("#add-nominee-modal").modal('hide');
                    window.location = '{{route('userpanel.product.index')}}?uid='+e.data.next_page_params.user_id;
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
    @include('user.user_modal_script')
@endsection
