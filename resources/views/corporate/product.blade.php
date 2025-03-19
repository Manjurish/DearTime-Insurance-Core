@extends('layouts.contentLayoutMaster')
@section('title', __('web/product.choose_your_benefits'))
@section('content')
    <div class="ecommerce-application">
        <form action="{{route('userpanel.groupPackage.savePackage')}}" method="post">
            <div class="row px-0">
                <div class="col-lg-8">
                    <div class="checkout-items">
                        <div class="grid-view" style="margin-bottom: auto">
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
                                        <input type="hidden" name="medical" value="{{$package->MC1 ?? '0'}}">


                                    </div>

                                </div>
                            </div>
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
                                        <input type="hidden" name="ci" value="{{$package->CI ?? '0'}}">

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
                                        <input type="hidden" name="death" value="{{$package->DTH ?? '0'}}">

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
                                        <input type="hidden" name="disability" value="{{$package->TPD ?? '0'}}">

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
                                        <input type="hidden" name="accident" value="{{$package->ADD ?? '0'}}">

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
                                            <div class="custom-control custom-switch switch-lg mb-1">
                                                <input type="checkbox" name="cycle" @if(($package->payment_term ?? '') == 'annually') checked @endif value="1" class="custom-control-input" id="cycle">
                                                <label class="custom-control-label" for="cycle">
                                                    <span class="switch-text-left">{{__('web/product.annually')}}</span>
                                                    <span class="switch-text-right">{{__('web/product.monthly')}}</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="coupons">
                                        <div class="coupons-title d-flex justify-content-center align-items-center">
                                            <span>{{__('web/product.title')}}</span>
                                        </div>
                                        <div class="apply-coupon">
                                            <div>
                                                <input type="text" class="form-control" name="title" placeholder="{{__('web/product.title')}}" value="{{$package->name ?? ''}}">
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="dth" value="{{$package->DTH ?? '0'}}">
                                    <input type="hidden" name="add" value="{{$package->ADD ?? '0'}}">
                                    <input type="hidden" name="tpd" value="{{$package->TPD ?? '0'}}">
                                    <input type="hidden" name="mc1" value="{{$package->MC1 ?? '0'}}">
                                    <input type="hidden" name="ci" value="{{$package->CI ?? '0'}}">
                                    <input type="hidden" name="package_id" value="{{$package->uuid ?? ''}}">
                                    @csrf
                                    <hr>
                                    <div class="price-details">
                                        <p>{{__('web/product.price_details')}}</p>

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
                                    <button class="btn btn-primary btn-block place-order">{{__('web/product.place_order')}}</button>
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
    <style>
        .my-1{
            width: 80%;

        }
        .checkout-options{
            position: fixed;
            width: 30%;
            top: 50px;
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
        $("form").on("submit",function (e) {
            var medical = $("input[name=medical]").val();
            var ci = $("input[name=ci]").val();
            var death = $("input[name=death]").val();
            var disability = $("input[name=disability]").val();
            var accident = $("input[name=accident]").val();
            var title = $("input[name=title]");
            Validation.clearAllValidation();
            if(parseInt(medical) <= 0 && parseInt(ci) <= 0 && parseInt(death) <= 0 && parseInt(disability) <= 0 && parseInt(accident) <= 0){
                Swal.fire({
                    title: '{{__('web/product.error')}}',
                    text: '{{__('web/product.product_select_error')}}',
                    type: 'error',
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Ok',
                    confirmButtonClass: 'btn btn-primary',
                    buttonsStyling: false,
                });
                return false;
            }
            if(Validation.empty(title)){
                Validation.setInvalid(title, '{{__('web/auth.required')}}');
                return false;
            }
            $(".loading").show();

        });
    </script>
    <script>
       var add_remove = bodymovin.loadAnimation({container: document.getElementById('add-remove'), path: '{{asset('products/add_remove.json')}}', renderer: 'svg', loop: false, autoplay: false});
    </script>

    <script>
        $( window ).resize(function() {
            var top = $("[data-content='medical']").offset().top;
            $(".checkout-options").css("top","150px");
        });
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
        var ci = parseInt($("input[name=ci]").val());
        var ci_slider = document.getElementById('ci_slider');
        noUiSlider.create(ci_slider, {
            start: 0,
            connect: [true, false],
            behaviour: 'drag',
            range: {
                'min': 0,
                'max': 1000000,
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
        var death = parseInt($("input[name=death]").val());
        var death_slider = document.getElementById('death_slider');
        noUiSlider.create(death_slider, {
            start: 0,
            connect: [true, false],
            behaviour: 'drag',
            range: {
                'min': 0,
                'max': 1000000,
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
        var disability = parseInt($("input[name=disability]").val());
        var disability_slider = document.getElementById('disability_slider');
        noUiSlider.create(disability_slider, {
            start: 0,
            connect: [true, false],
            behaviour: 'drag',
            range: {
                'min': 0,
                'max': 1000000,
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
        var accident = parseInt($("input[name=accident]").val());
        var accident_slider = document.getElementById('accident_slider');
        noUiSlider.create(accident_slider, {
            start: 0,
            connect: [true, false],
            behaviour: 'drag',
            range: {
                'min': 0,
                'max': 1000000,
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
            var top = $("[data-content='medical']").offset().top;
            $(".checkout-options").css("top","150px");
            @if(empty($package))
            loadData();
            @else
            loadData(true);
            @endif
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
        function loadData(withPayload = false,changed){
            if(onLoadingStatus)
                return;
            if(initialLoad){
                $(".content-body").hide();
                $(".loading").show();
            }
            onLoadingStatus = true;
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
                    {name:'Death',coverage:parseFloat($('input[name=death]').val())},
                ];
                data = {mode:'update',payload:data,_token:'{{csrf_token()}}'};
            }
            console.log(data);

            $.ajax({
                type: "POST",
                url: "{{route('wb-api.getproducts')}}",
                data: JSON.stringify(data),
                processData: false,
                contentType:"application/json",
                success:function (res) {

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
                    if(res.status != 'success')
                        window.location = '';
                    res = res.data;

                    //medical
                    var medical = res.find(x=>x.name == 'Medical');
                    if(medical.options.allowed.toString() == 'false'){
                        $(".addMedical").hide();
                        $("[data-content=medical] h5").hide();
                    }else{
                        $(".addMedical").show();
                        $("[data-content=medical] h5").show();
                    }
                    if(medical.options.message != null){
                        $("div[data-content=medical]").find(".error").html("<p class='danger mt-1 text-center'>"+medical.options.message+"</p>")
                    }else{
                        $("div[data-content=medical]").find(".error").html("");
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
                    $("input[name=mc1]").val(medical.options.coverage);

                    //ci
                    var ci = res.find(x=>x.name == 'Critical Illness');
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
                    $("input[name=ci]").val(ci.options.coverage);

                    //accident
                    var accident = res.find(x=>x.name == 'Accident');
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

                    $("span[data-value=accident][data-type=a]").html(Math.abs(accident.options.annually).toLocaleString());
                    $("span[data-value=accident][data-type=m]").html(Math.abs(accident.options.monthly).toLocaleString());
                    $("span[data-name=accident-c]").html(Math.abs(accident.options.max_coverage).toLocaleString());
                    total_anually += parseFloat(accident.options.annually);
                    total_monthly += parseFloat(accident.options.monthly);
                    accident_slider.noUiSlider.updateOptions({range: {'min': 0, 'max': parseFloat(accident.options.max_coverage) == 0 ? 1 : parseFloat(accident.options.max_coverage),}}, true);
                    accident_slider.noUiSlider.set([parseFloat(accident.options.coverage)]);
                    $("input[name=add]").val(accident.options.coverage);

                    //disability
                    var disability = res.find(x=>x.name == 'Disability');
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

                    $("span[data-value=disability][data-type=a]").html(Math.abs(disability.options.annually).toLocaleString());
                    $("span[data-value=disability][data-type=m]").html(Math.abs(disability.options.monthly).toLocaleString());
                    $("span[data-name=disability-c]").html(Math.abs(disability.options.max_coverage).toLocaleString());
                    total_anually += parseFloat(disability.options.annually);
                    total_monthly += parseFloat(disability.options.monthly);
                    disability_slider.noUiSlider.updateOptions({range: {'min': 0, 'max': parseFloat(disability.options.max_coverage) == 0 ? 1 : parseFloat(disability.options.max_coverage),}}, true);
                    disability_slider.noUiSlider.set([parseFloat(disability.options.coverage)]);
                    $("input[name=tpd]").val(disability.options.coverage);

                    //death
                    var death = res.find(x=>x.name == 'Death');
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

                    $("span[data-value=death][data-type=a]").html(Math.abs(death.options.annually).toLocaleString());
                    $("span[data-value=death][data-type=m]").html(Math.abs(death.options.monthly).toLocaleString());
                    $("span[data-name=death-c]").html(Math.abs(death.options.max_coverage).toLocaleString());
                    total_anually += parseFloat(death.options.annually);
                    total_monthly += parseFloat(death.options.monthly);
                    death_slider.noUiSlider.updateOptions({range: {'min': 0, 'max': parseFloat(death.options.max_coverage) == 0 ? 1 : parseFloat(death.options.max_coverage),}}, true);
                    death_slider.noUiSlider.set([parseFloat(death.options.coverage)]);
                    $("input[name=dth]").val(death.options.coverage);

                    changeCycle();
                    $(".loading").hide();

                    onLoadingStatus = false;
                }

            });
        }
    </script>
@endsection
