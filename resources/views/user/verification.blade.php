@extends('layouts.contentLayoutMaster')
@section('title', __('web/verification.verification'))
@section('content')
    <section>
        <form id="verificationForm" enctype="multipart/form-data" action="{{route('userpanel.Verification.store')}}" method="post">
            @csrf
            <div class="row ">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{__('web/verification.verification')}}</h4>
                            <style>
                                @media (max-width: 767px) {
                                    .verificationStatus{
                                        position: static !important;
                                        margin-top: 1rem;
                                    }
                                }
                            </style>
                            <div class="badge verificationStatus badge-primary position-absolute " style="right: 1.5rem;top: 1.5rem;">
                                <span>Status : {{empty($status) ? __('web/verification.pending') : $status}}</span>
                            </div>

                        </div>
                        <div class="card-content">
                            <div class="card-body">
                                <p>{{__('web/verification.verification_purpose')}}</p>
                                <p>{{__('web/verification.scanned_alert')}}</p>
                                <div class="col-md-12 mt-3">

                                </div>
                                <div class="col-12">
                                    <fieldset>
                                        <div class="vs-checkbox-con vs-checkbox-primary">
                                            <input type="checkbox" name="accept" style="width: 5%" value="1">
                                            <span class="vs-checkbox vs-checkbox-lg">
                                                      <span class="vs-checkbox--check">
                                                        <i class="vs-icon feather icon-check"></i>
                                                      </span>
                                                    </span>
                                            <span class="">{{__('mobile.kyc_accept')}}</span>
                                        </div>
                                    </fieldset>
                                    <fieldset>
                                        <div class="vs-checkbox-con vs-checkbox-primary">
                                            <input type="checkbox" name="pdc" style="width: 5%" value="1">
                                            <span class="vs-checkbox vs-checkbox-lg">
                                                      <span class="vs-checkbox--check">
                                                        <i class="vs-icon feather icon-check"></i>
                                                      </span>
                                                    </span>
                                            <span class="">{{__('mobile.i_have_read_and_accept_the')}} <a class="openPage" href="#" data-src="{{route('page.index',['PersonalDataConsent','mobile'=>'1'])}}">{{__('mobile.personal_data_consent')}}</a>.</span>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row match-height">
                @if(!$profile->is_local())
                    <div class="col-md-{{$profile->is_local() ? '6' : '4'}}">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">{{__('web/verification.verify_visa')}}</h4>

                                </div>
                                <div class="card-content">

                                    <div class="card-body">
                                        <p>{{__('web/verification.verify_visa_desc')}}</p>
                                        <div class="col-md-12 mt-3 ">
                                            <div class="d-flex justify-content-center align-items-center">
                                                <div class="w-50 d-flex justify-content-end align-items-center flex-column">
                                                    <img id="visa-img" data-selected="{{empty($visa) ? '0' : '1'}}" src="{{$visa ?? asset('images/passport.png')}}" style="height: 250px;max-width: 200%"/>
                                                    <input type="file"   accept="image/*"  name="visa" class="form-control mt-1 d-none">
                                                    <br>
                                                    <button type="button" class="btn btn-primary choose_file">
                                                        Choose File
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>
                @endif
                <div class="col-md-{{$profile->is_local() ? '6' : '4'}}">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{__('web/verification.verify_identity')}}</h4>

                        </div>
                        <div class="card-content">

                            <div class="card-body">
                                <p>{{__('web/verification.verify_identity_desc')}}</p>
                                <div class="col-md-12 mt-3 ">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <div class="w-50 d-flex justify-content-end align-items-center flex-column">
                                            <img id="myKad-img" data-selected="{{empty($passport) ? '0' : '1'}}"  src="{{$passport ?? ($profile->is_local() ? asset('images/mykad.png') : asset('images/passport.png')) }}" style="height: 250px;max-width: 200%"/>
                                            <input type="file"  accept="image/*"  name="myKad" class="form-control mt-1 d-none">
                                            <br>
                                            <button type="button" class="btn btn-primary choose_file">
                                                Choose File
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-{{$profile->is_local() ? '6' : '4'}}">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{__('web/verification.selfie')}}</h4>

                    </div>
                    <div class="card-content">

                        <div class="card-body">
                            <p>{{__('web/verification.selfie_desc')}}</p>
                            <div class="col-md-12 mt-3">
                                <div class="d-flex justify-content-center align-items-center">
                                    <div class="w-50 d-flex justify-content-end align-items-center flex-column">
                                        <div id="my_camera"></div>
                                        <div id="my_preview">@if(!empty($selfie))<img src="{{$selfie}}" style="height: 250px;max-width: 200%" />@else
                                            <img id="selfie-img" src="{{$passport ?? asset('images/selfie.png')}}" style="height: 250px;max-width: 200%"/>

                                            @endif </div>
                                        <input type="hidden" data-selected="{{empty($selfie) ? '0' : '1'}}"   name="selfie_upload" class="form-control mt-1">


                                        <br>
                                        <button type="button" class="btn btn-primary take_snp">
                                            Take photo
                                        </button>
{{--                                        <img id="selfie-img" data-selected="{{empty($selfie) ? '0' : '1'}}" src="{{$selfie ?? asset('images/selfie.png')}}" style="height: 250px; width:250px;max-width: 200%"/>--}}
{{--                                        <input type="file" name="selfie"   accept="image/*"  class="form-control mt-1">--}}
                                    </div>
                                </div>
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
    </section>

@endsection
@section('mystyle')
    <style>
        .invalid-feedback{
            display: block;
            width: auto;
        }
    </style>
@endsection
@section('myscript')
    <script src="{{asset('js/webcam.min.js')}}"></script>
    <script language="JavaScript">

        function startwb(){
            console.log("started");
            Webcam.set({
                width: 320,
                height: 240,
                image_format: 'jpeg',
                jpeg_quality: 90
            });
            Webcam.attach( '#my_camera' );

            $("#my_preview").hide();
        }

        function take_snapshot() {
            if(!Webcam.loaded)
                return startwb();
            console.log("started2");
            // take snapshot and get image data
            Webcam.snap( function(data_uri) {
                // display results in page
                $("#my_preview").show();
                $("[name=selfie_upload]").val(data_uri);
                $("[name=selfie_upload]").attr('data-selected','1');

                document.getElementById('my_preview').innerHTML = '<img src="'+data_uri+'"/>';
            } );
        }
        $(".choose_file").on("click",function (e) {
            $(this).parent("div").find("input[type=file]").click();
        });
        $(".take_snp").on("click",function (e) {
            if(!Webcam.loaded)
                return take_snapshot();
            $("#my_camera").hide();
            $("#my_preview").show();
            take_snapshot()

        })
    </script>
    <script>
        $("#verificationForm").on("submit",function (e) {
            Validation.clearAllValidation();
            var accept = $("input[name=accept]");
            var pdc = $("input[name=pdc]");

            if(!accept.is(":checked")) {
                Validation.setInvalid(accept.parents('.vs-checkbox-con'), '@lang('web/auth.required')');
                return false;
            }
            if(!pdc.is(":checked")) {
                Validation.setInvalid(pdc.parents('.vs-checkbox-con'), '@lang('web/auth.required')');
                return false;
            }
        @if(!$profile->is_local())
            var visa = $("#visa-img");
            if(visa.attr('data-selected') != '1'){
                Validation.setInvalid(visa, '{{__('web/auth.required')}}');
                return false;
            }
        @endif

            var mykad_img = $("#myKad-img");
            if(mykad_img.attr('data-selected') != '1'){
                Validation.setInvalid(mykad_img, '{{__('web/auth.required')}}');
                return false;
            }
            var selfie_img = $("[name=selfie_upload]");
            if(selfie_img.attr('data-selected') != '1'){
                Swal.fire({
                    title: 'Error',
                    text: 'Selfie is Required.',
                    type: 'error',
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Ok',
                    confirmButtonClass: 'btn btn-primary',
                    buttonsStyling: false,
                });
                return false;
            }
            $.fn.matchHeight._update();

            $(".loading").show();
            var form = new FormData();
            var blb = b64toBlob($("[name=selfie_upload]").val());
            form.append('_token','{{csrf_token()}}');

            if($("[name=selfie_upload]").val().length > 0)
                form.append('selfie',blb,'selfie.jpg');
            if($("[name=myKad]")[0].files[0])
                form.append('myKad',$("[name=myKad]")[0].files[0],'myKad.jpg');
            if($("[name=visa]").length > 0)
                form.append('visa',$("[name=visa]")[0].files[0],'myKad.jpg');

            $.ajax({
                url: "{{route('userpanel.Verification.store')}}",
                data: form,
                processData: false,
                contentType: false,
                type: 'POST',
                success: function ( data ) {
                    if(data == 'success')
                    window.location = '{{route('userpanel.order.index')}}';
                    else {
                        $(".loading").hide();
                        Swal.fire({
                            title: 'Error',
                            text: "Your selfie dosent match with your passport/mykad",
                            type: 'error',
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'Ok',
                            confirmButtonClass: 'btn btn-primary',
                            buttonsStyling: false,
                        });
                    }
                }
            });
            return false;
        })
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
        function readURL(input,selector) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function(e) {
                    $(selector).attr('src', e.target.result);
                    $(selector).attr('data-selected', '1');
                }

                reader.readAsDataURL(input.files[0]);
            }
        }
        $("input[name=selfie]").change(function() {
            readURL(this,'#selfie-img');
        });

        $("input[name=myKad]").change(function() {
            readURL(this,'#myKad-img');
        });
        $("input[name=visa]").change(function() {
            readURL(this,'#visa-img');
        });


        $("#myKad-img , #selfie-img","#visa-img").on("click",function (e) {
            $(this).parent("div").find("input").click();
        })


    </script>
@endsection
