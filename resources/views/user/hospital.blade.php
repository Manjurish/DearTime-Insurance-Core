@extends('layouts.contentLayoutMaster')
@section('title', __('web/hospital.panel_hospital'))
@section('content')
    <div class="clearfix"></div>
    <div class="row match-height">
        <div class="col-md-8 col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{__('web/hospital.scan')}}</h4>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        @csrf
                        <div class="form-body">
                            <div class="d-flex justify-content-center align-items-center flex-column cam-cnt">
                                <form id="search-uuid" action="{{route('userpanel.hospital.create')}}" method="post">
                                    @csrf
                                    <input type="hidden" name="uuid" value="{{request()->input('uuid')}}" />
                                    <input type="hidden" name="type" value="{{request()->input('type')}}" />
                                </form>
                                {{--                                <div style="position:relative;">--}}
                                {{--                                    <video id="preview" class="mb-2" style="width:400px;height:400px;border: 1px solid #ccc;background-color: #000;border-radius: 10px"></video>--}}
                                {{--                                    <canvas id="land" style="position: absolute; top: 0px; left: 0px; width: 400px; height: 400px;" ></canvas>--}}
                                {{--                                    <canvas id="canvas" style="overflow:auto;position: absolute;top: 0;left: 0"></canvas>--}}
                                {{--                                </div>--}}

                                <div class="video-container" style="display: flex;width: 100%;justify-content: center;align-items: center; position: relative">
                                    <video id="preview" style="border: 1px solid #ccc;background-color: #000;border-radius: 10px"></video>
                                    {{--                                    <canvas id="land" style="position: absolute; top: 0px; left: 0px; width: 400px; height: 400px;" ></canvas>--}}
                                    <canvas id="canvas" style="overflow:auto;position: absolute;top: 0;left: 0"></canvas>
                                </div>

                                <button type="button" class="btn btn-primary camera">start / stop</button>
                                <p class="cam_dt"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{__('web/hospital.coverage_info')}}</h4>
                </div>
                <div class="card-content">

                    @if(!empty($coverage))
                        <div class="card-body">
                            <p>{{__('web/hospital.owner')}} : {{$coverage->owner->name ?? $user->profile->name ?? '-'}}</p>
                            @if(!empty($coverage))
                                <p>{{__('web/hospital.product')}} : {{$coverage->product_name}}</p>
                                <p>{{__('web/hospital.coverage')}} : RM{{number_format($coverage->coverage)}}</p>
                                {{--                        <p>{{__('web/hospital.premium')}} : RM{{number_format($coverage->Payable)}}</p>--}}
                                {{--                        <p>{{__('web/hospital.due_date')}} : {{$coverage->next_payment_on}}</p>--}}
                            @endif
                        </div>
                    @else
                        <div class="card-body">
                            <p>{{__('web/hospital.scan_to_show')}}</p>
                        </div>
                        @if(!empty($claims))
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <td>Name</td>
                                    <td>Action</td>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($claims as $cl)
                                    <tr>
                                        <td>{{$cl->OwnerName}} - {{$cl->PolicyName}}</td>
                                        <td><a href="#" data-id="{{$cl->uuid}}" data-policy="{{$cl->PolicyName}}" class="claim btn btn-primary">Claim</a></td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @endif
                        @if(!empty($coverages))
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <td>Name</td>
                                    <td>Action</td>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($coverages as $cov)
                                    <tr>
                                        <td>{{$cov->owner->name ?? '-'}} - {{$cov->product_name}}</td>
                                        <td><a href="#" data-id="{{$cov->uuid}}"  data-policy="{{$cov->product_name}}" class="claim btn btn-primary">Claim</a></td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
    @if(!empty($coverage))

        @php
            $death_docs = [
                ['name'=>'APS- Death','link'=>asset('documents/1. DS - Death (for PH) 20201017.xlsm')],
                ['name'=>'Post Mortem','link'=>null],
                ['name'=>'Toxicology Report','link'=>null],
            ];
            $disability_docs = [
                    ['name'=>'APS- Disability','link'=>asset('documents/1. DS - Disability (for PH) 20201017.xlsm')],
                    ['name'=>'Other','link'=>null],
            ];
            $disability = $coverage->product_name == 'Disability' || request()->input('type') == 'disability';
            $death = $coverage->product_name == 'Death' || request()->input('type') == 'death';
            if($disability)
                $docs = $disability_docs;
            elseif($death)
                $docs = $death_docs;
            else
                $docs = [];

        @endphp
        <div class="row match-height">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Documents</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            @if($coverage->product_name == 'Medical')
                                <div class="d-flex justify-content-center align-items-center">
                                    <img src="{{asset('images/medical_card.png')}}" style="width: 40%;border: 2px solid #ccc;">
                                </div>
                            @else
                                <p class="card-text">Documents</p>
                                <form id="uploader" enctype="multipart/form-data" action="{{route('userpanel.hospital.upload.doc',$claim->uuid)}}" method="post">
                                    <table class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <td class="text-center">Item</td>
                                            <td class="text-center">Template</td>
                                            <td class="text-center">Processed Document</td>
                                            <td class="text-center">Upload</td>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($docs as $doc)
                                            <tr>
                                                <td>
                                                    <div  class="d-flex justify-content-center align-items-center">
                                                        {{$loop->index +1 }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div  class="d-flex justify-content-center align-items-center">
                                                        {{$doc['name']}}
                                                        @if(!empty($doc['link']))
                                                            <a class="badge badge-primary ml-1" href="{{asset('documents/1. DS - Death (for PH) 20201017.xlsm')}}" target="_blank"><i class="feather icon-download"></i></a>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    @if(!empty($claim))
                                                        {{--                                                <ul style="list-style: none">--}}
                                                        @foreach($claim->documents()->where("type",$doc['name'])->get() ?? [] as $_doc)
                                                            <p class="m-1 p-0  d-flex justify-content-between align-items-center"><a href="{{$_doc->link}}">{{$_doc->name}}</a>
                                                                <span class="">
                                                       <a class="badge badge-primary ml-1" href="{{$_doc->link}}" target="_blank"><i class="feather icon-download"></i></a>
                                                       <a class="badge badge-primary ml-1 remove" href="#" data-href="{{route('userpanel.hospital.upload.remove',[$_doc->url])}}" target="_blank"><i class="feather icon-trash-2"></i></a>
                                                    </span>
                                                            </p>
                                                        @endforeach
                                                        {{--                                                </ul>--}}
                                                    @endif
                                                </td>
                                                <td>
                                                    <div  class="d-flex justify-content-center align-items-center">
                                                        @csrf
                                                        <input type="file" name="claim_form[{{$doc['name']}}]" class="d-none">
                                                        <a href="#" class="badge badge-primary ml-1 upload"><i class="feather icon-upload"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
@section('mystyle')
    <link rel="stylesheet" type="text/css" href="{{asset('vendors/css/extensions/nouislider.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset(('vendors/css/file-uploaders/dropzone.css'))}}">
    <link rel="stylesheet" type="text/css" href="{{asset(('vendors/css/file-uploaders/dropzone.min.css'))}}">
    <style>
        canvas {
            position: absolute;
        }
    </style>
@endsection
@section('myscript')

    <script src="{{asset('js/instascan.min.js')}}"></script>
    <script src="{{asset('js/face-api.min.js')}}"></script>
    <script src="{{asset('js/jquery.form.js')}}"></script>
    <script type="text/javascript">


        var scanner = new Instascan.Scanner({ video: document.getElementById('preview'), mirror: false });
        faceapi.nets.tinyFaceDetector.loadFromUri('{{asset('/models')}}');
        faceapi.nets.faceLandmark68Net.loadFromUri('{{asset('/models')}}');
        faceapi.nets.faceRecognitionNet.loadFromUri('{{asset('/models')}}');
        faceapi.nets.faceExpressionNet.loadFromUri('{{asset('/models')}}');


        $(document).ready(function() {
            $("html, body").animate({ scrollTop: $(document).height() }, 1000);
        });

        $(".remove").on("click",function (e) {
            e.preventDefault();
            var link = $(this).data('href');
            Swal.fire({
                title: "Delete Confirmation",
                text: 'Are you sure ?',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes',
                cancelButtonText: 'Cancel',
                confirmButtonClass: 'btn btn-primary',
                cancelButtonClass: 'btn btn-primary ml-1',
                buttonsStyling: false,
            }).then(function (result) {
                if(result.value){
                    // window.location = link;
                    $(".loading").show();
                    $.get(link,{},function(e){
                        $(".loading").hide();
                        $("#search-uuid").submit();
                    })
                }
            });
        })
        $(".upload").on("click",function(e){
            e.preventDefault();
            $(this).parent('div').find('input[type=file]').click();
        });
        $('input[type=file]').on("change",function (e) {
            $("#uploader").submit();
        });
        $("#uploader").on("submit",function(e){
            $(".loading").show();
            $(this).ajaxSubmit(function(res){
                $(".loading").hide();
                $("#search-uuid").submit();
            });
            return false;
        })

        const video = document.getElementById('preview');
        const canv = document.getElementById('land');

        function screenResize() {
            video.style.width = '640px';
            video.style.height = '400px';
        }

        screenResize()




        var set = false;
        //
        var play = function() {

            const canvas = faceapi.createCanvasFromMedia(video);
            let container = document.querySelector(".video-container");
            container.append(canvas)
            const displaySize = { width: video.offsetWidth, height: video.offsetHeight };

            const dims =  faceapi.matchDimensions(canvas, displaySize);


            setInterval(async () => {

                var detections = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceExpressions();

                canvas.getContext("2d").clearRect(0, 0, canvas.width, canvas.height);


                if(detections){
                    //  faceapi.matchDimensions(canv, displaySize)
                    //   const resizedDetections = faceapi.resizeResults(detections, displaySize, true);
                    const resizedDetections = faceapi.resizeResults(detections, dims);

                    faceapi.draw.drawDetections(canvas, resizedDetections);
                    faceapi.draw.drawFaceLandmarks(canvas, resizedDetections);
                    faceapi.draw.drawFaceExpressions(canvas, resizedDetections);


                    if(detections.detection && detections.detection.score > 0.85) {
                        if (!set) {
                            setTimeout(function () {
                                capture(video);
                            }, 1500);
                        }
                    }
                }
            }, 200);
        };
        video.addEventListener('playing',play);
        function capture(video) {
            if(set)
                return;

            var canvas = document.getElementById('canvas');
            //  var video1 = document.getElementById('preview');


            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            canvas.getContext('2d').drawImage(video, 0, 0, video.videoWidth, video.videoHeight);
            var img = document.createElement("img");
            img.src = canvas.toDataURL();
            $("form").append(img);
            $("form img").hide();
            $("canvas").hide();
            set = true;

            $.fn.matchHeight._update();

            $(".loading").show();
            var form = new FormData();
            var blb = b64toBlob($("form img").attr('src'));
            form.append('_token','{{csrf_token()}}');
            form.append('selfie',blb,'selfie.jpg');
            $.ajax({
                url: "{{route('userpanel.hospital.checkSelfie')}}",
                data: form,
                processData: false,
                cache: false,
                contentType: false,
                type: 'POST',
                success: function ( data ) {
                    $(".loading").hide();

                    if(data.status == 'failed'){

                        $('.cam_dt').html(data.data+' <a href="">Please retry</a>');
                        // return Swal.fire({
                        //     title: 'Information',
                        //     text: data.data,
                        //     type: 'info',
                        //     showCancelButton: false,
                        //     confirmButtonColor: '#3085d6',
                        //     confirmButtonText: 'Ok',
                        //     confirmButtonClass: 'btn btn-primary',
                        //     buttonsStyling: false,
                        // }).then(function(res){
                        //     set = false;
                        // });
                    }else{
                        $("input[name=uuid]").val(data.data);
                        $("#search-uuid").submit();
                    }
                    $.fn.matchHeight._update();
                }
            });
            return false;

        }
        function b64toBlob(dataURI) {

            if(!dataURI || dataURI.length == 0)
                return ;
            var byteString = atob(dataURI.split(',')[1]);
            var ab = new ArrayBuffer(byteString.length);
            var ia = new Uint8Array(ab);

            for (var i = 0; i < byteString.length; i++) {
                ia[i] = byteString.charCodeAt(i);
            }
            return new Blob([ab], { type: 'image/jpeg' });
        }



        scanner.addListener('scan', function (content) {
            $("input[name=uuid]").val(content);
            $("#search-uuid").submit();
        });
        $(".claim").on("click",function(d){
            $("input[name=uuid]").val($(this).data('id'));
            var product = $(this).data('policy');
            if(product == 'Accident')
                return Swal.fire({
                    title: "Select Accident Type",
                    text: 'Choose  the Accident Type',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Accidental Death',
                    cancelButtonText: 'Accidental Disability',
                    confirmButtonClass: 'btn btn-primary',
                    cancelButtonClass: 'btn btn-primary ml-1',
                    buttonsStyling: false,
                }).then(function (result) {
                    if(result.value)
                        $("input[name=type]").val('death');
                    else if(result.dismiss && result.dismiss == "cancel")
                        $("input[name=type]").val('disability');
                    else{
                        return false;
                    }

                    $("#search-uuid").submit();
                });
            $("#search-uuid").submit();
        });
        var initialized = false;



        $(".camera").on("click",function (e) {
            if(initialized){
                // set = !set;
                scanner.stop();
                initialized = false;
            }else{
                Instascan.Camera.getCameras().then(function (cameras) {
                    if (cameras.length > 0) {
                        scanner.start(cameras[0]);
                        initialized = true;
                    } else {
                        console.error('No cameras found.');
                        initialized = false;
                    }
                }).catch(function (e) {
                    console.error(e);
                    Swal.fire({
                        title: "Unable to Access Camera",
                        text: e,
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Try Again',
                        cancelButtonText: 'Cancel',
                        confirmButtonClass: 'btn btn-primary',
                        cancelButtonClass: 'btn btn-primary ml-1',
                        buttonsStyling: false,
                    }).then(function (result) {
                        if(result.value){
                            $(".camera").trigger('click')
                        }
                    });

                    initialized = false;
                });
            }
        });

        // setTimeout(function () {
        //     $(".camera").trigger('click');
        // },1000)

    </script>
@endsection
