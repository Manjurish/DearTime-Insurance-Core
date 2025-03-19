@extends('layouts.contentLayoutMaster')
@section('title', __('web/product.choose_your_benefits'))
@section('content')
<div class="ecommerce-application">
    <form  action="#" class="icons-tab-steps checkout-tab-steps wizard-circle">
        <style>
            .my-1{
                width: 80%;

            }
            .checkout-options-c{
                position: fixed;
                width: 80%;
                bottom: 5px;
                z-index: 999;

            }
            .checkout-options{
                z-index: 999;
            }
        </style>
        <div class="row px-0">

            <div class="col-md-12">
                <div class="checkout-items">
                    <div class=" d-flex justify-content-center align-items-center">
                        <div class="col-md-6">
                            <div class="card ecommerce-card" data-content="medical">
                                <i class="feather icon-info position-absolute mt-1 mr-1 font-size-large" style="right: 0;top:0;"></i>
                                <i class="feather icon-trash-2  position-absolute mt-1 ml-1 font-size-large" style="left: 0;top:0;"></i>
                                <div class="card-content">
                                    <div class="item-name mb-2 mt-2 text-center">
                                        <h1 class="text-center">{{__('web/product.medical')}}</h1>

                                    </div>
                                <div class="item-img text-center  d-flex justify-content-center align-items-center">
                                    <div id="prod-1" style="width: 300px;height: 300px">
                                    </div>
                                </div>
                                <div class="card-body pb-2 mt-0 pt-0">

                                    <h5 class="mb-1  text-center">
                                        <span class="item-description text-center">{{__('web/product.annual_limit')}}</span> : <span data-name="medical"></span>
                                    </h5>
                                    <h5 class="mb-1  text-center">
                                        <span class="item-description text-center ">{{__('web/product.as_low_as')}}</span> : <span data-value="medical" data-type="m"></span><span data-value="medical" data-type="a"></span>
                                    </h5>
                                    <div class="d-flex justify-content-center align-items-center">
                                        <div id="add-remove" class="addMedical" style="width: 50px;height: 50px">
                                        </div>
                                    </div>
                                    <div class="error"></div>
                                    <input type="hidden" name="medical" value="0">

                                </div>

                            </div>
                            </div>
                        </div>
                    </div>
                    <div class="grid-view" style="margin-bottom: auto">


                            <div class="card ecommerce-card" data-content="ci">
                                <i class="feather icon-info position-absolute mt-1 mr-1 font-size-large" style="right: 0;top:0;"></i>
                                <i class="feather icon-trash-2  position-absolute mt-1 ml-1 font-size-large" style="left: 0;top:0;"></i>
                                <div class="card-content">
                                    <div class="item-name mb-2 mt-2 text-center">
                                        <h1 class="text-center">{{__('web/product.ci')}}</h1>

                                    </div>
                                    <div class="item-img text-center  d-flex justify-content-center align-items-center">
                                        <div id="prod-2" style="width: 300px;height: 300px">
                                        </div>
                                    </div>
                                    <div class="card-body pb-2 mt-0 pt-0">
                                        <h5 class="mb-1  text-center">
                                            <span class="item-description text-center">{{__('web/product.max_coverage')}}</span> : <span data-name="ci-c"></span>
                                        </h5>
                                        <div class="d-flex justify-content-around align-items-center">
                                            <h5 class="mb-1  text-center">
                                                <span class="item-description text-center">{{__('web/product.coverage')}}</span> : <span data-name="ci"></span>
                                            </h5>
                                            <h5 class="mb-1  text-center">
                                                <span class="item-description text-center">{{__('web/product.as_low_as')}}</span> : <span data-value="ci" data-type="m"></span><span data-value="ci" data-type="a"></span>
                                            </h5>
                                        </div>

                                        <div class="d-flex justify-content-center align-items-center">
                                            <div class="my-1" id="ci_slider"></div>
                                        </div>
                                        <div class="error"></div>
                                        <input type="hidden" name="ci" value="0">

                                    </div>

                                </div>
                            </div>
                            <div class="card ecommerce-card" data-content="death">
                                <i class="feather icon-info position-absolute mt-1 mr-1 font-size-large" style="right: 0;top:0;"></i>
                                <i class="feather icon-trash-2  position-absolute mt-1 ml-1 font-size-large" style="left: 0;top:0;"></i>
                                <div class="card-content">
                                    <div class="item-name mb-2 mt-2 text-center">
                                        <h1 class="text-center">{{__('web/product.death')}}</h1>
                                    </div>
                                    <div class="item-img text-center  d-flex justify-content-center align-items-center">
                                        <div id="prod-3" style="width: 300px;height: 300px">
                                        </div>
                                    </div>
                                    <div class="card-body pb-2 mt-0 pt-0">
                                        <h5 class="mb-1  text-center">
                                            <span class="item-description text-center">{{__('web/product.max_coverage')}}</span> : <span data-name="death-c"></span>
                                        </h5>
                                        <div class="d-flex justify-content-around align-items-center">
                                            <h5 class="mb-1  text-center">
                                                <span class="item-description text-center">{{__('web/product.coverage')}}</span> : <span data-name="death"></span>
                                            </h5>
                                            <h5 class="mb-1  text-center">
                                                <span class="item-description text-center">{{__('web/product.as_low_as')}}</span> : <span data-value="death" data-type="m"></span><span data-value="death" data-type="a"></span>
                                            </h5>
                                        </div>
                                        <div class="d-flex justify-content-center align-items-center">
                                            <div class="my-1" id="death_slider"></div>
                                        </div>
                                        <div class="error"></div>
                                        <input type="hidden" name="death" value="0">

                                    </div>

                                </div>
                            </div>
                            <div class="card ecommerce-card" data-content="disability">
                                <i class="feather icon-info position-absolute mt-1 mr-1 font-size-large" style="right: 0;top:0;"></i>
                                <i class="feather icon-trash-2  position-absolute mt-1 ml-1 font-size-large" style="left: 0;top:0;"></i>
                                <div class="card-content">
                                    <div class="item-name mb-2 mt-2 text-center">
                                        <h1 class="text-center">{{__('web/product.disability')}}</h1>
                                    </div>
                                    <div class="item-img text-center  d-flex justify-content-center align-items-center">
                                        <div id="prod-4" style="width: 300px;height: 300px">
                                        </div>
                                    </div>
                                    <div class="card-body pb-2 mt-0 pt-0">
                                        <h5 class="mb-1  text-center">
                                            <span class="item-description text-center">{{__('web/product.max_coverage')}}</span> : <span data-name="disability-c"></span>
                                        </h5>
                                        <div class="d-flex justify-content-around align-items-center">
                                            <h5 class="mb-1  text-center">
                                                <span class="item-description text-center">{{__('web/product.coverage')}}</span> : <span data-name="disability"></span>
                                            </h5>
                                            <h5 class="mb-1  text-center">
                                                <span class="item-description text-center">{{__('web/product.as_low_as')}}</span> : <span data-value="disability" data-type="m"></span><span data-value="disability" data-type="a"></span>
                                            </h5>
                                        </div>
                                        <div class="d-flex justify-content-center align-items-center">
                                            <div class="my-1" id="disability_slider"></div>
                                        </div>
                                        <div class="error"></div>
                                        <input type="hidden" name="disability" value="0">

                                    </div>

                                </div>
                            </div>
                            <div class="card ecommerce-card" data-content="accident">
                                <i class="feather icon-info position-absolute mt-1 mr-1 font-size-large" style="right: 0;top:0;"></i>
                                <i class="feather icon-trash-2  position-absolute mt-1 ml-1 font-size-large" style="left: 0;top:0;"></i>
                                <div class="card-content">
                                    <div class="item-name mb-2 mt-2 text-center">
                                        <h1 class="text-center">{{__('web/product.accident')}}</h1>
                                    </div>
                                    <div class="item-img text-center  d-flex justify-content-center align-items-center">
                                        <div id="prod-5" style="width: 300px;height: 300px">
                                        </div>
                                    </div>
                                    <div class="card-body pb-2 mt-0 pt-0">
                                        <h5 class="mb-1  text-center">
                                            <span class="item-description text-center">{{__('web/product.max_coverage')}}</span> : <span data-name="accident-c"></span>
                                        </h5>
                                        <div class="d-flex justify-content-around align-items-center">
                                            <h5 class="mb-1  text-center">
                                                <span class="item-description text-center">{{__('web/product.coverage')}}</span> : <span data-name="accident"></span>
                                            </h5>
                                            <h5 class="mb-1  text-center">
                                                <span class="item-description text-center">{{__('web/product.as_low_as')}}</span> : <span data-value="accident" data-type="m"></span><span data-value="accident" data-type="a"></span>
                                            </h5>
                                        </div>
                                        <div class="d-flex justify-content-center align-items-center">
                                            <div class="my-1" id="accident_slider"></div>
                                        </div>
                                        <div class="error"></div>
                                        <input type="hidden" name="accident" value="0">

                                        <style>
                                            .switch-text-left{
                                                color: #000 !important;
                                            }
                                            .checkout-options{
                                                width: 50%;
                                            }
                                            @media (max-width: 767px) {
                                                .checkout-options{
                                                    width: 100% !important;
                                                }
                                                .checkout-options-c{
                                                    width: 100% !important;
                                                    bottom: 0px !important;
                                                }
                                            }
                                        </style>
                                    </div>

                                </div>
                            </div>
                    </div>
                </div>

            </div>
            <div class="checkout-options-c d-flex justify-content-center align-items-center m-0 p-0">
                <div class="checkout-options box-shadow-1">



                    <div class="card  mb-0">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="row justify-content-center align-items-center">
                                    <div class="col-6">
                                        <div class="d-flex flex-column">
                                            <div class="detail">
                                                <div class="detail-title">
                                                    {{__('web/product.medical')}}
                                                </div>
                                                <div class="detail-amt" >
                                                    <span  data-value="medical" data-type="m"></span>
                                                    <span  data-value="medical" data-type="a"></span>
                                                </div>
                                            </div>
                                            <div class="detail">
                                                <div class="detail-title">
                                                    {{__('web/product.ci')}}
                                                </div>
                                                <div class="detail-amt" >
                                                    <span  data-value="ci" data-type="m"></span>
                                                    <span  data-value="ci" data-type="a"></span>
                                                </div>
                                            </div>
                                            <div class="detail">
                                                <div class="detail-title">
                                                    {{__('web/product.death')}}
                                                </div>
                                                <div class="detail-amt" >
                                                    <span  data-value="death" data-type="m"></span>
                                                    <span  data-value="death" data-type="a"></span>
                                                </div>
                                            </div>
                                            <div class="detail">
                                                <div class="detail-title">
                                                    {{__('web/product.disability')}}
                                                </div>
                                                <div class="detail-amt">
                                                    <span  data-value="disability" data-type="m"></span>
                                                    <span  data-value="disability" data-type="a"></span>
                                                </div>
                                            </div>
                                            <div class="detail">
                                                <div class="detail-title">
                                                    {{__('web/product.accident')}}
                                                </div>
                                                <div class="detail-amt">
                                                    <span  data-value="accident" data-type="m"></span>
                                                    <span  data-value="accident" data-type="a"></span>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="col-6 m-0 p-0">
                                        <div class="row">
                                            <div class="col-md-6 m-0 p-0">
                                                <p class="options-title text-center dark">Frequency</p>
                                                <div class="coupons d-flex pb-1 justify-content-center align-items-center">

                                                    <div class="apply-coupon">
                                                        <div class="custom-control custom-switch switch-lg">
                                                            <input type="checkbox" name="cycle"  value="1" class="custom-control-input" id="cycle">
                                                            <label class="custom-control-label" for="cycle">
                                                                <span class="switch-text-left">{{__('web/product.annually')}}</span>
                                                                <span class="switch-text-right">{{__('web/product.monthly')}}</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 m-0 p-0">
                                                <p class="options-title text-center dark">{{__('web/product.as_low_as')}}</p>
                                                <div class="coupons d-flex justify-content-center align-items-center">
                                                    <div class="apply-coupon">
                                                        <div class="detail-amt total-amt ">0</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

@endsection
@section('mystyle')
    <link rel="stylesheet" type="text/css" href="{{asset('css/pages/app-ecommerce-shop.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('vendors/css/extensions/nouislider.min.css')}}">
@endsection
@section('myscript')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.5.9/lottie.min.js" type="text/javascript"></script>
    <script src="{{asset('vendors/js/extensions/nouislider.min.js')}}" type="text/javascript"></script>
    <script>
        var prod_1 = bodymovin.loadAnimation({container: document.getElementById('prod-1'), path: '{{asset('products/1_o.json')}}', renderer: 'canvas', loop: true, autoplay: true});
        var prod_2 = bodymovin.loadAnimation({container: document.getElementById('prod-2'), path: '{{asset('products/2_o.json')}}', renderer: 'canvas', loop: true, autoplay: true});
        var prod_3 = bodymovin.loadAnimation({container: document.getElementById('prod-3'), path: '{{asset('products/3_o.json')}}', renderer: 'canvas', loop: true, autoplay: true});
        var prod_4 = bodymovin.loadAnimation({container: document.getElementById('prod-4'), path: '{{asset('products/4_o.json')}}', renderer: 'canvas', loop: true, autoplay: true});
        var prod_5 = bodymovin.loadAnimation({container: document.getElementById('prod-5'), path: '{{asset('products/5_o.json')}}', renderer: 'canvas', loop: true, autoplay: true});
        var add_remove = bodymovin.loadAnimation({container: document.getElementById('add-remove'), path: '{{asset('products/add_remove.json')}}', renderer: 'svg', loop: false, autoplay: false});
    </script>
    <script>
        $(".addMedical").on("click",function (e) {
            var val = $("input[name=medical]").val();
            if(val == '1'){
                //set to disable
                $("input[name=medical]").val("0");
                // $(".addMedical").html("Purchase");

                add_remove.playSegments([140,195],true);
            }else{
                //set to enable
                $("input[name=medical]").val("1");
                // $(".addMedical").html("Remove");
                add_remove.playSegments([50,110],true);
            }
            loadData(true,"medical");
        })
    </script>
    <script>
        var ci = $("input[name=ci]").val();
        var ci_slider = document.getElementById('ci_slider');
        noUiSlider.create(ci_slider, {
            start: 0,
            connect: [true, false],
            behaviour: 'drag',
            range: {
                'min': 0,
                'max': 100,
            }
        });
        ci_slider.noUiSlider.on('update', function (values, handle) {
            var value = Math.round(values[0]);
            $("input[name=ci]").val(value);
            $("span[data-name=ci]").html(value.toLocaleString());
        });
        ci_slider.noUiSlider.set([ci]);
    </script>

    <script>
        var death = $("input[name=death]").val();
        var death_slider = document.getElementById('death_slider');
        noUiSlider.create(death_slider, {
            start: 0,
            connect: [true, false],
            behaviour: 'drag',
            range: {
                'min': 0,
                'max': 100,
            }
        });
        death_slider.noUiSlider.on('update', function (values, handle) {
            var value = Math.round(values[0]);
            $("input[name=death]").val(value);
            $("span[data-name=death]").html(value.toLocaleString());
        });
        death_slider.noUiSlider.set([death]);
    </script>

    <script>
        var disability = $("input[name=disability]").val();
        var disability_slider = document.getElementById('disability_slider');
        noUiSlider.create(disability_slider, {
            start: 0,
            connect: [true, false],
            behaviour: 'drag',
            range: {
                'min': 0,
                'max': 100,
            }
        });
        disability_slider.noUiSlider.on('update', function (values, handle) {
            var value = Math.round(values[0]);
            $("input[name=disability]").val(value);
            $("span[data-name=disability]").html(value.toLocaleString());
        });
        disability_slider.noUiSlider.set([disability]);
    </script>

    <script>
        var accident = $("input[name=accident]").val();
        var accident_slider = document.getElementById('accident_slider');
        noUiSlider.create(accident_slider, {
            start: 0,
            connect: [true, false],
            behaviour: 'drag',
            range: {
                'min': 0,
                'max': 100,
            }
        });
        accident_slider.noUiSlider.on('update', function (values, handle) {
            var value = Math.round(values[0]);
            $("input[name=accident]").val(value);
            $("span[data-name=accident]").html(value.toLocaleString());
        });
        accident_slider.noUiSlider.set([accident]);
    </script>
    <script>
        var total_anually = 0;
        var total_monthly = 0;
        function changeCycle(){
            cycle = $("input[name=cycle]:checked").length == '1' ? 'a' : 'm';
            if(cycle == 'm'){
                $("span[data-type=a]").hide();
                $("span[data-type=m]").show();
                $(".total-amt").html(total_monthly.toLocaleString());
            }else{
                $("span[data-type=a]").show();
                $("span[data-type=m]").hide();
                $(".total-amt").html(total_anually.toLocaleString());
            }

        }
        $("input[name=cycle]").on("change",function(e){
            changeCycle();
        });
        $(document).ready(function () {
            loadData();
            ci_slider.noUiSlider.on('change', function (values, handle) {loadData(true,"ci");});
            accident_slider.noUiSlider.on('change', function (values, handle) {loadData(true,"accident");});
            disability_slider.noUiSlider.on('change', function (values, handle) {loadData(true,"disability");});
            death_slider.noUiSlider.on('change', function (values, handle) {loadData(true,"death");});

        });

        function loadData(withPayload = false,changed){
            total_anually = 0;
            total_monthly = 0;

            if(!withPayload) {
                $(".loading").show();

            }else{
                $("span[data-value="+changed+"]").html('<div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div>')
            }

            var data = {_token:'{{csrf_token()}}'};
            if(withPayload){
                data=[
                    {name:'Medical',coverage:parseFloat($('input[name=medical]').val())},
                    {name:'Critical Illness',coverage:parseFloat($('input[name=ci]').val())},
                    {name:'Accident',coverage:parseFloat($('input[name=accident]').val())},
                    {name:'Disability',coverage:parseFloat($('input[name=disability]').val())},
                    {name:'Disability',coverage:parseFloat($('input[name=disability]').val())},
                    {name:'Death',coverage:parseFloat($('input[name=death]').val())},
                ];
                data = {mode:'update',payload:data,_token:'{{csrf_token()}}'};
            }

            $.ajax({
                type: "POST",
                url: "{{route('wb-api.getproducts')}}",
                data: JSON.stringify(data),
                processData: false,
                contentType:"application/json",
                success:function (res) {

                    if(res.status != 'success')
                        return alert('loadingError');
                    res = res.data;

                    var medical = res.find(x=>x.name == 'Medical');
                    if(medical.options.allowed.toString() == 'false'){
                        $(".addMedical").hide();
                        $("div[data-content=medical]").find(".error").html("<p class='danger mt-1 text-center'>"+medical.options.message+"</p>")
                        $("[data-content=medical] h5").hide();
                    }else{
                        $(".addMedical").show();
                        $("div[data-content=medical]").find(".error").html("");
                        $("[data-content=medical] h5").show();
                    }
                    $("span[data-name=medical]").html(medical.options.max_coverage.toLocaleString());

                    if(medical.options.coverage == '1') {
                        $("span[data-value=medical][data-type=a]").html(Math.abs(medical.options.annually).toLocaleString());
                        $("span[data-value=medical][data-type=m]").html(Math.abs(medical.options.monthly).toLocaleString());
                        $(".detail span[data-value=medical]").parents(".detail").show();

                        total_anually += parseFloat(medical.options.annually);
                        total_monthly += parseFloat(medical.options.monthly);
                    }else{

                        $(".detail span[data-value=medical]").parents(".detail").hide();
                        $("span[data-value=medical][data-type=a]").html("0");
                        $("span[data-value=medical][data-type=m]").html("0");
                    }

                    //ci
                    var ci = res.find(x=>x.name == 'Critical Illness');
                    if(ci.options.allowed.toString() == 'false'){
                        $("#ci_slider").hide();
                        $("div[data-content=ci]").find(".error").html("<p class='danger mt-1 text-center'>"+ci.options.message+"</p>")
                        $("[data-content=ci] h5").hide();
                    }else{
                        $("#ci_slider").show();
                        $("div[data-content=ci]").find(".error").html("");
                        $("[data-content=ci] h5").show();
                    }
                    if(ci.options.coverage > 1)
                        $(".detail span[data-value=ci]").parents(".detail").show();
                    else
                        $(".detail span[data-value=ci]").parents(".detail").hide();

                    $("span[data-value=ci][data-type=a]").html(Math.abs(ci.options.annually).toLocaleString());
                    $("span[data-value=ci][data-type=m]").html(Math.abs(ci.options.monthly).toLocaleString());
                    $("span[data-name=ci-c]").html(Math.abs(ci.options.max_coverage).toLocaleString());
                    total_anually += parseFloat(ci.options.annually);
                    total_monthly += parseFloat(ci.options.monthly);
                    ci_slider.noUiSlider.updateOptions({range: {'min': 0, 'max': parseFloat(ci.options.max_coverage) == 0 ? 1 : parseFloat(ci.options.max_coverage),}}, true);
                    ci_slider.noUiSlider.set([parseFloat(ci.options.coverage)]);


                    //accident
                    var accident = res.find(x=>x.name == 'Accident');
                    if(accident.options.allowed.toString() == 'false'){
                        $("#accident_slider").hide();
                        $("div[data-content=accident]").find(".error").html("<p class='danger mt-1 text-center'>"+accident.options.message+"</p>");
                        $("[data-content=accident] h5").hide();
                    }else{
                        $("#accident_slider").show();
                        $("div[data-content=accident]").find(".error").html("");
                        $("[data-content=accident] h5").show();
                    }
                    if(accident.options.coverage > 1)
                        $(".detail span[data-value=accident]").parents(".detail").show();
                    else
                        $(".detail span[data-value=accident]").parents(".detail").hide();

                    $("span[data-value=accident][data-type=a]").html(Math.abs(accident.options.annually).toLocaleString());
                    $("span[data-value=accident][data-type=m]").html(Math.abs(accident.options.monthly).toLocaleString());
                    $("span[data-name=accident-c]").html(Math.abs(accident.options.max_coverage).toLocaleString());
                    total_anually += parseFloat(accident.options.annually);
                    total_monthly += parseFloat(accident.options.monthly);
                    accident_slider.noUiSlider.updateOptions({range: {'min': 0, 'max': parseFloat(accident.options.max_coverage) == 0 ? 1 : parseFloat(accident.options.max_coverage),}}, true);
                    accident_slider.noUiSlider.set([parseFloat(accident.options.coverage)]);


                    //disability
                    var disability = res.find(x=>x.name == 'Disability');
                    if(disability.options.allowed.toString() == 'false'){
                        $("#disability_slider").hide();
                        $("div[data-content=disability]").find(".error").html("<p class='danger mt-1 text-center'>"+disability.options.message+"</p>");
                        $("[data-content=disability] h5").hide();
                    }else{
                        $("#disability_slider").show();
                        $("div[data-content=disability]").find(".error").html("");
                        $("[data-content=disability] h5").show();
                    }
                    if(disability.options.coverage > 1)
                        $(".detail span[data-value=disability]").parents(".detail").show();
                    else
                        $(".detail span[data-value=disability]").parents(".detail").hide();

                    $("span[data-value=disability][data-type=a]").html(Math.abs(disability.options.annually).toLocaleString());
                    $("span[data-value=disability][data-type=m]").html(Math.abs(disability.options.monthly).toLocaleString());
                    $("span[data-name=disability-c]").html(Math.abs(disability.options.max_coverage).toLocaleString());
                    total_anually += parseFloat(disability.options.annually);
                    total_monthly += parseFloat(disability.options.monthly);
                    disability_slider.noUiSlider.updateOptions({range: {'min': 0, 'max': parseFloat(disability.options.max_coverage) == 0 ? 1 : parseFloat(disability.options.max_coverage),}}, true);
                    disability_slider.noUiSlider.set([parseFloat(disability.options.coverage)]);


                    //death
                    var death = res.find(x=>x.name == 'Death');
                    if(death.options.allowed.toString() == 'false'){
                        $("#death_slider").hide();
                        $("div[data-content=death]").find(".error").html("<p class='danger mt-1 text-center'>"+death.options.message+"</p>");
                        $("[data-content=death] h5").hide();
                    }else{
                        $("#death_slider").show();
                        $("div[data-content=death]").find(".error").html("");
                        $("[data-content=death] h5").show();

                    }
                    if(death.options.coverage > 1)
                        $(".detail span[data-value=death]").parents(".detail").show();
                    else
                        $(".detail span[data-value=death]").parents(".detail").hide();

                    $("span[data-value=death][data-type=a]").html(Math.abs(death.options.annually).toLocaleString());
                    $("span[data-value=death][data-type=m]").html(Math.abs(death.options.monthly).toLocaleString());
                    $("span[data-name=death-c]").html(Math.abs(death.options.max_coverage).toLocaleString());
                    total_anually += parseFloat(death.options.annually);
                    total_monthly += parseFloat(death.options.monthly);
                    death_slider.noUiSlider.updateOptions({range: {'min': 0, 'max': parseFloat(death.options.max_coverage) == 0 ? 1 : parseFloat(death.options.max_coverage),}}, true);
                    death_slider.noUiSlider.set([parseFloat(death.options.coverage)]);

                    changeCycle();
                    $(".loading").hide();

                    $(".grid-view").css("margin-bottom",$(".checkout-options").height());
                }
            });
        }
    </script>
@endsection
