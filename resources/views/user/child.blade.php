@extends('layouts.contentLayoutMaster')
@section('title', __('web/child.child'))
@section('content')
    <section id="basic-examples">
        <div class="row">
            <div class="col-md-12">
                <form id="beneficiaryForm" action="#" method="post">

                    <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{__('web/child.child')}}</h4>

                    </div>
                    <div class="card-content">
                        <div class="card-body ">
                            <p>{{__('web/child.child_desc')}}</p>

                                @csrf
                                <div class="addNewC addNewData d-inline-block">
                                    <button type="submit" class="btn btn-outline-primary ">
                                        {{__('web/child.add_child')}}
                                    </button>

                                </div>
                                <input type="hidden" value="" name="childs_data">
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <div class="row childs">
                                            @foreach($childs as $child)
                                                <div class="col-lg-4 col-sm-12 childData" data-email="{{$child->uuid}}" >
                                                    <div class="card text-white bg-gradient-dark bg-white text-left">
                                                        <div class="card-content d-flex">
                                                            <div class="card-body">
                                                                <img src="{{($child->gender == 'Male' ? asset('images/male.svg') : asset('images/female.svg'))}}" alt="" width="100" height="100" class="float-left px-1">

                                                                <h4 class="card-title text-white mt-2">{{$child->name}}</h4>
                                                                <p class="card-text mb-0">{{__('web/child.dob')}} : {{$child->date_birth}}</p>

                                                                <i class="feather icon-edit-2 white font-size-large  mr-1 mt-1 mb-1  position-absolute" style="bottom: 0px;right: 0px"></i>


                                                                <i class="feather icon-trash-2 white font-size-large  mr-4 mt-1 mb-1  position-absolute" style="bottom: 0px;right: 0px"></i>

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

                </form>
            </div>
        </div>
        @include('user.user_modal',['hide'=>[
    'relation_ship',
    'allocate_percentage',
    'email',
]])
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
    <script>
        var isedit = false;

        $(".overlay-bg").on("click",function (e) {
            //$(".hide-data-sidebar").click();
        })
        $("#add-child-modal").on("hide.bs.modal",function (e) {

            var name = $("input[name=name]");
            var email = $("input[name=email]");
            var nationality = $("select[name=nationality]");
            var passport = $("input[name=passport]");
            var passport_expiry_date = $("input[name=passport_expiry_date]");
            var dob = $("input[name=dob]");
            var mobile = $("input[name=mobile]");

            name.val('');
            email.val('');
            nationality.val('Malaysian').change();
            passport.val('');
            mobile.val('');
            passport_expiry_date.val('');
            dob.val('');
        });
        $(".addNewData").on("click",function (e) {

            $("#add-child-form .col-12").show();
            if(isedit && $("select[name=child_type]").val() == 'hibah'){
                $("#add-child-form .col-12").hide();

            }
            isedit = false;

            e.preventDefault();

            $("#add-nominee-modal").modal('show')
        })
    </script>

    <script>


        ChildList = [
            @foreach($childs as $child)
            {
                name : '{{$child->name}}',
                uuid : '{{$child->uuid}}',
                email : '{{$child->email}}',
                nationality : '{{$child->nationality}}',
                passport :  '{{$child->nric}}',
                passport_expiry_date :  '{{$child->passport_expiry_date}}',
                dob : '{{$child->date_birth}}',
                gender : '{{$child->gender}}',
                child_type : '{{$child->type}}',

            }
            @if(!$loop->last)
            ,
            @endif

            @endforeach
        ];



        $(".addNew").on("click",function (e) {
            $("#addNewModal").modal('show');
        })
        $("#add-nominee-form").on("submit",function (e) {

            Validation.clearAllValidation();
            var name = $("input[name=name]");
            var nationality = $("select[name=nationality]");
            var passport = $("input[name=passport]");
            var dob = $("input[name=dob]");
            var gender = $("input[name=gender]");
            var relation_ship = $("select[name=relation_ship]");
            var child_type = $("select[name=child_type]");


            if(name.val() == '') {
                Validation.setInvalid(name, '@lang('web/auth.required')');
                return false;
            }
            if(!Validation.isValidFullName(name)){
                Validation.setInvalid(name, '@lang('web/profile.full_name_error')');
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
            if(dob.val() == '' || dob.val().length < 10){
                Validation.setInvalid(dob,'@lang('web/auth.required')');
                return false;
            }

            $("#addNewModal").modal('hide');

            var data = {
                name : name.val(),
                email : email.val(),
                nationality : nationality.val(),
                passport : passport.val(),
                passport_expiry_date : passport_expiry_date.val(),
                dob : dob.val(),
                gender : gender.val(),
                relation_ship : relation_ship.val(),
                child_type : child_type.val(),
                _token : '{{csrf_token()}}',

            };
            $(".loading").show();
            $.post("{{route('wb-api.save-child')}}",data,function (d) {
                console.log(d);
                $(".loading").hide();
            })

            return  false;
        });
        $(document).ready(function () {
            changeNric($("select[name=nationality]").val());
        });
        $("input[name=gender]").on("change",function (e) {
            $(".gender-selector").removeClass("selected");
            if($(this).val() == 'Male'){
                $(".gender-selector[data-value=male]").addClass('selected');

            }else{
                $(".gender-selector[data-value=female]").addClass('selected');
            }
        })

        </script>
        @include('user.user_modal_script')
    <script>
        var FillConfirmation = false;
        $("body").on("click",".icon-edit-2",function (e) {
            var thiis = this;
            var selector = $(thiis).parents(".childData");
            var email = $(selector).data('email');
            var data = ChildList.find(x => x.uuid == email);
            var remain = 0;


            var name = $("input[name=name]").val(data.name).change();
            var email = $("input[name=email]").val(data.email).change();
            var nationality = $("select[name=nationality]").val(data.nationality).change();
            var passport = $("input[name=passport]").val(data.passport).change();
            var passport_expiry_date = $("input[name=passport_expiry_date]").val(data.passport_expiry_date).change();
            var dob = $("input[name=dob]").val(data.dob).change();
            var gender = $("input[name=gender]").val(data.gender).change();
            var relation_ship = $("select[name=relation_ship]").val(data.relation_ship).change();
            var child_type = $("select[name=child_type]").val(data.child_type).change();

            isedit = true;
            $(".addNewData").click();


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

                };
            })
        })

    </script>
@endsection
