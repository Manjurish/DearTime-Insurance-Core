@extends('layouts.contentLayoutMaster')
@section('title', __('web/clinic.panel_clinics'))
@section('content')
    <section>
        <a href="{{route('userpanel.MedicalSurvey.index',['start-over'])}}" class="btn btn-primary m-1">{{__('web/medicalsurvey.manually')}}</a>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group" >
                    <div class="row charityList match-height">


                    </div>
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('myscript')
    <script>

        function getLocation() {
            if (navigator.geolocation) {
                $(".loading").show();
                navigator.geolocation.getCurrentPosition(loadData,showError);
            } else {
                Swal.fire({
                    title: 'Error',
                    text: "{{__('web/clinic.geolocation_not_supported')}}",
                    type: 'error',
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Ok',
                    confirmButtonClass: 'btn btn-primary',
                    buttonsStyling: false,
                });
            }
        }
        function showError(error) {
            $(".loading").hide();
            var error = '';
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    error = "{{__('web/clinic.geolocation_permission_denied')}}"
                    break;
                case error.POSITION_UNAVAILABLE:
                    error = "{{__('web/clinic.geolocation_unavailable')}}";
                    break;
                case error.TIMEOUT:
                    error = "{{__('web/clinic.geolocation_timeout')}}";
                    break;
                default:
                    error = "{{__('web/clinic.geolocation_unknown')}}";
                    break;
            }
            Swal.fire({
                title: '{{__('web/clinic.geolocation_failed_title')}}',
                text: error,
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '{{__('web/clinic.geolocation_try_again')}}',
                cancelButtonText: '{{__('web/clinic.geolocation_show_without')}}',
                confirmButtonClass: 'btn btn-primary',
                cancelButtonClass: 'btn btn-danger ml-1',
                buttonsStyling: false,
            }).then(function (result) {
                if (result.value) {

                    getLocation();
                }else{
                    $(".loading").show();
                    loadData();
                }
            });
        }

        $(document).ready(function () {
            // $(".loading").show();
            getLocation();
        });
        $(window).scroll(function() {
            if($(window).scrollTop() + $(window).height() > $(document).height() - 100) {
                loadData();
            }
        });
        var page = 1;
        var stop = false;
        var inLoading = false;
        var Coordinates = {latitude:0,longitude:0};
        function loadData(coordinate = null){
            if(coordinate != null)
                Coordinates = coordinate.coords;
            if(stop || inLoading)
                return;


            inLoading = true;

            $.get('{{route('api.clinic.list')}}',{page:page,latitude:Coordinates.latitude,longitude:Coordinates.longitude,ct:'clinic'},function (d) {

                $(".loading").hide();
                var out = '';

                d.data.data.map(function (val) {
                    var mapLink = 'https://maps.googleapis.com/maps/api/staticmap?center="'+val.latitude+','+val.longitude+'"&zoom=15&size=400x150&maptype=roadmap&markers=color:red%7C'+val.latitude+','+val.longitude+'&key=&language=en';

                    out += '<div  class="col-lg-4 col-sm-12">\n' +
                        '<div class="card">\n' +
                        '<div class="card-content text-center d-flex">\n' +
                        '<div class="card-body d-flex justify-content-center align-items-center flex-column">\n' +
                        '<img src=\''+mapLink+'\' alt="" width="100%" class="float-left mx-1 my-2">\n' +
                        '\n' +
                        '<h4 class="card-title mt-50">' + val.name + '</h4>\n' +
                        '<p class="card-text mb-0">' + val.phone + '</p>\n' +
                        '<p class="card-text mb-0 " style="height: 75px">' + val.address + '</p>\n' +
                        '<p class="card-text mb-0">' + val.state + '</p>\n' +
                        '<p class="card-text mb-0">' + val.post_code+' '+ val.city + '</p>\n' +
                        '</div>\n' +
                        '</div>\n' +
                        '</div>';

                    if(Coordinates.latitude != 0)
                    out+= '<div class="badge badge-primary badge-md mr-1 mb-1 position-absolute" style="top: 12px;right: 20px">' + Math.round(val.distance) + ' KM</div>\n';
                    out+='</div>';
                    $.fn.matchHeight._update();

                });

                inLoading = false;
                $(".charityList").append(out);

                if(page >= d.data.last_page){
                    stop = true;
                    $(".spinner-border").hide();
                }else{
                    page = page + 1;
                }
            })
        }
    </script>
@endsection
