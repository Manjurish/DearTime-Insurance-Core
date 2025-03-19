@extends('layouts.contentLayoutMaster')
@section('title', __('web/thanksgiving.thanksgiving'))
@section('content')
    <section>
        <div class="row ">
            <div class="col-12">
                <form id="thanksgiving_form" action="" method="post">
                    @csrf
                    <div class="card pb-1">
                        <div class="card-header">
                            <h4 class="card-title">{{__('web/thanksgiving.thanksgiving')}}</h4>

                        </div>
                        <div class="card-content">
                            <div class="card-body">
                                <p>{{__('web/thanksgiving.thanksgiving_desc')}}</p>
                                <p>{{__('web/thanksgiving.thanksgiving_ask')}}</p>
                                <div class="offset-md-2 col-md-8 mt-2">



                                        <div class="col-12 p-2 mt-1" style="border: 1px solid #ccc;border-radius: 15px">
                                            <div class="d-flex Slider flex-row justify-content-center align-items-center">
                                                <div class="m-1 pr-1">
                                                    <svg width="56" height="56" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M9.01184 26.3034C8.46017 24.7434 6.98888 20.4305 6.7131 19.6965C5.7025 17.5862 4.41888 17.3122 3.68584 17.5884C3.41094 17.6805 3.04466 18.048 3.32084 19.1493C3.59702 20.2506 6.17843 34.0174 8.65805 38.3292C9.02531 38.8796 10.0346 39.7965 10.4933 40.1632C12.2363 41.3547 18.289 43.9185 18.289 43.9185L18.2933 47.8658L25.6266 47.8579C25.6266 47.8579 26.1702 41.8905 24.2412 38.1289C24.1493 37.9454 23.415 37.0282 22.7727 36.4781C22.4975 36.2948 21.947 35.7446 21.6719 35.6531C19.654 34.5537 15.2522 32.8143 15.2522 32.8143L11.7622 26.6676C11.303 25.8419 10.2027 25.5677 9.37821 26.0276C8.5537 26.4875 8.27988 27.5893 8.7391 28.415L12.5044 34.9286C12.5963 35.1121 12.7798 35.2955 12.8716 35.3872L13.0551 35.5706L16.2661 38.0457" stroke="#ACB1CA" stroke-miterlimit="10" stroke-linecap="round"/>
                                                        <path d="M36.035 9.6592C29.8933 9.66579 27.5174 16.6449 27.5174 16.6449C27.5174 16.6449 25.035 9.67101 18.985 9.67751C14.9516 9.68184 12.2074 15.1008 13.7716 20.5152C15.8878 27.8567 27.5363 34.27 27.5363 34.27C27.5363 34.27 39.1711 27.8317 41.2716 20.4857C42.8241 15.0679 40.0683 9.65487 36.035 9.6592Z" stroke="#ACB1CA" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M25.717 47.7657L23.6087 47.768L19.3004 47.7726L18.3837 47.7736C17.1921 47.7749 16.2766 48.8774 16.2778 50.0708C16.2791 51.2642 17.197 52.3648 18.3886 52.3635L19.3053 52.3625L23.6136 52.3579L25.722 52.3556L25.717 47.7657Z" stroke="#ACB1CA" stroke-miterlimit="10" stroke-linecap="round"/>
                                                        <path d="M46.0449 26.2634C46.5932 24.7022 48.0553 20.3862 48.3295 19.6515C49.3356 17.5391 50.6186 17.2623 51.3522 17.5369C51.6273 17.6285 51.9944 17.9952 51.7206 19.0971C51.4468 20.199 48.8948 33.9713 46.4245 38.2884C46.0584 38.8396 45.051 39.7587 44.5931 40.1263C42.8527 41.3216 36.8052 43.623 36.8052 43.623L36.8091 47.2949L29.4758 47.3028C29.4758 47.3028 28.9196 41.5201 30.8405 37.7544C30.932 37.5707 31.6646 36.8355 32.3056 36.284C32.5804 36.1002 33.1299 35.6406 33.4048 35.4567C35.4202 34.353 39.8184 32.6041 39.8184 32.6041L43.2951 26.4499C43.7526 25.6233 44.8523 25.3467 45.6778 25.8048C46.5032 26.2629 46.7794 27.3642 46.322 28.1908L42.5706 34.7125C42.4792 34.8962 42.296 35.08 42.2045 35.1718L42.0213 35.3556L38.8156 37.8376" stroke="#ACB1CA" stroke-miterlimit="10" stroke-linecap="round"/>
                                                        <path d="M29.3838 47.7622L31.8588 47.7595L36.1671 47.7549L37.0838 47.7539C38.2755 47.7527 39.1933 48.8532 39.1946 50.0466C39.1959 51.24 38.2804 52.3425 37.0887 52.3438L36.172 52.3448L31.8637 52.3494L29.3887 52.3521L29.3838 47.7622Z" stroke="#ACB1CA" stroke-miterlimit="10" stroke-linecap="round"/>
                                                    </svg>

                                                </div>

                                                <div class="custom-control custom-switch switch-lg custom-switch-success mr-2 width-90-per">
                                                    <p class="mb-0">{{__('web/thanksgiving.charity_insurance')}} (<span id="charity_value">0</span> %)</p>
                                                    <input type="hidden" name="charity"  value="0">
                                                    <div class="my-1" id="charity_slider"></div>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="col-12 p-2 mt-1 @if(!$promoter_allowed) d-none @endif" style="border: 1px solid #ccc;border-radius: 15px">
                                            <div class="d-flex Slider flex-row justify-content-center align-items-center">
                                                <div class="m-1 pr-1">
                                                    <svg width="42" height="49" viewBox="0 0 42 49" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M24.7799 2.25204C18.9261 14.2838 14.3451 16.4918 14.3451 16.4918L3.25929 21.9198C1.61017 22.7477 0.879003 24.768 1.70577 26.4195L3.17568 29.4472L4.73745 32.6585C5.56422 34.3099 7.48971 34.7668 9.13893 34.0307L20.2248 28.6027C20.2248 28.6027 24.8975 26.4864 37.9171 29.2263C39.2924 29.5002 41.308 28.4883 40.2975 26.4699C39.287 24.4514 34.326 14.1754 34.326 14.1754C34.326 14.1754 29.3652 4.08306 28.4464 2.06451C27.5275 0.0459539 25.4202 0.966189 24.7799 2.25204Z" stroke="#ACB1CA" stroke-miterlimit="10"/>
                                                        <path d="M32.8559 11.1477L34.8715 10.1358C36.5206 9.30782 38.5381 10.04 39.3649 11.6915C40.1916 13.343 39.4605 15.3633 37.8113 16.1912L35.7958 17.2032" stroke="#ACB1CA" stroke-miterlimit="10"/>
                                                        <path d="M8.03893 34.3072C8.03893 34.3072 10.4442 33.8457 15.1928 38.0632C17.6703 40.2637 23.6355 46.6831 23.6355 46.6831C25.012 48.0586 26.4789 48.2406 28.6782 47.5957L30.236 47.135C31.8851 46.3071 32.3419 44.8378 31.1485 43.2785C31.1485 43.2785 25.4556 34.2885 24.5369 32.4536C23.6183 30.6186 25.9978 27.2195 25.9978 27.2195L18.3728 11.6222" stroke="#ACB1CA" stroke-miterlimit="10"/>
                                                        <path d="M2.25673 27.5205L12.7929 22.3685C13.8923 21.8166 15.0845 22.2743 15.6356 23.3753C16.1868 24.4762 15.7298 25.6701 14.6304 26.2221L4.18576 31.2821" stroke="#ACB1CA" stroke-miterlimit="10"/>
                                                        <path d="M37.5508 29.043C36.5402 26.9328 31.1201 15.9229 31.1201 15.9229C31.1201 15.9229 25.8836 5.18828 24.7812 2.98633" stroke="#ACB1CA" stroke-miterlimit="10"/>
                                                    </svg>

                                                </div>
                                                <div class="custom-control custom-switch switch-lg custom-switch-success mr-2 width-90-per">
                                                    <p class="mb-0">{{__('web/thanksgiving.promoter')}} ( {{  $promoter_name }} ) (<span id="promoter_value">0</span> %)</p>
                                                    <input type="hidden" name="promoter"  value="0">
                                                    <div class="my-1" id="promoter_slider"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 p-2 mt-1 @if(!$self_allowed) d-none @endif" style="border: 1px solid #ccc;border-radius: 15px">
                                            <div class="d-flex Slider flex-row justify-content-center align-items-center">
                                                <div class="m-1 pr-1">
                                                    <svg width="43" height="50" viewBox="0 0 43 50" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M29.9771 14.9593C29.9771 14.9593 31.0541 13.921 31.9654 15.211C33.5586 17.4529 29.8177 20.497 29.8177 20.497M11.7481 14.4287C11.7481 14.4287 10.3497 7.80937 12.6126 4.44462C14.6575 1.39951 18.6992 1.00716 20.5806 1.0001C21.2516 0.997581 21.9231 0.999444 22.5941 1.00001C24.5306 1.00163 28.7666 1.3775 30.8263 4.44462C33.0893 7.80937 31.6908 14.4287 31.6908 14.4287M13.5551 14.9593C13.5551 14.9593 12.4781 13.921 11.5668 15.211C9.97358 17.4529 13.7144 20.497 13.7144 20.497M15.1891 30.9558C15.1891 30.9558 5.76444 32.0491 3.79814 34.2697C1.67928 36.6598 -0.736221 42.2451 2.78956 45.5929C6.38315 49 21.5881 49 21.5881 49C21.5881 49 21.5881 49 21.5881 49C21.5881 49 37.0642 49 40.6578 45.5929C44.1836 42.2451 41.7596 36.6682 39.6492 34.2697C37.6829 32.0491 28.2583 30.9558 28.2583 30.9558M15.1891 30.9558C17.1046 30.6676 17.9521 26.8536 17.9521 26.8536M15.1891 30.9558C14.2653 31.7694 12.9347 32.0575 15.8756 35.8376C17.9769 38.5383 19.9873 39.4003 20.964 39.6724C21.3678 39.7849 21.7904 39.7857 22.1971 39.6841C23.2331 39.4252 25.4185 38.5772 27.5548 35.8376C28.2705 34.9198 28.7332 34.2082 29.0104 33.6451C29.9294 31.7779 26.698 30.9081 25.9701 28.9585C25.58 27.9138 25.58 26.8536 25.58 26.8536M14.8671 23.4975C16.7062 28.2861 21.588 28.2861 21.5881 28.2861C21.5881 28.2861 21.5881 28.2861 21.5881 28.2861C21.5881 28.2861 26.7327 28.2861 28.5803 23.4975C29.7838 20.37 30.0974 17.5393 29.894 14.9119C29.8008 13.7168 29.4448 13.1235 29.3177 11.7166C29.2244 10.6657 29.4109 10.9962 29.3177 9.70795C29.2244 8.41968 28.9193 6.58051 26.3682 5.93638C24.2833 5.4109 22.7916 6.60594 21.5881 6.58051C20.4524 6.55508 19.1641 5.4109 17.0791 5.93638C14.5196 6.58051 14.2229 8.41968 14.1297 9.70795C14.0365 10.9962 14.2229 10.6741 14.1297 11.7166C14.0026 13.1151 13.6466 13.7168 13.5534 14.9119C13.35 17.5393 13.6635 20.37 14.8671 23.4975Z" stroke="#ACB1CA" stroke-miterlimit="10"/>
                                                    </svg>
                                                </div>
                                                <div class="custom-control custom-switch switch-lg custom-switch-success mr-2 width-90-per">
                                                    <p class="mb-0">{{__('web/thanksgiving.self')}} (<span id="self_value">0</span> %)</p>
                                                    <input type="hidden" name="self"  value="0">
                                                    <div class="my-1" id="self_slider"></div>
                                                </div>
                                            </div>
                                        </div>


                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group my-2">
                        <button type="submit" class="btn btn-primary storeBtn">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

@endsection
@section('mystyle')
    <link rel="stylesheet" type="text/css" href="{{asset('vendors/css/extensions/nouislider.min.css')}}">
    <style>
        @media (max-width: 767px) {
           .Slider{
               flex-direction: column !important;
               text-align: center;
           }
        }
    </style>
@endsection
@section('myscript')
    <script src="{{asset('vendors/js/extensions/nouislider.min.js')}}"></script>
    <script>

        $(document).ready(function () {
            charity_slider.noUiSlider.set({{$charity}});
            promoter_slider.noUiSlider.set({{$promoter}});
            self_slider.noUiSlider.set({{$self}});

        });



        var charity_slider = document.getElementById('charity_slider');
        noUiSlider.create(charity_slider, {
            start: 0,
            connect: [true, false],
            behaviour: 'drag',
            range: {
                'min': 0,
                'max': 10
            },

        });
        @if(!$promoter_allowed && !$self_allowed)
            charity_slider.noUiSlider.set(10);
            $("#charity_value").html(10);
            $("input[name=charity]").val(10);
        @endif
        charity_slider.noUiSlider.on('update', function (values, handle) {
            var value = Math.round(values[0]);
            var promoter = parseInt($("input[name=promoter]").val());
            var self = parseInt($("input[name=self]").val());
            @if(!$promoter_allowed && !$self_allowed)
                // charity_slider.noUiSlider.set(10);
                // $("#charity_value").html(10);
                // $("input[name=charity]").val(10);
                return true;
            @endif
            if(value > (10 - (promoter + self))){
                charity_slider.noUiSlider.set((10 - (promoter + self)));
                value = (10 - (promoter + self));
            }
            $("#charity_value").html(value);
            $("input[name=charity]").val(value);

        });



        var promoter_slider = document.getElementById('promoter_slider');
        noUiSlider.create(promoter_slider, {
            start: 0,
            connect: [true, false],
            behaviour: 'drag',
            range: {
                'min': 0,
                'max': 10
            }
        });
        promoter_slider.noUiSlider.on('update', function (values, handle) {
            var value = Math.round(values[0]);
            var charity = parseInt($("input[name=charity]").val());
            var self = parseInt($("input[name=self]").val());

            if(value > (10 - (charity + self))){
                promoter_slider.noUiSlider.set((10 - (charity + self)));
                value = (10 - (charity + self));
            }


            $("#promoter_value").html(value);
            $("input[name=promoter]").val(value);

        });


        var self_slider = document.getElementById('self_slider');
        noUiSlider.create(self_slider, {
            start: 0,
            connect: [true, false],
            behaviour: 'drag',
            range: {
                'min': 0,
                'max': 10
            }
        });
        self_slider.noUiSlider.on('update', function (values, handle) {
            var value = Math.round(values[0]);

            var charity = parseInt($("input[name=charity]").val());
            var promoter = parseInt($("input[name=promoter]").val());

            if(value > (10 - (charity + promoter))){
                self_slider.noUiSlider.set((10 - (charity + promoter)));
                value = (10 - (charity + promoter));
            }

            $("#self_value").html(value);
            $("input[name=self]").val(value);

        });


        $("#thanksgiving_form").on("submit",function (e) {
            $(".loading").show();
            $.post("{{route('wb-api.setThanksgiving')}}",{self:$("input[name=self]").val() * 10,charity:$("input[name=charity]").val() * 10,promoter:$("input[name=promoter]").val() * 10,_token:'{{csrf_token()}}'},function (res) {
                $(".loading").hide();
                Swal.fire({
                    title: 'Information',
                    text: '{{__('web/thanksgiving.save_success')}}',
                    type: 'success',
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Ok',
                    confirmButtonClass: 'btn btn-primary',
                    buttonsStyling: false,
                }).then(function(res2){
                    @if(request()->has('mn'))
                        window.location = '';
                    @else
                        window.location = '{{asset('user/go/')}}'+'/'+res.data.next_page;
                    @endif

                });
            });

           return false;
        });

    </script>
@endsection
