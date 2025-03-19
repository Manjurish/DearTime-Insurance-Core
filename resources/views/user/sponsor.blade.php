@extends('layouts.contentLayoutMaster')
@section('title', __('web/sponsor.sponsor'))
@section('content')
    <section>
        <div class="row match-height">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{__('web/sponsor.sponsor')}}</h4>
                    </div>
                    <div class="card-content">

                        <div class="card-body">
                            <div>

                                @csrf

                                <p>{{__('web/sponsor.sponsor_desc')}}</p>


                                <div class="form-group" >
                                    <div class="row charityList">


                                    </div>
                                    <div class="spinner-border" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('myscript')
    <script>


        $(document).ready(function () {
            $(".loading").show();
            loadData();
        });
        $(window).scroll(function() {
            if($(window).scrollTop() + $(window).height() > $(document).height() - 100) {
                loadData();
            }
        });
        var page = 1;
        var stop = false;
        var inLoading = false;
        function loadData(){
            if(stop || inLoading)
                return;


            inLoading = true;
            $.post('{{route('userpanel.sponsor.getData')}}',{_token:'{{csrf_token()}}',page:page},function (d) {

                $(".loading").hide();
                var out = '';
                var maleIcon = '{{asset('images/male.svg')}}';
                var femaleIcon = '{{asset('images/female.svg')}}';

                d.data.data.map(function (val) {
                    out += '<div  class="col-lg-4 col-sm-12">\n' +
                        '<div class="card text-white bg-gradient-dark bg-white text-left">\n' +
                        '<div class="card-content d-flex">\n' +
                        '<div class="card-body">\n' +
                        '<img src="' + (val.selfie == null ? (val.gender == 'Male' ? maleIcon : femaleIcon) : (val.selfie)) + '" alt="" width="100" height="100" style="object-fit:cover" class="float-left  mx-1 my-1">\n' +
                        '\n' +
                        '<h4 class="card-title text-white mt-50">' + val.name + '</h4>\n' +
                        '<p class="card-text mb-0">' + (val.city || '-') + '</p>\n' +
                        '<p class="card-text mb-0">' + val.job + '</p>\n' +
                        '<p class="card-text mb-1">{{__('web/sponsor.waiting')}} ' + val.waiting + '</p>\n' +
                        '<div class="badge badge-primary badge-md mr-1 mb-1 position-absolute" style="top: 12px;right: 0px">' + val.age + ' y/o</div>\n' +
                        '</div>\n' +
                        '</div>\n' +
                        '</div>\n' +
                        '</div>';

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
