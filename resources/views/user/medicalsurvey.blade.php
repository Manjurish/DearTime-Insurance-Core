@extends('layouts.contentLayoutMaster')
@section('title', __('web/medicalsurvey.medical_survey').' '.($title ?? ''))
@section('content')
    <section id="basic-examples">
        @if (session()->has('info') )

            <div class="alert alert-info">
                {!! session()->get('info') !!}
            </div>

        @endif
        <form id="surveyForm" action="{{route('userpanel.MedicalSurvey.index')}}" method="post">
            <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title"></h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <div id="surveyQuestion">

                            </div>
                            <style>
                                .checkbox .invalid-feedback {
                                    display: block;
                                }
                            </style>
                            <fieldset class="checkbox mt-1">
                                <div class="vs-checkbox-con vs-checkbox-primary acceptChk">
                                    <input type="checkbox" name="accept" value="1">
                                    <span class="vs-checkbox">
                                        <span class="vs-checkbox--check">
                                            <i class="vs-icon feather icon-check"></i>
                                        </span>
                                    </span>
                                    <span class="">{{__('web/medicalsurvey.verify_checkbox')}}</span>
                                </div>
                            </fieldset>
                            <div class="form-group my-2">
                                <button type="submit" class="btn btn-primary storeBtn">
                                    {{__('web/medicalsurvey.save')}}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
            <div class="card">
            <div class="card-header">
                <h4 class="card-title"></h4>
            </div>
            <div class="card-content">
                <div class="card-body">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="w-100 my-1 d-flex justify-content-center align-items-center flex-column">
                                    <input name="height" data-linecap=round type="text" data-thickness=".15" value="100" data-fgColor="#000" class="dial height"  data-min="40" data-max="240" data-angleArc=250 data-angleOffset=-125>
                                    <h4 style="margin-top: -55px">{{__('web/medicalsurvey.current_height')}}</h4>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="w-100 my-1 d-flex justify-content-center align-items-center flex-column">
                                    <input name="weight" data-linecap=round data-step=".1" type="text" data-thickness=".15" value="40" data-fgColor="#000" class="dial weight" data-min="2" data-max="150" data-angleArc=250 data-angleOffset=-125>
                                    <h4 style="margin-top: -55px">{{__('web/medicalsurvey.current_weight')}}</h4>
                                </div>
                            </div>

                        </div>

                        <div class="col-12">

                            <div class="custom-control custom-switch switch-lg custom-switch-success mr-2 mb-1 smoke">
                                <h4 class="mb-0">{{__('web/medicalsurvey.how_many_cigarettes')}} (<span id="smoke_value">1</span>)</h4>
                                <input type="hidden" name="smoke"  value="1">
                                <div class="row m-2 d-flex justify-content-start">
                                    <div class="col-9 m-0 p-0"><div class="my-1" id="smoke_slider"></div></div>
                                    <div class="col-2 m-0 p-0 d-flex justify-content-start "><img class="cFilter" src="{{asset('images/cigarette/c-filter.png')}}"> </div>

                                </div>

                            </div>
                        </div>
                </div>
            </div>
        </div>
        </div>
        </div>
        </form>

    </section>
    <div class="modal fade text-left" id="change-value-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel160" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xs modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary white">
                    <h5 class="modal-title" id="myModalLabel160">Change Coverage</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="change-value-form">
                    <div class="modal-body">
                        <label class="text-center">Please Enter a value</label>

                        <div class="form-group m-1">
                            <input autocomplete="off" name="this-value" type="text" placeholder="Value" class="form-control text-center">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary submit-data">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
@section('mystyle')
    <link rel="stylesheet" type="text/css" href="{{asset('vendors/css/extensions/nouislider.min.css')}}">
    <style>

        .noUi-connect{
            background: url('{{asset('images/cigarette/c-pipe.png')}}');
            height : 120px;
        }
        .noUi-connects{
            height: 50px;
            border-radius: 0px !important;
        }
        .noUi-base, .noUi-connects{
            top: -7px;
            width: 100% !important;
        }
        .noUi-target{
            border: 0px !important;
            border-radius: 0px !important;
            box-shadow: none !important;
            background: #fff;
        }
        .noUi-handle{
            border: 0px !important;
            border-radius: 0px !important;
            box-shadow: none !important;
        }
        .noUi-horizontal .noUi-handle{
            top: -13px !important;
            height: 61px !important;
            background: url('{{asset('images/cigarette/c-fire.png')}}');
            background-size: cover;
        }
        .answer{
            width: auto;
            padding: 7px;
            border: 1px solid #ccc;
            border-radius: 40px;
            margin: 4px;
            cursor: pointer;
            flex-grow: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .selected-ans{
            border: 2px solid #000;
            margin: 3px;
            background-color: #000;
            color: #fff;
        }
        .detail_info {
            margin-left: 5px;
        }
        .section_part .invalid-feedback{
            display: block;
        }
        .answer-box{
            display: flex;
            justify-content: flex-start;
            flex-wrap: wrap;
        }

        @media (max-width: 767px) {
            .cFilter{
                height: 30px !important;
            }
            .noUi-connect{
                height: 30px !important;
                background-size: contain;
            }
            .noUi-horizontal .noUi-handle{
                top: -11px !important;
                height: 37px !important;
                background-size: contain !important;
                background-position: right !important;
                background-repeat: no-repeat !important;

            }
            .smoke h4{
                width: 120%;
            }
        }
    </style>
@endsection
@section('myscript')
    <script src="{{asset('vendors/js/extensions/nouislider.min.js')}}"></script>
    <script src="{{asset('js/scripts/jquery.knob.min.js')}}"></script>

    <script>


        $("body").on("click",".answer",function (e) {

            if($(this).find('span').html() == '{{__('web/medicalsurvey.none')}}' || $(this).find('span').html() == '{{__('web/medicalsurvey.no')}}' ){
                $(this).parent().find(".answer").removeClass("selected-ans");
            }else{
                $(this).parent().find(":contains('{{__('web/medicalsurvey.none')}}')").removeClass("selected-ans");
                $(this).parent().find(":contains('{{__('web/medicalsurvey.no')}}')").removeClass("selected-ans");
            }
            $(this).toggleClass("selected-ans");
        });
        $("body").on("click",".detail_info",function(e){
            e.preventDefault();
            e.stopImmediatePropagation();
            var text = $(this).parent('.answer').data('info');
            $(this).removeClass("selected-ans");
            showDetails(text)
        });

        $(document).ready(function () {

            $('#height_value,#weight_value').on("click",function(e){
                var value = $(this).parent().find("input");

                $("[name=this-value]").val(value.val());
                var cid = '';
                if($(this).attr('id') == 'height_value'){
                    cid = 'height';
                }else if($(this).attr('id') == 'weight_value'){
                    cid = 'weight';
                }
                $("[name=this-value]").attr("data-cid",cid);

                $("#change-value-modal").modal()
            })
            $(".submit-data").on("click",function(e){
                e.preventDefault();
                $("#change-value-modal").modal('hide')

                var cid = $("[name=this-value]").attr("data-cid");
                var val = $("[name=this-value]").val();
                console.log(cid,val);
                $("."+cid).parent().find("input").val(val).trigger("change");
                $("#"+cid+"_value").html(val);

            })

            @if(!$hasUnderWriting)
            Swal.fire({
                title: "{{__('web/medicalsurvey.ms_choose')}}",
                text: "{{__('web/medicalsurvey.ms_choose_desc')}}",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '{{__('web/medicalsurvey.myself')}}',
                cancelButtonText: '{{__('web/medicalsurvey.nearby_clinic')}}',
                confirmButtonClass: 'btn btn-primary',
                cancelButtonClass: 'btn btn-primary ml-1',
                buttonsStyling: false,
            }).then(function (result) {
                if (result.value) {
                    getUnderwritingDetail();
                } else {
                    window.location = '?fill-by-clinic';
                }
            });
            @else
                getUnderwritingDetail();
            @endif



        });
        function getUnderwritingDetail() {
            $(".loading").show();
            $.post("{{route('wb-api.getunderwritings')}}",{_token:'{{csrf_token()}}',user_id:'{{$uid}}',fill_type:'{{$fill_type}}'},function (res) {
                var height = res.data.height || 180;
                var weight = res.data.weight || 80;
                var smoke = res.data.smoke || 0;

                //$('#weight_value').html(weight);
                $('input[name=weight]').val(weight).trigger('change');
                changeWeight(weight);

                $('#height_value').html(height);
                $('input[name=height]').val(height).trigger('change');
                changeHeight(height);


                smoke_slider.noUiSlider.set([smoke]);



                Object.values(res.data.questions).map((val, i) => {
                    var count = 0;
                    $("#surveyQuestion").append("<div class='section_part' data-id='"+(i)+"'><span class='error'></span></div>");
                    $(".section_part[data-id='"+i+"']").append("<h4 class='my-2'>"+val.title+"</h4><div class='answer-box'></div>");
                    val.questions.map((vall, ii) => {
                        var info = vall.info || '';
                        if (vall.value != null && vall.value.toString() == 'true') {
                            count++;
                            $(".section_part[data-id='"+i+"'] .answer-box").append("<div class='answer selected-ans' data-section='"+(i)+"' data-id='"+vall.id+"' data-info='"+info+"'><span class='detail_text'>"+vall.title+"</span></div>");
                        }else{
                            $(".section_part[data-id='"+i+"'] .answer-box").append("<div class='answer' data-section='"+(i)+"'  data-id='"+vall.id+"'  data-info='"+info+"'><span class='detail_text'>"+vall.title+"</span></div>");
                        }
                    });
                    {{--if(count == 0){--}}
                    {{--    $(".section_part[data-id='"+i+"'] .answer-box").find("div:contains('{{__('web/medicalsurvey.none')}}')").addClass("selected-ans");--}}
                    {{--    $(".section_part[data-id='"+i+"'] .answer-box").find(":contains('{{__('web/medicalsurvey.no')}}')").addClass("selected-ans");--}}
                    {{--}--}}

                });

                $(".answer:not(.answer[data-info=''])").append("<span class='detail_info fa fa-info-circle'></span> ");

                $(".loading").hide();
            })
        }
        var changeWeight = function (v) {

            if(Math.round(v) == '150') {
                return $("#weight_value").html(">= 150");
            }
            if(v == '2') {
                return $("#weight_value").html("<= 2.0");
            }
            if(v > 10){
                $("#weight_value").html(Math.round(v));
                $('.weight').trigger(
                    'configure',
                    {
                        "step":1,
                    }
                );

            }else{
                $("#weight_value").html(Math.round(v * 10) / 10);
                $('.weight').trigger(
                    'configure',
                    {
                        "step":0.1,
                    }
                );

            }
        };
        $(".weight").knob({
            'displayInput':false,
            'change' : changeWeight
        });
        $("input[name=weight]").parent('div').append("<span id='weight_value' style='width: 104px; height: 66px; position: absolute; vertical-align: middle; margin-top: 66px; margin-left: -152px; border: 0px; background: none; font: bold 30px Arial; text-align: center; color: rgb(0, 0, 0); padding: 0px; -webkit-appearance: none;'>0</span>")

        var changeHeight = function (v) {
            v = Math.round(v);

            if(v == '40') {
                $("#height_value").html("<= 40");
            }else if(v == '240') {
                $("#height_value").html(">= 240");
            }else{
                $("#height_value").html(v);
            }
        };
        $(".height").knob({
            'displayInput':false,
            'change' : changeHeight
        });
        $("input[name=height]").parent('div').append("<span id='height_value' style='width: 104px; height: 66px; position: absolute; vertical-align: middle; margin-top: 66px; margin-left: -152px; border: 0px; background: none; font: bold 30px Arial; text-align: center; color: rgb(0, 0, 0); padding: 0px; -webkit-appearance: none;'>0</span>")



        var smoke = $("input[name=smoke]").val();

        var smoke_slider = document.getElementById('smoke_slider');
        noUiSlider.create(smoke_slider, {
            start: 0,
            behaviour: 'drag',
            connect: [false, true],
            range: {
                'min': 0,
                'max': 31
            },

        });
        smoke_slider.noUiSlider.on('update', function (values, handle) {
            var value = Math.round(values[0]);
            $("#smoke_value").html(value == 31 ? '30+' : value);
            $("input[name=smoke]").val(value);
        });
        smoke_slider.noUiSlider.set([smoke]);

        function showDetails(details){
            Swal.fire({
                title: 'Information',
                text: details,
                type: 'info',
                showCancelButton: false,
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Ok',
                confirmButtonClass: 'btn btn-primary',
                buttonsStyling: false,
            });
        }
        $("#surveyForm").on("submit",function (e) {
            Validation.clearAllValidation();
            var data = {};
            data.weight = $("input[name=weight]").val();
            data.height = $("input[name=height]").val();
            data.smoke = $("input[name=smoke]").val();


            var sec1 = $(".selected-ans[data-section=0]");
            var sec2 = $(".selected-ans[data-section=1]");
            var sec3 = $(".selected-ans[data-section=2]");
            var sec4 = $(".selected-ans[data-section=3]");
            var sec5 = $(".selected-ans[data-section=4]");
            var sec6 = $(".selected-ans[data-section=5]");
            var accept = $(".acceptChk");
            if(sec1.length == 0) {
                var sec1_h3 = $(".section_part[data-id=0] .error");
                Validation.setInvalid(sec1_h3,"{{__('web/medicalsurvey.select_error')}}");
                return false;
            }
            if(sec2.length == 0) {
                var sec2_h3 = $(".section_part[data-id=1] .error");
                Validation.setInvalid(sec2_h3,"{{__('web/medicalsurvey.select_error')}}");
                return false;
            }
            if(sec3.length == 0) {
                var sec3_h3 = $(".section_part[data-id=2] .error");
                Validation.setInvalid(sec3_h3,"{{__('web/medicalsurvey.select_error')}}");
                return false;
            }
            if(sec4.length == 0) {
                var sec4_h3 = $(".section_part[data-id=3] .error");
                Validation.setInvalid(sec4_h3,"{{__('web/medicalsurvey.select_error')}}");
                return false;
            }
            if(sec5.length == 0) {
                var sec5_h3 = $(".section_part[data-id=4] .error");
                Validation.setInvalid(sec5_h3,"{{__('web/medicalsurvey.select_error')}}");
                return false;
            }
            if(sec6.length == 0) {
                var sec6_h3 = $(".section_part[data-id=5] .error");
                Validation.setInvalid(sec6_h3,"{{__('web/medicalsurvey.select_error')}}");
                return false;
            }
            if($("input[name=accept]:checked").length == 0){
                Validation.setInvalid(accept,"{{__('web/medicalsurvey.accept_error')}}");
                return false;
            }

            var answers = [];
            $(".selected-ans").each(function( index ) {
                answers.push(parseInt($(this).data('id').toString()));
            });

            data.answers = answers;
            data = {payload:data,_token:'{{csrf_token()}}',user_id:'{{$uid}}',fill_type:'{{$fill_type}}'};
            $(".storeBtn").html('<div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div>');
            $(".loading").show();
            $.ajax({
                type: "POST",
                url: "{{route('wb-api.getunderwritings')}}",
                data: JSON.stringify(data),
                processData: false,
                contentType: "application/json",
                success: function (res) {
                    let next_page = res.data.next_page_url;
                    $(".loading").hide();
                    Swal.fire({
                        title: 'Information',
                        text: '{{__('web/medicalsurvey.save_success')}}',
                        type: 'success',
                        showCancelButton: false,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Ok',
                        confirmButtonClass: 'btn btn-primary',
                        buttonsStyling: false,
                    }).then(function(res2){
                        if(res.data.next_page_url){
                            return window.location = res.data.next_page_url;
                        }
                        @if(request()->has('mn'))
                            window.location = '';
                        @else
                            window.location = '{{asset('user/go/')}}'+'/'+res.data.next_page;
                        @endif

                    });


                }
            });

            return false;

        });
    </script>
@endsection
