@extends('layouts.contentLayoutMaster')
@section('title', __('web/hospital.panel_hospital'))
@section('content')
    <div class="clearfix"></div>
    <div class="row match-height">
        <div class="col-md-12">
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
                                <div class="video-container" style="display: flex;width: 100%;justify-content: center;align-items: center; position: relative">
                                    <video id="preview" style="border: 1px solid #ccc;background-color: #000;border-radius: 10px"></video>
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
    </div>

@endsection
@section('mystyle')
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

        const video = document.getElementById('preview');
        const canv = document.getElementById('land');

        function screenResize() {
            video.style.width = '640px';
            video.style.height = '400px';
        }
        screenResize()
        var set = false;
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
                        $("input[name=type]").val("face");
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
            console.log(content);
            $("input[name=uuid]").val(content);
            $("input[name=type]").val("barcode");
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


    </script>
@endsection
