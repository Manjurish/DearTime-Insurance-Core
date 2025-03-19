@extends('layouts.contentLayoutMaster')
@section('title', __('web/beneficiary.beneficiary'))
@section('content')
    <section id="basic-examples">
        <div class="row">
            <div class="col-md-12">
                <form id="beneficiaryForm" action="{{route('userpanel.Beneficiary.store')}}" method="post">
                    @if(request()->has('mn'))
                        <input type="hidden" name="mn" value="{{request()->has('mn') ? '1':'0'}}">
                    @endif
                    <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{__('web/beneficiary.nominee')}}</h4>

                    </div>
                    <div class="card-content">
                        <div class="card-body ">
                            <p>{{__('web/beneficiary.nominee_desc')}}</p>

                                @csrf
                                <div class="addNewC addNewData d-inline-block">
                                    <button type="submit" class="btn btn-outline-primary ">
                                        {{__('web/beneficiary.new_nominee')}}
                                    </button>

                                </div>
                                <input type="hidden" value="" name="nominees_data">
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <div class="row nominees">
                                            @foreach($nominees as $nominee)
                                                <div class="col-lg-4 col-sm-12 nomineeData" data-email="{{$nominee->email}}" >
                                                    <div class="card text-white bg-gradient-dark bg-white text-left">
                                                        <div class="card-content d-flex">
                                                            <div class="card-body">
                                                                <img src="{{$nominee->isCharity() ? asset('images/charity.svg') : ($nominee->gender == 'male' ? asset('images/male.svg') : asset('images/female.svg'))}}" alt="" width="100" height="100" class="float-left px-1">


                                                                @if($nominee->isCharity())
                                                                    <h4 class="card-title text-white mt-3 ">{{$nominee->name}}</h4>
                                                                @else
                                                                    <h4 class="card-title text-white mt-2">{{$nominee->name}}</h4>
                                                                    <p class="card-text mb-0">{{__('web/beneficiary.relation_ship')}} : {{$nominee->relationship}}</p>
{{--                                                                    <p class="card-text mb-3">{{__('web/beneficiary.nominee_type')}} : {{$nominee->type}}</p>--}}
                                                                @endif

                                                                <div class="badge badge-primary badge-md mr-1 mt-1 mb-1 position-absolute" style="top: 0px;right: 0px">{{$nominee->percentage}} %</div>
                                                                <i class="feather icon-edit-2 white font-size-large  mr-1 mt-1 mb-1  position-absolute" style="bottom: 0px;right: 0px"></i>


                                                                <i class="feather icon-trash-2 white font-size-large  mr-4 mt-1 mb-1  position-absolute @if($nominee->isCharity()) d-none @endif" style="bottom: 0px;right: 0px"></i>

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
                    <div class="form-group my-2">
                        <button type="submit" class="btn btn-primary storeBtn">
                            {{__('web/beneficiary.submit')}}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @include('user.user_modal',['hide'=>['mobile'],'show'=>['relation_ship_parent']])
    </section>

@endsection
@section('mystyle')
    <link rel="stylesheet" type="text/css" href="{{asset('vendors/css/pickers/pickadate/pickadate.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('vendors/css/extensions/nouislider.min.css')}}">
    <style>
        .row [class*='col-'] {
            transition: all 0.5s ease-in-out;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/pages/data-list-view.css') }}">

@endsection
@section('myscript')
    <script src="{{asset('vendors/js/pickers/pickadate/picker.js')}}"></script>
    <script src="{{asset('vendors/js/pickers/pickadate/picker.date.js')}}"></script>
    <script src="{{asset('vendors/js/pickers/pickadate/picker.time.js')}}"></script>
    <script src="{{asset('vendors/js/pickers/pickadate/legacy.js')}}"></script>
    <script src="{{asset('vendors/js/extensions/nouislider.min.js')}}"></script>
    <script src="{{asset('js/scripts/data-list-view.js')}}"></script>
    <script src="{{asset('js/jquery.form.js')}}"></script>

    <script>

        var isedit = false;
        var has_only_parent = {{auth()->user()->profile->hasOnlyParentNominee() ? 'true' : 'false'}};
        var is_muslim = {{auth()->user()->profile->religion == 'muslim' ? 'true' : 'false'}};
        var show_parent = false;

        if(!is_muslim && has_only_parent){
            show_parent = true;
        }else{
            show_parent = false;
        }

        $(".overlay-bg").on("click",function (e) {
            //$(".hide-data-sidebar").click();
        })
        $("#add-nominee-modal").on("hide.bs.modal",function (e) {

            var name = $("input[name=name]");
            var email = $("input[name=email]");
            var nationality = $("select[name=nationality]");
            var passport = $("input[name=passport]");
            var passport_expiry_date = $("input[name=passport_expiry_date]");
            var dob = $("input[name=dob]");
            var mobile = $("input[name=mobile]");

            name.val('');
            email.val('');
            nationality.val('{{\App\Country::where("nationality","Malaysian")->first()->uuid}}').change();
            passport.val('');
            mobile.val('');
            passport_expiry_date.val('');
            dob.val('');
        });
        $(".addNewData").on("click",function (e) {

            $("#add-nominee-form .col-12").show();
            hideBirthCert();
            if(isedit && $("select[name=nominee_type]").val() == 'hibah'){
                $("#add-nominee-form .col-12").hide();
                $("#add-nominee-form #allocate_percentage_div").show();

            }
            isedit = false;

            e.preventDefault();

            $("#add-nominee-modal").modal('show')
        })
    </script>

    <script>


        NomineeList = [
            @foreach($nominees as $nominee)
            {
                name : '{{($nominee->isCharity() ? 'charity_insurance' : $nominee->name)}}',
                nric :  '{{$nominee->nric}}',
                dob : '{{$nominee->dob}}',
                nationality : '{{ ($nominee->isCharity() ? 'Malaysian' : ($nominee->nationalityData->uuid ?? '')) }}',
                email : '{{$nominee->email}}',
                gender : '{{$nominee->gender}}',
                passport_expiry_date :  '{{$nominee->passport_expiry_date}}',
                relationship : '{{ ($nominee->isCharity() ? 'other' : $nominee->relationship) }}',
                type : '{{$nominee->type}}',
                percentage : '{{$nominee->percentage}}',

            }
            @if(!$loop->last)
            ,
            @endif

            @endforeach
        ];


        var allocate_percentage_slider = document.getElementById('allocate_percentage_slider');
        noUiSlider.create(allocate_percentage_slider, {
            start: 0,
            connect: [true, false],
            behaviour: 'drag',
            range: {
                'min': 0,
                'max': 100
            }
        });
        var remain = 0;
        NomineeList.map((val,i)=>{
            remain += parseInt(val.percentage);
        });
        allocate_percentage_slider.noUiSlider.updateOptions({range: {'min': 0, 'max':(100 - remain == 0) ? 1 : (100 - remain)}}, true);
        if(remain == 100){
            $(".addNewC").hide();
        }else{
            $(".addNewC").show();
        }
        allocate_percentage_slider.noUiSlider.on('update', function (values, handle) {
            var value = Math.round(values[0]);
            $("#allocate_percentage_value").html(value);
            $("input[name=allocate_percentage]").val(value);

        });



        $(".addNew").on("click",function (e) {
            $("#addNewModal").modal('show');
        })
        $("#add-nominee-form").on("submit",function (e) {
            var remain = 0;
            NomineeList.map((val,i)=>{
                remain += parseInt(val.percentage);
            });
            if($("[data-email='"+$("input[name=email]").val()+"']").length > 0){
                var dataId = NomineeList.findIndex(x => x.email == $("input[name=email]").val());
                remain = parseInt(remain) - parseInt(NomineeList[dataId].percentage);
            }
            remain = remain + parseInt($("input[name=allocate_percentage]").val());
            if(remain > 100){
                Swal.fire({
                    title: 'Information',
                    text: '{{__('web/beneficiary.add_max_100')}}',
                    type: 'error',
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Ok',
                    confirmButtonClass: 'btn btn-primary',
                    buttonsStyling: false,
                });
                $("#add-nominee-modal").modal('hide');
                return false;
            }

            Validation.clearAllValidation();
            var name = $("input[name=name]");
            var email = $("input[name=email]");
            var nationality = $("select[name=nationality]");
            var passport = $("input[name=passport]");
            var passport_expiry_date = $("input[name=passport_expiry_date]");
            var dob = $("input[name=dob]");
            var gender = $("input[name=gender]");
            var relation_ship = $("select[name=relation_ship]");
            var nominee_type = $("select[name=nominee_type]");
            var allocate_percentage = $("input[name=allocate_percentage]");


            var isCharity = nominee_type.val() == 'hibah';

            if(!isCharity) {
                if (name.val() == '') {
                    Validation.setInvalid(name, '@lang('web/auth.required')');
                    return false;
                }
                if (!Validation.isValidFullName(name)) {
                    Validation.setInvalid(name, '@lang('web/profile.full_name_error')');
                    return false;
                }

                if (Validation.empty(email)) {
                    Validation.setInvalid(email, '{{__('web/auth.required')}}');
                    return false;
                }
                if (!Validation.validateEmail(email.val())) {
                    Validation.setInvalid(email, '{{__('web/auth.email_invalid')}}');
                    return false;
                }
                if (email.val() == '{{auth()->user()->email}}') {
                    Validation.setInvalid(email, '{{__('web/beneficiary.your_email')}}');
                    return false;
                }

                if (Validation.empty(nationality)) {
                    Validation.setInvalid(nationality, '{{__('web/auth.required')}}');
                    return false;
                }

                if (passport.val() == '' || (isLocal(nationality.val()) && !isValidMykad(passport.val(), false))) {
                    Validation.setInvalid(passport, '@lang('web/auth.required')');
                    return false;
                }
                if ((passport_expiry_date.val() == '' || passport_expiry_date.val().length < 10) && !isLocal(nationality.val())) {
                    Validation.setInvalid(passport_expiry_date, '@lang('web/auth.required')');
                    return false;
                }
                if (dob.val() == '' || dob.val().length < 10) {
                    Validation.setInvalid(dob, '@lang('web/auth.required')');
                    return false;
                }

                if (Validation.empty(relation_ship)) {
                    Validation.setInvalid(relation_ship, '{{__('web/auth.required')}}');
                    return false;
                }

                if (Validation.empty(nominee_type)) {
                    Validation.setInvalid(nominee_type, '{{__('web/auth.required')}}');
                    return false;
                }
            }
            if(allocate_percentage.val() == 0) {
                var emailTitle = email.val();
                $(".hide-data-sidebar").click();
                $("[data-email='"+emailTitle+"'] .icon-trash-2").click();
                console.log("[data-email='"+(email.val())+"'] .icon-trash-2");
                return  false;
            }


            $("#addNewModal").modal('hide');
            var data = {
                name : name.val(),
                nric : passport.val(),
                dob : dob.val(),
                nationality : nationality.val(),
                email : email.val(),
                gender : gender.val().toLowerCase(),
                passport_expiry_date : passport_expiry_date.val(),
                relationship : relation_ship.val(),
                type : nominee_type.val(),
                percentage : allocate_percentage.val(),
            };

            var maleIcon = '{{asset('images/male.svg')}}';
            var femaleIcon = '{{asset('images/female.svg')}}';
            var charityIcon = '{{asset('images/charity.svg')}}';
            if($("[data-email='"+email.val()+"']").length > 0){
                //Validation.setInvalid(email, '{{__('web/beneficiary.email_duplicate')}}');
                var dataId = NomineeList.findIndex(x => x.email == email.val());
                NomineeList[dataId] = data;
                $(".nomineeData[data-email='"+email.val()+"']").html(
                    '<div class="card text-white bg-gradient-dark bg-white text-left">\n' +
                    '<div class="card-content d-flex">\n' +
                    '<div class="card-body">\n' +
                    '<img src="' + (isCharity ? charityIcon : (gender.val().toLowerCase() == 'male' ? maleIcon : femaleIcon)) + '" alt="" width="100" height="100" class="float-left px-1">\n' +
                    '\n' +
                    (isCharity ?
                        ('<h4 class="card-title text-white mt-3">' + name.val() + '</h4>\n')
                        :
                        ('<h4 class="card-title text-white mt-2">' + name.val() + '</h4>\n' +
                            '<p class="card-text mb-0">{{__('web/beneficiary.relation_ship')}} : ' + relation_ship.find(":selected").text() + '</p>\n')
                    )
                        +
                    '<div class="badge badge-primary badge-md mr-1 mb-1 position-absolute" style="top: 12px;right: 0px">' + allocate_percentage.val() + ' %</div>\n' +
                    '<i class="feather icon-edit-2 white font-size-large  mr-1 mt-1 mb-1  position-absolute" style="bottom: 0px;right: 0px"></i>' +
                    (isCharity ? '' : '<i class="feather icon-trash-2 white font-size-large  mr-4 mt-1 mb-1  position-absolute" style="bottom: 0px;right: 0px"></i>') +

                    '</div>\n' +
                    '</div>\n' +
                    '</div>\n');
            }else {
                NomineeList.push(data);
                $(".nominees").append('<div  class="col-lg-4 col-sm-12 nomineeData" data-email="' + email.val() + '">\n' +
                    '<div class="card text-white bg-gradient-dark bg-white text-left">\n' +
                    '<div class="card-content d-flex">\n' +
                    '<div class="card-body">\n' +
                    '<img src="' + (gender.val().toLowerCase() == 'male' ? maleIcon : femaleIcon) + '" alt="" width="100" height="100" class="float-left px-1">\n' +
                    '\n' +
                    '<h4 class="card-title text-white mt-2">' + name.val() + '</h4>\n' +
                    '<p class="card-text mb-0">{{__('web/beneficiary.relation_ship')}} : ' + relation_ship.find(":selected").text() + '</p>\n' +
                    {{--'<p class="card-text mb-3">{{__('web/beneficiary.nominee_type')}} : ' + nominee_type.find(":selected").text() + '</p>\n' +--}}
                    '<div class="badge badge-primary badge-md mr-1 mb-1 position-absolute" style="top: 12px;right: 0px">' + allocate_percentage.val() + ' %</div>\n' +
                    '<i class="feather icon-trash-2 white font-size-large  mr-1 mt-1 mb-1  position-absolute" style="bottom: 0px;right: 0px"></i>' +
                    '<i class="feather icon-edit-2 white font-size-large  mr-4 mt-1 mb-1  position-absolute" style="bottom: 0px;right: 0px"></i>' +
                    '</div>\n' +
                    '</div>\n' +
                    '</div>\n' +
                    '</div>');
            }
            name.val('');
            email.val('');
            nationality.val('{{\App\Country::where("nationality","Malaysian")->first()->uuid}}').change();
            passport.val('');
            passport_expiry_date.val('');
            dob.val('');
            allocate_percentage.val(0);
            allocate_percentage_slider.noUiSlider.set([0]);
            var remain = 0;
            has_only_parent = true;
            NomineeList.map((val,i)=>{
                remain += parseInt(val.percentage);
                console.log(val);
                if(val.relationship != 'parent'){
                    if(val.type != 'hibah')
                        has_only_parent = false;
                }
            });
            allocate_percentage_slider.noUiSlider.updateOptions({range: {'min': 0, 'max':(100 - remain == 0) ? 1 : (100 - remain)}}, true);
            if(remain == 100){
                $(".addNewC").hide();
            }else{
                $(".addNewC").show();
            }
            $("#add-nominee-modal").modal('hide');


            if(!is_muslim && has_only_parent){
                $("#ask_parent").show();
            }else{
                $("#ask_parent").hide();
            }

            return false;


        });
        $(document).ready(function () {
            changeNric($("select[name=nationality]").val());
        });
        $("input[name=gender]").on("change",function (e) {
            $(".gender-selector").removeClass("selected");
            if($(this).val() == 'male'){
                $(".gender-selector[data-value=male]").addClass('selected');

            }else{
                $(".gender-selector[data-value=female]").addClass('selected');
            }
        })

        </script>
        @include('user.user_modal_script')
    <script>
        var FillConfirmation = false;
        $("#beneficiaryForm").on("submit",function (e) {
            //
            // if(NomineeList.length == 0){
            //     $(".addNewData").click();
            //     return false;
            // }
            var remain = 0;
            NomineeList.map((val,i)=>{
                remain += parseInt(val.percentage);
            });
            if(remain < 100 && !FillConfirmation){
                Swal.fire({
                    title: 'Confirmation',
                    text: '{{__('web/beneficiary.charity_alert')}}',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes',
                    confirmButtonClass: 'btn btn-primary',
                    cancelButtonClass: 'btn btn-danger ml-1',
                    buttonsStyling: false,
                }).then(function (result) {
                    if(result.value) {

                        var data = {
                            name: 'charity_insurance',
                            email: 'Charity@Deartime.com',
                            nationality: 'Malaysian',
                            status: 'Done',
                            nric: '950101-00-0000',
                            gender: 'male',
                            passport_expiry_date: null,
                            dob: '01/01/1995',
                            relationship: 'other',
                            type: 'hibah',
                            percentage: 100 - remain,
                        };

                        //check if charity already exists.
                        var charityIndex = NomineeList.findIndex(x=>x.type == 'hibah');
                        if(charityIndex > -1) {
                            data['percentage'] = parseInt(data.percentage) + parseInt(NomineeList[charityIndex].percentage);
                            NomineeList[charityIndex] = data;
                        }else
                            NomineeList.push(data);

                        $("input[name=nominees_data]").val(JSON.stringify(NomineeList));
                        FillConfirmation = true;
                        $("#beneficiaryForm").submit();
                    }
                });

                return false;
            }

            $("input[name=nominees_data]").val(JSON.stringify(NomineeList));

            $(".loading").show();
            $("#beneficiaryForm").ajaxSubmit(function (e) {
                $(".loading").hide();

                if(e.status == 'success') {
                    @if(request()->has('mn'))
                        window.location = '';
                    @else
                        window.location = '{{asset('user/go/')}}'+'/'+e.data.next_page;
                    @endif
                }
            })

           return false;
        });
        $("body").on("click",".icon-edit-2",function (e) {
            var thiis = this;
            var selector = $(thiis).parents(".nomineeData");
            var email = $(selector).data('email');
            var data = NomineeList.find(x => x.email == email);
            var remain = 0;
            NomineeList.map((val, i) => {
                remain += parseInt(val.percentage);
            });
            remain = remain - data.percentage;
            allocate_percentage_slider.noUiSlider.updateOptions({
                range: {
                    'min': 0,
                    'max': (100 - remain == 0) ? 1 : (100 - remain)
                }
            }, true);

            var name = $("input[name=name]").val(data.name).change();
            var email = $("input[name=email]").val(data.email).change();
            var nationality = $("select[name=nationality]").val(data.nationality).change();
            var passport = $("input[name=passport]").val(data.nric).change();
            var passport_expiry_date = $("input[name=passport_expiry_date]").val(data.passport_expiry_date).change();
            var dob = $("input[name=dob]").val(data.dob).change();
            var gender = $("input[name=gender]").val(data.gender).change();
            var relation_ship = $("select[name=relation_ship]").val(data.relationship).change();
            var nominee_type = $("select[name=nominee_type]").val(data.type).change();
            var allocate_percentage = $("input[name=allocate_percentage]").val(data.percentage).change();

            isedit = true;
            $(".addNewData").click();
            allocate_percentage_slider.noUiSlider.set([data.percentage]);


        });
        $("body").on("click",".icon-trash-2",function (e) {
            var thiis = this;
            Swal.fire({
                title: 'Confirmation',
                text: '{{__('web/beneficiary.delete_confirm')}}',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes',
                confirmButtonClass: 'btn btn-primary',
                cancelButtonClass: 'btn btn-danger ml-1',
                buttonsStyling: false,
            }).then(function (result) {
                if(result.value) {
                    var selector = $(thiis).parents(".nomineeData");
                    var email = $(selector).data('email');
                    var data = NomineeList.find(x => x.email == email);
                    NomineeList.splice(NomineeList.findIndex(x => x.email == email), 1);
                    var remain = 0;
                    NomineeList.map((val, i) => {
                        remain += parseInt(val.percentage);
                    });
                    allocate_percentage_slider.noUiSlider.updateOptions({
                        range: {
                            'min': 0,
                            'max': (100 - remain == 0) ? 1 : (100 - remain)
                        }
                    }, true);
                    if (remain == 100) {
                        $(".addNewC").hide();
                    } else {
                        $(".addNewC").show();
                    }
                    $(selector).remove();
                };
            })
        })

    </script>
@endsection
