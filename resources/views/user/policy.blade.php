@extends('layouts.contentLayoutMaster')
@section('title', __('web/policy.policy'))
@section('content')
    <section>
        <div class="row match-height">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{__('web/policy.policy')}}</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <div class="col-md-12 d-flex align-items-center justify-content-center">
                                <div class="media d-flex align-items-center flex-column ">
                                    <a class="media-left" href="#">
                                        <img  style="object-fit: cover;object-position: center;" class="rounded-circle mr-2" src="{{auth()->user()->profile->avatar}}" alt="User avatar" height="100" width="100">
                                    </a>
                                    <div class="media-body mt-1  d-flex align-items-center justify-content-center flex-column">
{{--                                        <h5 class="media-heading mb-0 text-center">{{__('web/promote.my_promote_code')}}</h5>--}}
{{--                                        <a class="text-muted text-center" href="#"><small>DT112123</small></a>--}}
                                    </div>
                                </div>

                            </div>
                            <div class="mt-3 d-flex justify-content-center align-items-center">

                                <a href="#" class="btn btn-primary m-1 assist_to_buy">{{__('web/policy.buy_for_others')}}</a>
                                <a href="{{route('userpanel.product.index')}}" class="btn btn-primary m-1">{{__('web/policy.buy_for_myself')}}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{__('web/policy.list_of_policy')}}</h4>
                </div>
                <div class="card-content scrollable-container">
                    <div class="card-body">
                        <style>
                            .action-btn{
                                padding: 0.5rem;
                                margin: 0.5rem;
                                border-radius: 50%;
                                background-color: rgb(245,246,250);
                            }
                            .list-group li:hover{
                                background-color: #fff !important;
                            }
                        </style>
                        <ul class="list-group ">
                            @foreach($list['data']['owner'] ?? [] as $coverage)
                                <li class="list-group-item d-flex flex-column p-2 parent">
                                    <span class="title">{{$coverage['covered']}}</span>
                                    @foreach($coverage['badges'] as $badge)
                                        <span class="badge badge-primary position-absolute" style="right: {{($loop->index * 60) + 10}}px;top: 10px">{{$badge}}</span>
                                    @endforeach
                                    <div class="w-100 mt-3 coverages" style="display: none">
                                        <ul class="list-group ">
                                            @foreach($coverage['coverages'] as $cov)
                                            <li class="list-group-item d-flex p-2 align-items-center justify-content-between">
                                                <span class="badge badge-primary position-absolute" style="right: 10px;top: 10px">{{$cov['status']}}</span>
                                                <div class="d-flex flex-column">
                                                    <div>
                                                        <img src="{{asset('images/products/'.$cov['product_name'].'.png')}}" style="width: 45px"/>
                                                        <span>{{$cov['product_name']}}</span>
                                                    </div>
                                                    <div>
                                                        {{__('mobile.total_coverage')}} : RM{{number_format($cov['coverage'])}}
                                                    </div>
                                                </div>
                                                <div class="actions-list">
                                                    <div class="documents d-none">
                                                        @foreach($cov['documents'] as $doc)
                                                            <div class="p-1 m-1" style="border: 1px solid #ccc;border-radius: 10px"><a target="_blank" href="{{$doc['link']}}">{{$doc['title']}}</a> </div>
                                                        @endforeach
                                                    </div>
                                                    <div class="info d-none">
                                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                                            @if(!empty($cov['decrease']['from']))
                                                                <p class="text-center">{{__('mobile.coverage_decrease_desc',['from'=>$cov['decrease']['from'],'to'=>$cov['decrease']['to'],'alt'=>$cov['decrease']['alt'],'date'=>$cov['decrease']['date']])}}</p>
                                                            @endif
                                                            <div style="width: 90%;border: 1px solid #ccc;border-radius: 5px" class="d-flex align-items-center p-1">
                                                                <img  style="width: 45px;border-radius: 50%;margin-right: 10px" src="{{$cov['ownerProfile']['photo']}}"/>
                                                                <span>{{$cov['ownerProfile']['name']}}</span>
                                                            </div>
                                                            <br>
                                                            <h5>{{__('mobile.list_of_payers')}}</h5>
                                                            @foreach($cov['payers'] as $payer)
                                                                <div class="p-1 m-1" style="border: 1px solid #ccc;border-radius: 10px;width: 90%;">
                                                                    <p>{{__('mobile.payer').' '.($loop->index + 1) }} : {{$payer['title']}}</p>
                                                                    @if($cov['product_name'] !='Medical')
                                                                        <p>{{__('mobile.coverage_price')}} : RM{{number_format($payer['coverage'])}}</p>
                                                                    @else
                                                                        <p>{{__('mobile.annual_limit_rm')}} : RM{{number_format($payer['coverage'])}}</p>
                                                                        <p>{{__('mobile.deductible_rm')}} : RM{{number_format($payer['deductible'])}}/{{__('mobile.admission')}}</p>
                                                                    @endif
                                                                        <p>{{__('mobile.premium')}} : {{($payer['premium'])}}</p>
                                                                    <p>{{__('mobile.due_date')}} : {{($payer['due_date'])}}</p>
                                                                    <p>{{__('mobile.payment_term')}} : {{($payer['payment_term'])}}</p>
                                                                </div>
                                                            @endforeach
                                                        </div>

                                                    </div>
                                                    <a class="action-btn" href="#"><span class="feather icon-info" data-product=""></span></a>
                                                    <a class="action-btn" href="#"><span class="feather icon-eye"></span></a>

                                                    <a class="action-btn" href="{{($coverage['user_id'] == auth()->user()->uuid || empty($coverage['user_id'])) ? route('userpanel.product.index') : route('userpanel.policies.product',$coverage['user_id']) }}"><span class="feather icon-edit-2"></span></a>
                                                </div>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </li>
                            @endforeach

                        </ul>


                    </div>
                </div>
            </div>
        </div>
        </div>
        <div class="modal fade text-left" id="info-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel160" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary white">
                        <h5 class="modal-title" id="myModalLabel160">Product Information</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <div class="doc_list">
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>
    @php
        config()->set('static.relation_ships',config('static.relation_ships_buy_for_other'))
    @endphp
    @include('user.user_modal',['hide'=>['birth_cert','nominee_type','allocate_percentage'],'title'=>__('web/policy.buy_for_others'),'show'=>['personal_income','household_income','occ']])

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
        var is_child = false;
        @php
            $age_16 = now()->subYears(16);
        @endphp
        var age_chk = new Date(parseInt('{{$age_16->format('Y')}}'), parseInt('{{$age_16->format('m') - 1}}'), parseInt('{{$age_16->format('d')}}'));

        $("[name=dob]").on("change",function (e) {

            var _dob = $(this).val().split("/");
            var dob = new Date(parseInt(_dob[2]),parseInt(_dob[1]) -1,parseInt(_dob[0]));

            if(dob.getTime() > age_chk.getTime()){
                is_child = true;
                $("[name=email]").parents('.col-12').hide();
            }else{
                is_child = false;
                $("[name=email]").parents('.col-12').show();
            }
        })
        $(".icon-info").on("click",function(e){
            e.preventDefault();
            var out = $(this).parents('.actions-list').find(".documents").html();

            $(".doc_list").html(out);
            $(".modal-title").html("{{__('mobile.policy_information')}}");
            $('#info-modal').modal('show');

        })
        $(".icon-eye").on("click",function(e){
            e.preventDefault();
            var out = $(this).parents('.actions-list').find(".info").html();

            $(".doc_list").html(out);
            $(".modal-title").html("{{__('mobile.policy_owner_details')}}");
            $('#info-modal').modal('show');

        })

        $(".parent").on("click",function (e) {
            if (e.target !== this)
                return;

            $(this).find(".coverages").slideToggle();
            setTimeout(function () {
                $.fn.matchHeight._update();
            },500)

        })
        $(".parent .title").on("click",function (e) {
            $(this).parent(".parent").click();
        })

        var userId = null;
        $("[name=email],[name=passport],[name=dob]").on("change",function(e){
            if(userId != null)
                return;

            if(is_child){
                //email not required
                if($("[name=passport]").val().length == 0 || $("[name=dob]").val().length == 0)
                    return false;
            }else{
                if($("[name=email]").val().length == 0 || $("[name=passport]").val().length == 0 || $("[name=dob]").val().length == 0){
                    return false;
                }
            }
            var email = $("input[name=email]");
            var passport = $("input[name=passport]");
            var dob = $("input[name=dob]");

            $(".loading").show();

            $.post("{{route('wb-api.check-user')}}",{_token:'{{csrf_token()}}',email:$("input[name=email]").val(),dob:$("input[name=dob]").val(),passport:$("input[name=passport]").val(),title:'buy_for_others_title'},function(e){
                $(".loading").hide();

                if(e.status == 'success' && e.data.uuid != ''){

                    Swal.fire({
                        title: '{{__('web/profile.info')}}',
                        html: '{{__('mobile.user_is_already_registered_no_need_to_fill_data')}}',
                        type: 'success',
                        showCancelButton: false,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Ok',
                        confirmButtonClass: 'btn btn-primary',
                        buttonsStyling: false,
                    });
                    userId = e.data.uuid;
                    $(".modal-body").find('.col-12').hide();
                    $(".modal-body").find('.col-md-6').hide();

                    if(!is_child) {
                        $("[name=email]").parents('.col-12').show();
                        $("[name=email]").attr("disabled", true);
                    }

                    $("[name=name]").val(e.data.name);
                    $("[name=name]").parents('.col-12').show();
                    $("[name=name]").attr("disabled",true);

                    $("[name=dob]").parents('.col-12').show();
                    $("[name=dob]").attr("disabled",true);

                    $("[name=passport]").parents('.col-12').show();
                    $("[name=passport]").attr("disabled",true);
                }else if(e.message){
                    Swal.fire({
                        title: '{{__('web/profile.info')}}',
                        html: e.message,
                        type: 'error',
                        showCancelButton: false,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Ok',
                        confirmButtonClass: 'btn btn-primary',
                        buttonsStyling: false,
                    });
                }
            });
        });

        $(".assist_to_buy").on("click",function (e) {
            $(".loading").show();
            $.post("{{route('wb-api.can-pay-for-others')}}",{_token:'{{csrf_token()}}',email:$("input[name=email]").val()},function(e) {
                $(".loading").hide();
                if(e.data == '1'){
                    $("#add-nominee-modal").modal('show');
                }else{
                    var replaceAll = function(val,str1, str2, ignore)
                    {
                        return val.replace(new RegExp(str1.replace(/([\/\,\!\\\^\$\{\}\[\]\(\)\.\*\+\?\|\<\>\-\&])/g,"\\$&"),(ignore?"gi":"g")),(typeof(str2)=="string")?str2.replace(/\$/g,"$$$$"):str2);
                    }


                }
            }).fail(function (e){
                $(".loading").hide();
                Swal.fire({
                    title: '{{__('web/product.error')}}',
                    html: replaceAll(e.responseJSON.data.description,"\n","<br><br>"),
                    type: 'error',
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Ok',
                    confirmButtonClass: 'btn btn-primary',
                    buttonsStyling: false,
                });
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
            var mobile = $("input[name=mobile]");
            var passport_expiry_date = $("input[name=passport_expiry_date]");

            if(userId == null) {
                if (Validation.empty(name)) {
                    Validation.setInvalid(name, '@lang('web/auth.required')');
                    return false;
                }
                if (!Validation.isValidFullName(name)) {
                    Validation.setInvalid(name, '@lang('web/profile.full_name_error')');
                    return false;
                }
                if (Validation.empty(email) && !is_child) {
                    Validation.setInvalid(email, '{{__('web/auth.required')}}');
                    return false;
                }
                if (!Validation.validateEmail(email.val()) && !is_child) {
                    Validation.setInvalid(email, '{{__('web/auth.email_invalid')}}');
                    return false;
                }
                if ((Validation.empty(mobile) || mobile.val().length != 12 ) && !is_child) {
                    Validation.setInvalid(email, '{{__('web/auth.mobile_invalid')}}');
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

                if (Validation.empty(passport) || (isLocal(nationality.val()) && !isValidMykad(passport.val(), false))) {
                    Validation.setInvalid(passport, '@lang('web/auth.required')');
                    return false;
                }
                if ((Validation.empty(passport_expiry_date) || passport_expiry_date.val().length < 10) && !isLocal(nationality.val())) {
                    Validation.setInvalid(passport_expiry_date, '@lang('web/auth.required')');
                    return false;
                }
                if (Validation.empty(dob) || dob.val().length < 10) {
                    Validation.setInvalid(dob, '@lang('web/auth.required')');
                    return false;
                }
            }

            $(".loading").show();
            $.post("{{route('wb-api.buyForOther')}}",{
                uuid:userId,
                _token:'{{csrf_token()}}',
                type:'individual',
                name : name.val(),
                mobile : mobile.val(),
                passport:passport.val(),
                dob:dob.val(),
                nationality:nationality.val(),
                email:email.val(),
                gender:gender.val(),
                occ:job.val(),
                household_income:$("input[name=household_income]").val(),
                personal_income:$("input[name=personal_income]").val()
            },function (e) {
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

        $.get("{{route('wb-api.initPostRegisterIndividual')}}",{},function (res) {
            if(res.status == 'success'){

                var industries = '<option value="">Please Select ...</option>';
                res.data.industries.map((val,i)=>{
                    industries += '<option value="'+val.uuid+'">'+val.name+'</option>';
                })
                $("select[name=industry]").html(industries);
                if($("select[name=industry]").data('value') != '') {
                    val = $("select[name=industry]").data('value');
                    $("select[name=industry]").attr('data-value', '');
                    $("select[name=industry]").val(val).change();
                }else{
                    $(".loading").hide();
                }

                var states = '<option value="">Please Select ...</option>';
                res.data.states.map((val,i)=>{
                    states += '<option value="'+val.uuid+'">'+val.name+'</option>';
                })
                $("select[name=state]").html(states);
                if($("select[name=state]").data('value') != '') {
                    val = $("select[name=state]").data('value');
                    $("select[name=state]").attr('data-value', '');
                    $("select[name=state]").val(val).change();
                }else{
                    $(".loading").hide();
                }
            }
        })

    </script>
    @include('user.user_modal_script',['show'=>['personal_income','household_income','occ']])
@endsection
