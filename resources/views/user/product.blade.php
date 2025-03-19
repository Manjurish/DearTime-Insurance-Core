@extends('layouts.contentLayoutMaster')
@section('title', __('web/product.choose_your_benefits').' '.($title ?? ''))
@section('content')
    <div class="ecommerce-application">
        <form  action="#" class="icons-tab-steps checkout-tab-steps wizard-circle">
            <div class="row px-0">

                <div class="col-lg-8">
                    <div class="checkout-items">
                        <div class=" d-flex justify-content-center align-items-center">
                            <div class="col-md-6">

                            </div>
                        </div>
                        <div class="grid-view" style="margin-bottom: auto">

                            <div class="card ecommerce-card" data-content="medical">
                                <i class="feather icon-info position-absolute mt-1 mr-1 font-size-large" data-product="Medical" style="right: 0;top:0;"></i>
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
                                            <span class="item-description text-center">{{__('web/product.max_coverage')}}</span> : <span data-name="medical-c"></span>
                                        </h5>
                                        <div class="d-flex justify-content-around align-items-center">
                                            <h5 class="mb-1  text-center"  data-product="medical">
                                                <span class=" coverage-selector">
                                                    <span class="item-description text-center ">{{__('web/product.deductible')}}  </span> : <span data-name="medical"></span>
                                                </span>
                                                <i class="fa fa-info-circle deductible-info"></i>
                                            </h5>

                                            <h5 class="mb-1  text-center">
                                                <span class="item-description text-center">{{__('web/product.as_low_as')}}</span> : <span data-value="medical" data-type="m"></span><span data-value="medical" data-type="a"></span>
                                            </h5>
                                        </div>

                                        <div class="d-flex justify-content-center align-items-center">
                                            <div class="my-1" id="medical_slider"></div>
                                        </div>
                                        <div class="error"></div>
                                        <input type="hidden" name="medical" value="0">

                                    </div>

                                </div>
                            </div>
                            <div class="card ecommerce-card" data-content="ci">
                                <i class="feather icon-info position-absolute mt-1 mr-1 font-size-large"  data-product="Critical Illness" style="right: 0;top:0;"></i>
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
                                            <h5 class="mb-1  text-center coverage-selector"  data-product="ci">
                                                <span class="item-description text-center ">{{__('web/product.coverage')}}</span> : <span data-name="ci"></span>
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
                                <i class="feather icon-info position-absolute mt-1 mr-1 font-size-large"  data-product="Death" style="right: 0;top:0;"></i>
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
                                            <h5 class="mb-1  text-center coverage-selector"  data-product="death">
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
                                <i class="feather icon-info position-absolute mt-1 mr-1 font-size-large"  data-product="Disability" style="right: 0;top:0;"></i>
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
                                            <h5 class="mb-1  text-center coverage-selector"  data-product="disability">
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
                                <i class="feather icon-info position-absolute mt-1 mr-1 font-size-large" data-product="Accident" style="right: 0;top:0;"></i>
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
                                            <h5 class="mb-1  text-center coverage-selector" data-product="accident">
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

                                        </style>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="col-lg-4">
                    <div class="checkout-options" style="">
                        <div class="card">
                            <div class="card-content">
                                <div class="card-body">
                                    <p class="options-title">{{__('web/product.quick_quote')}}</p>
                                    <div class="coupons">
                                        <div class="coupons-title">
                                            <p>{{__('web/product.payment_term')}}</p>
                                        </div>
                                        <div class="apply-coupon">
                                            <div class="custom-control custom-switch switch-lg mr-2 mb-1">
                                                <input type="checkbox" name="cycle"  value="1" class="custom-control-input" id="cycle">
                                                <label class="custom-control-label" for="cycle">
                                                    <span class="switch-text-left">{{__('web/product.annually')}}</span>
                                                    <span class="switch-text-right">{{__('web/product.monthly')}}</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="price-details">
                                        <p>Price Details</p>

                                        <div class="detail" data-id="medical">
                                            <div class="detail-title">
                                                {{__('web/product.medical')}}
                                            </div>
                                            <div class="detail-amt">
                                                <span data-value="medical" data-type="a"></span>
                                                <span data-value="medical" data-type="m"></span>
                                            </div>
                                        </div>
                                        <div class="detail" data-id="ci">
                                            <div class="detail-title">
                                                {{__('web/product.ci')}}
                                            </div>
                                            <div class="detail-amt">
                                                <span data-value="ci" data-type="a"></span>
                                                <span data-value="ci" data-type="m"></span>
                                            </div>
                                        </div>
                                        <div class="detail" data-id="death">
                                            <div class="detail-title">
                                                {{__('web/product.death')}}
                                            </div>
                                            <div class="detail-amt">
                                                <span data-value="death" data-type="a"></span>
                                                <span data-value="death" data-type="m"></span>
                                            </div>
                                        </div>
                                        <div class="detail" data-id="disability">
                                            <div class="detail-title">
                                                {{__('web/product.disability')}}
                                            </div>
                                            <div class="detail-amt">
                                                <span data-value="disability" data-type="a"></span>
                                                <span data-value="disability" data-type="m"></span>
                                            </div>
                                        </div>
                                        <div class="detail" data-id="accident">
                                            <div class="detail-title">
                                                {{__('web/product.accident')}}
                                            </div>
                                            <div class="detail-amt">
                                                <span data-value="accident" data-type="a"></span>
                                                <span data-value="accident" data-type="m"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="detail">
                                        <div class="detail-title detail-total">{{__('web/product.as_low_as')}}</div>
                                        <div class="detail-amt total-amt">0</div>
                                    </div>
                                    <div class="btn btn-primary btn-block place-order">PLACE ORDER</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="modal fade text-left" id="info-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel160" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xs modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary white">
                    <h5 class="modal-title" id="myModalLabel160">Product Information</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <div class="doc_list">
                        @foreach(config('static.product_documents') as $doc)
                            <div class="p-1 m-1" style="border: 1px solid #ccc;border-radius: 10px"><a target="_blank" href="{{$doc['link']}}">{{__('mobile.'.$doc['title'])}}</a> </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
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
                        <label class="text-center">Please Enter a value between <span id="min-coverage">0</span> to <span id="max-coverage">8</span></label>

                        <div class="form-group m-1">
                            <input autocomplete="off" name="coverage-value" type="text" placeholder="Value" class="form-control text-center">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary verify-phone">Change</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
@section('mystyle')
    <link rel="stylesheet" type="text/css" href="{{asset('css/pages/app-ecommerce-shop.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('vendors/css/extensions/nouislider.min.css')}}">
    <style>
        .my-1{
            width: 80%;

        }
        .checkout-options{
            position: fixed;
            width: 30%;
            top: 150px;
            z-index: 990;
            right: 30px;

        }
        @media (max-width: 992px) {
            .checkout-options{
                position: fixed;
                width: 100%;
                bottom: 0px;
                top: unset !important;
                z-index: 999;
                right: 0px !important;
            }
            .checkout-items{
                margin-bottom: 100px;
            }
            .col-lg-4{
                margin: 0 !important;
                padding: 0 !important;
            }
            .checkout-options .card {
                margin: 0px !important;
            }
            .checkout-options hr {
                display: none !important;
            }
            .checkout-options .options-title {
                display: none !important;
            }
            .checkout-options .price-details{
                display: none !important;
            }
            .checkout-options .custom-switch{
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>

@endsection
@section('myscript')
    <script src="{{asset('vendors/js/extensions/nouislider.min.js')}}" type="text/javascript"></script>
    <script>
        var add_remove = bodymovin.loadAnimation({container: document.getElementById('add-remove'), path: '{{asset('products/add_remove.json')}}', renderer: 'svg', loop: false, autoplay: false});
        var response = {};
        var min_coverage = {
            medical:0,
            ci:0,
            accident:0,
            disability:0,
            death:0,
        };

    </script>
    <script>
        $(".place-order").on("click",function (e) {
            loadData(true,null,'confirm');
        });
        $( window ).resize(function() {
            var top = $("[data-content='medical']").offset().top;
            $(".checkout-options").css("top","150px");
        });
        // $(".addMedical").on("click",function (e) {
        //     var val = $("input[name=medical]").val();
        //     if(val == '1'){
        //         //set to disable
        //         $("input[name=medical]").val("0");
        //         // $(".addMedical").html("Purchase");
        //
        //         add_remove.playSegments([140,195],true);
        //     }else{
        //         //set to enable
        //         $("input[name=medical]").val("1");
        //         // $(".addMedical").html("Remove");
        //         add_remove.playSegments([50,110],true);
        //     }
        //     loadData(true,"medical");
        // })
    </script>
    <script>
        var medical = parseInt($("input[name=medical]").val());
        var medical_slider = document.getElementById('medical_slider');
        noUiSlider.create(medical_slider, {
            start: 0,
            step:1,
            connect: [true, false],
            behaviour: 'drag',
            range: {
                'min': 0,
                'max': 5,
            }
        });

        medical_slider.noUiSlider.on('update', function (values, handle) {
            var value = Math.round(values[0]);

            $("input[name=medical]").val(value);

                if(value == 0)
                    value =  '0 /{{__('web/product.admission')}}' ;
                if(value == 1)
                    value =  '500 /{{__('web/product.admission')}}' ;
                if(value == 2)
                    value =  '1000 /{{__('web/product.admission')}}' ;
                if(value == 3)
                    value =  '2000 /{{__('web/product.admission')}}' ;
                if(value == 4)
                    value =  '5000 /{{__('web/product.admission')}}' ;
                if(value == 5)
                    value =  '10000 /{{__('web/product.admission')}}' ;

            $("span[data-name=medical]").html(value.toLocaleString());

        });
        medical_slider.noUiSlider.set([medical]);
    </script>
    <script>
        var ci = parseInt($("input[name=ci]").val());
        var ci_slider = document.getElementById('ci_slider');
        noUiSlider.create(ci_slider, {
            start: 0,
            step:1000,
            connect: [true, false],
            behaviour: 'drag',
            range: {
                'min': 0,
                'max': 1000000,
            }
        });

        ci_slider.noUiSlider.on('update', function (values, handle) {
            var value = Math.round(values[0]);
            if(value < min_coverage.ci){
                ci_slider.noUiSlider.set([min_coverage.ci]);
                return;
            }
            $("input[name=ci]").val(value);
            $("span[data-name=ci]").html(value.toLocaleString());

        });
        ci_slider.noUiSlider.set([ci]);
    </script>

    <script>
        var death = parseInt($("input[name=death]").val());
        var death_slider = document.getElementById('death_slider');
        noUiSlider.create(death_slider, {
            start: 0,
            step:1000,
            connect: [true, false],
            behaviour: 'drag',
            range: {
                'min': 0,
                'max': 1000000,
            }
        });
        death_slider.noUiSlider.on('update', function (values, handle) {
            var value = Math.round(values[0]);
            if(value < min_coverage.death){
                death_slider.noUiSlider.set([min_coverage.death]);
                return;
            }
            $("input[name=death]").val(value);
            $("span[data-name=death]").html(value.toLocaleString());
        });
        death_slider.noUiSlider.set([death]);
    </script>

    <script>
        var disability = parseInt($("input[name=disability]").val());
        var disability_slider = document.getElementById('disability_slider');
        noUiSlider.create(disability_slider, {
            start: 0,
            step:1000,
            connect: [true, false],
            behaviour: 'drag',
            range: {
                'min': 0,
                'max': 1000000,
            }
        });
        disability_slider.noUiSlider.on('update', function (values, handle) {
            var value = Math.round(values[0]);
            if(value < min_coverage.disability){
                disability_slider.noUiSlider.set([min_coverage.disability]);
                return;
            }
            $("input[name=disability]").val(value);
            $("span[data-name=disability]").html(value.toLocaleString());
        });
        disability_slider.noUiSlider.set([disability]);
    </script>

    <script>
        var accident = parseInt($("input[name=accident]").val());
        var accident_slider = document.getElementById('accident_slider');
        noUiSlider.create(accident_slider, {
            start: 0,
            step:1000,
            connect: [true, false],
            behaviour: 'drag',
            range: {
                'min': 0,
                'max': 1000000,
            }
        });
        accident_slider.noUiSlider.on('update', function (values, handle) {
            var value = Math.round(values[0]);
            // if(value < min_coverage.accident){
            //     accident_slider.noUiSlider.set([min_coverage.accident]);
            //     return;
            // }
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
            var top = $("[data-content='medical']").offset().top;
            $(".checkout-options").css("top","150px");
            loadData();
            medical_slider.noUiSlider.on('change', function (values, handle) {loadData(true,"medical");});
            ci_slider.noUiSlider.on('change', function (values, handle) {loadData(true,"ci");});
            accident_slider.noUiSlider.on('change', function (values, handle) {loadData(true,"accident");});
            disability_slider.noUiSlider.on('change', function (values, handle) {loadData(true,"disability");});
            death_slider.noUiSlider.on('change', function (values, handle) {loadData(true,"death");});
            if($("input[name=medical]").val() == '1'){
                add_remove.playSegments([50,110],true);
            }
        });

        var onLoadingStatus = false;
        var initialLoad = true;

        function loadData(withPayload = false,changed,mode='update'){
            if(onLoadingStatus)
                return;
            onLoadingStatus = true;
            total_anually = 0;
            total_monthly = 0;

            if(mode == 'confirm'){
                $(".content-body").hide();
                $(".loading").show();
            }

            if(initialLoad){
                $(".content-body").hide();
                $(".loading").show();
            }

            if(!withPayload) {
                $(".loading").show();

            }else{
                $("span[data-value="+changed+"]").html('<div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div>')
            }

            var data = {_token:'{{csrf_token()}}',user_id:'{{$uid ?? null}}',fill_type : '{{$fill_type ?? null}}'};
            if(withPayload){
                data=[
                    {name:'Medical',coverage:parseFloat($('input[name=medical]').val())},
                    {name:'Critical Illness',coverage:parseFloat($('input[name=ci]').val())},
                    {name:'Accident',coverage:parseFloat($('input[name=accident]').val())},
                    {name:'Disability',coverage:parseFloat($('input[name=disability]').val())},
                    {name:'Disability',coverage:parseFloat($('input[name=disability]').val())},
                    {name:'Death',coverage:parseFloat($('input[name=death]').val())},
                ];

                data = {mode:mode,payload:data,_token:'{{csrf_token()}}',user_id:'{{$uid ?? null}}',fill_type : '{{$fill_type ?? null}}'};
            }

            $.ajax({
                type: "POST",
                url: "{{route('wb-api.getproducts')}}",
                data: JSON.stringify(data),
                processData: false,
                contentType:"application/json",
                success:function (res) {
                    if(res.status == 'error'){
                        onLoadingStatus = false;
                        $(".loading").hide();
                        $(".content-body").show();
                        return Swal.fire({
                            title: 'Error',
                            text: res.message,
                            type: 'error',
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'Ok',
                            confirmButtonClass: 'btn btn-primary',
                            buttonsStyling: false,
                        });
                    }
                    if(res.data && res.data.msg){
                        $(".loading").hide();
                        Swal.fire({
                            title: "Error",
                            text: res.data.msg,
                            type: 'error',
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'Ok',
                            confirmButtonClass: 'btn btn-primary',
                            buttonsStyling: false,
                        }).then(function (result) {
                            window.location = '{{asset('/')}}';
                        });
                        return ;
                    }
                    response = res;
                    onLoadingStatus = false;
                    if(mode == 'confirm'){
                        $(".content-body").show();
                        $(".loading").hide();

                        if(res.status == 'success'){
                            Swal.fire({
                                title: 'Information',
                                text: '{{__('web/product.save_success')}}',
                                type: 'success',
                                showCancelButton: false,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'Ok',
                                confirmButtonClass: 'btn btn-primary',
                                buttonsStyling: false,
                            }).then(function(res2){
                                if(res.data.next_page_url)
                                    return window.location = res.data.next_page_url;
                                @if(!empty($fill_type) && $fill_type == 'buy_for_others')
                                    window.location = '{{route('userpanel.order.other',['uid'=>$uid])}}';
                                @else{
                                    window.location = '{{asset('user/go/')}}' + '/' + res.data.next_page;
                                }
                                @endif
                            });

                        }else{
                            Swal.fire({
                                title: 'Error',
                                text: '{{__('web/product.save_failed')}}',
                                type: 'error',
                                showCancelButton: false,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'Ok',
                                confirmButtonClass: 'btn btn-primary',
                                buttonsStyling: false,
                            });
                        }
                        return true;
                    }
                    if(initialLoad) {
                        $(".content-body").show();
                        $(".loading").hide();
                        var prod_1 = bodymovin.loadAnimation({
                            container: document.getElementById('prod-1'),
                            path: '{{asset('products/1_o.json')}}',
                            renderer: 'canvas',
                            loop: true,
                            autoplay: true
                        });
                        var prod_2 = bodymovin.loadAnimation({
                            container: document.getElementById('prod-2'),
                            path: '{{asset('products/2_o.json')}}',
                            renderer: 'canvas',
                            loop: true,
                            autoplay: true
                        });
                        var prod_3 = bodymovin.loadAnimation({
                            container: document.getElementById('prod-3'),
                            path: '{{asset('products/3_o.json')}}',
                            renderer: 'canvas',
                            loop: true,
                            autoplay: true
                        });
                        var prod_4 = bodymovin.loadAnimation({
                            container: document.getElementById('prod-4'),
                            path: '{{asset('products/4_o.json')}}',
                            renderer: 'canvas',
                            loop: true,
                            autoplay: true
                        });
                        var prod_5 = bodymovin.loadAnimation({
                            container: document.getElementById('prod-5'),
                            path: '{{asset('products/5_o.json')}}',
                            renderer: 'canvas',
                            loop: true,
                            autoplay: true
                        });

                        initialLoad = false;
                    }
                    var slider_width = $(".noUi-connects").width();
                    if(res.status != 'success')
                        return alert('loadingError');
                    res = res.data;

                    $(document).find(".ext-pc").remove();

                    //medical
                    var medical = res.find(x=>x.name == 'Medical');
                    min_coverage.medical = medical.options.min_coverage;
                    if(medical.options.allowed.toString() == 'false'){
                        $("#medical_slider").hide();
                        $("[data-content=medical] h5").hide();
                    }else{
                        $("#medical_slider").show();
                        $("[data-content=medical] h5").show();
                    }
                    if(medical.options.message != null){
                        $("div[data-content=medical]").find(".error").html("<p class='danger mt-1 text-center'>"+medical.options.message+"</p>")
                    }else{
                        $("div[data-content=medical]").find(".error").html("");
                    }
                    if(medical.options.coverage > 1)
                        $(".detail span[data-value=medical]").parents(".detail").show();
                    else
                        $(".detail span[data-value=medical]").parents(".detail").hide();

                    $("span[data-value=medical][data-type=a]").html(Math.abs(medical.options.annually).toLocaleString());
                    $("span[data-value=medical][data-type=m]").html(Math.abs(medical.options.monthly).toLocaleString());
                    $("span[data-name=medical-c]").html(Math.abs(medical.options.max_coverage).toLocaleString());
                    total_anually += parseFloat(medical.options.annually);
                    total_monthly += parseFloat(medical.options.monthly);
                    medical_slider.noUiSlider.updateOptions({range: {'min': 0, 'max': 5,}}, true);
                    medical_slider.noUiSlider.set([parseFloat(medical.options.coverage)]);

                    // let medical_width = 0;
                    // medical.payers.map(function(val,i){
                    //     $("#medical_slider .noUi-connects").append("<div class='ext-pc' style='width: "+((val.coverage/medical.options.max_coverage)*100)+"%;position:absolute;left:"+medical_width+"%;background-color: "+val.color+";height: 12px;z-index: 999'></div>");
                    //     medical_width += ((val.coverage/medical.options.max_coverage)*100);
                    // });

                    //ci
                    var ci = res.find(x=>x.name == 'Critical Illness');
                    min_coverage.ci = ci.options.min_coverage;
                    if(ci.options.allowed.toString() == 'false'){
                        $("#ci_slider").hide();
                        $("[data-content=ci] h5").hide();
                    }else{
                        $("#ci_slider").show();
                        $("[data-content=ci] h5").show();
                    }
                    if(ci.options.message != null){
                        $("div[data-content=ci]").find(".error").html("<p class='danger mt-1 text-center'>"+ci.options.message+"</p>")
                    }else{
                        $("div[data-content=ci]").find(".error").html("");
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

                    let ci_width = 0;
                    ci.payers.map(function(val,i){
                        $("#ci_slider .noUi-connects").append("<div class='ext-pc' style='width: "+((val.coverage/ci.options.max_coverage)*100)+"%;position:absolute;left:"+ci_width+"%;background-color: "+val.color+";height: 12px;z-index: 999'></div>");
                        ci_width += ((val.coverage/ci.options.max_coverage)*100);
                    });

                    //accident
                    var accident = res.find(x=>x.name == 'Accident');
                    min_coverage.accident = accident.options.min_coverage;

                    if(accident.options.allowed.toString() == 'false'){
                        $("#accident_slider").hide();
                        $("[data-content=accident] h5").hide();
                    }else{
                        $("#accident_slider").show();
                        $("[data-content=accident] h5").show();
                    }
                    if(accident.options.message != null){
                        $("div[data-content=accident]").find(".error").html("<p class='danger mt-1 text-center'>"+accident.options.message+"</p>")
                    }else{
                        $("div[data-content=accident]").find(".error").html("");
                    }
                    if(accident.options.coverage > 1)
                        $(".detail span[data-value=accident]").parents(".detail").show();
                    else
                        $(".detail span[data-value=accident]").parents(".detail").hide();

                    $("span[data-value=accident][data-type=a]").html(Math.abs(accident.options.annually).toFixed(2).toLocaleString());
                    $("span[data-value=accident][data-type=m]").html(Math.abs(accident.options.monthly).toFixed(2).toLocaleString());
                    $("span[data-name=accident-c]").html(Math.abs(accident.options.max_coverage).toLocaleString());
                    total_anually += parseFloat(accident.options.annually);
                    total_monthly += parseFloat(accident.options.monthly);
                    accident_slider.noUiSlider.updateOptions({range: {'min': 0, 'max': parseFloat(accident.options.max_coverage) == 0 ? 1 : parseFloat(accident.options.max_coverage),}}, true);
                    accident_slider.noUiSlider.set([parseFloat(accident.options.coverage)]);

                    let accident_width = 0;
                    accident.payers.map(function(val,i){
                        $("#accident_slider .noUi-connects").append("<div class='ext-pc' style='position:relative;left:0;width: "+(val.coverage*100/accident.options.max_coverage)+"%;background-color: "+val.color+";height: 12px;z-index: 999'></div>");
                        accident_width += ((val.coverage/accident.options.max_coverage)*100);
                    });

                    //disability
                    var disability = res.find(x=>x.name == 'Disability');
                    min_coverage.disability = disability.options.min_coverage;

                    if(disability.options.allowed.toString() == 'false'){
                        $("#disability_slider").hide();
                        $("[data-content=disability] h5").hide();
                    }else{
                        $("#disability_slider").show();
                        $("[data-content=disability] h5").show();
                    }
                    if(disability.options.message != null){
                        $("div[data-content=disability]").find(".error").html("<p class='danger mt-1 text-center'>"+disability.options.message+"</p>")
                    }else{
                        $("div[data-content=disability]").find(".error").html("");
                    }
                    if(disability.options.coverage > 1)
                        $(".detail span[data-value=disability]").parents(".detail").show();
                    else
                        $(".detail span[data-value=disability]").parents(".detail").hide();

                    $("span[data-value=disability][data-type=a]").html(Math.abs(disability.options.annually).toFixed(2).toLocaleString());
                    $("span[data-value=disability][data-type=m]").html(Math.abs(disability.options.monthly).toFixed(2).toLocaleString());
                    $("span[data-name=disability-c]").html(Math.abs(disability.options.max_coverage).toLocaleString());
                    total_anually += parseFloat(disability.options.annually);
                    total_monthly += parseFloat(disability.options.monthly);
                    disability_slider.noUiSlider.updateOptions({range: {'min': 0, 'max': parseFloat(disability.options.max_coverage) == 0 ? 1 : parseFloat(disability.options.max_coverage),}}, true);
                    disability_slider.noUiSlider.set([parseFloat(disability.options.coverage)]);

                    let disability_width = 0;
                    disability.payers.map(function(val,i){
                        $("#disability_slider .noUi-connects").append("<div class='ext-pc' style='position:relative;left:0;width: "+(val.coverage*100/disability.options.max_coverage)+"%;background-color: "+val.color+";height: 12px;z-index: 999'></div>");
                        disability_width += ((val.coverage/disability.options.max_coverage)*100);
                    });

                    //death
                    var death = res.find(x=>x.name == 'Death');
                    min_coverage.death = death.options.min_coverage;

                    if(death.options.allowed.toString() == 'false'){
                        $("#death_slider").hide();
                        $("[data-content=death] h5").hide();
                    }else{
                        $("#death_slider").show();
                        $("[data-content=death] h5").show();

                    }
                    if(death.options.message != null){
                        $("div[data-content=death]").find(".error").html("<p class='danger mt-1 text-center'>"+death.options.message+"</p>")
                    }else{
                        $("div[data-content=death]").find(".error").html("");
                    }

                    if(death.options.coverage > 1)
                        $(".detail span[data-value=death]").parents(".detail").show();
                    else
                        $(".detail span[data-value=death]").parents(".detail").hide();

                    $("span[data-value=death][data-type=a]").html(Math.abs(death.options.annually).toFixed(2).toLocaleString());
                    $("span[data-value=death][data-type=m]").html(Math.abs(death.options.monthly).toFixed(2).toLocaleString());
                    $("span[data-name=death-c]").html(Math.abs(death.options.max_coverage).toLocaleString());
                    total_anually += parseFloat(death.options.annually);
                    total_monthly += parseFloat(death.options.monthly);
                    death_slider.noUiSlider.updateOptions({range: {'min': 0, 'max': parseFloat(death.options.max_coverage) == 0 ? 1 : parseFloat(death.options.max_coverage),}}, true);
                    death_slider.noUiSlider.set([parseFloat(death.options.coverage)]);

                    let death_width = 0;
                    death.payers.map(function(val,i){
                        $("#death_slider .noUi-connects").append("<div class='ext-pc' style='position:relative;left:0;width: "+(val.coverage*100/death.options.max_coverage)+"%;background-color: "+val.color+";height: 12px;z-index: 999'></div>");
                        death_width += ((val.coverage/death.options.max_coverage)*100);

                    });

                    changeCycle();
                    $(".loading").hide();

                }
            });
            $(".noUi-connects").addClass('d-flex');
        }
    </script>
    <script>
        $(".icon-info").on("click",function(e){
            var data = response.data.find(x=>x.name == $(this).data('product'));
            $(".doc_list").html("");
            var out = '';
            data.documents.map(function(val){
                out += '<div class="p-1 m-1" style="border: 1px solid #ccc;border-radius: 10px"><a target="_blank" href="'+val.link+'">'+val.title+'</a> </div>';
            });

            $(".doc_list").html(out);
            $('#info-modal').modal('show');

        })
        $(".coverage-selector").on("click",function(e){
            $("input[name=coverage-value]").removeClass('is-invalid');
            let data = $(this).data('product');
            let max_coverage = $("[data-name="+data+'-c').html();
            let coverage = replaceAll($("[data-name="+data).html(),',','');
            $("#change-value-modal #min-coverage").html('0');
            $("#change-value-modal #max-coverage").html(max_coverage);
            $("input[name=coverage-value]").val(coverage);
            $('#change-value-modal').modal('show');
            $('#change-value-modal form').attr("data-handler",data);


        });
        $(".deductible-info").on("click",function (e) {
            Swal.fire({
                title: "Info",
                text: '{{__('mobile.deductible_info')}}',
                type: 'info',
                showCancelButton: false,
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Ok',
                confirmButtonClass: 'btn btn-primary',
                buttonsStyling: false,
            })
        });
        var element = '';
        var this_slider = '';
        var value = '';
        var max_coverage = '';
        $("#change-value-form").on("submit",function(e){
            element = $(this).attr('data-handler');
            this_slider = document.getElementById(element+'_slider');
            value = $("input[name=coverage-value]").val();
            max_coverage = replaceAll($("[data-name="+element+'-c').html(),',','');
            if(parseFloat(value) < 0 || parseFloat(value) > parseFloat(max_coverage)) {

                 $("input[name=coverage-value]").addClass('is-invalid');
                return false;
            }



            this_slider.noUiSlider.set([value]);
            $("input[name="+element+"]").val(value);
            $("span[data-name="+element+"]").html(value.toLocaleString());
            loadData(true,element);
            $('#change-value-modal').modal('hide');
            return false;


        })
    </script>
@endsection
