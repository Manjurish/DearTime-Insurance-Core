@extends('layouts.contentLayoutMaster')
@section('title', 'Charity Applicant')
@section('content')
    <section>
        <div>
            <style>
                .invalid-feedback{
                    display: block;
                    width: auto;
                }
            </style>
            <form enctype="multipart/form-data" action="{{route('userpanel.charity.save')}}" method="post">
                <div class="row match-height">
                    <div class="col-md-9">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">{{__('web/charity.charity_title')}}</h4>
                                <style>
                                    @media (max-width: 767px) {
                                        .charityStatus{
                                            position: static !important;
                                            margin-top: 1rem;
                                        }
                                    }
                                </style>
                                <div class="badge charityStatus badge-primary position-absolute " style="right: 1.5rem;top: 1.5rem;">
                                    <span>Status : {{$charity->active == '3' ? __('web/charity.pending_data_entry') : __('web/charity.pending_validation')}}</span>
                                </div>
                            </div>
                            <div class="card-content">

                                <div class="card-body">
                                    <div>

                                        @csrf

                                        <p>{{__('web/charity.charity_desc')}}</p>


                                        <div class="form-group" >
                                            <label>{{__('web/charity.about_self')}}</label>
                                            <input type="text" class="form-control  @error('about_self') is-invalid @enderror" name="about_self" value="{{$charity->about_self}}" placeholder="{{__('web/charity.about_self')}}">
                                            @error('about_self')
                                            <div class="invalid-feedback">
                                                {{$message}}
                                            </div>
                                            @enderror
                                        </div>

                                        <div class="form-group" >
                                            <label>{{__('web/charity.sponsor_thank')}}</label>

                                            <input type="text" class="form-control   @error('sponsor_thank_note') is-invalid @enderror" name="sponsor_thank_note" value="{{$charity->sponsor_thank_note}}" placeholder="{{__('web/charity.sponsor_thank')}}">
                                            @error('sponsor_thank_note')
                                            <div class="invalid-feedback">
                                                {{$message}}
                                            </div>
                                            @enderror
                                        </div>

                                        <div class="">
                                            <p class="mb-0">{{__('web/charity.dependants')}} (<span id="dependants_value">{{$charity->dependants}}</span>)</p>
                                            <input type="hidden" name="dependants"  value="{{$charity->dependants}}">
                                            <div class="my-1" id="dependants_slider"></div>
                                            @error('dependants')
                                            <div class="invalid-feedback">
                                                {{$message}}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">{{__('web/charity.selfie')}}</h4>
                            </div>
                            <div class="card-content">
                                <div class="card-body">
                                    <p class="card-text">{{__('web/charity.selfie_desc')}}</p>

                                    <div class="w-100 d-flex align-items-center justify-content-center flex-column">
                                        <img id="selfie-img" class="mt-2 img-fluid " style="max-height: 180px;min-height: 150px;" @if(($charity->documents()->where("type","selfie")->first()->Link ?? null) == null) data-selected="0" @else data-selected="1" @endif src="{{ $charity->documents()->where("type","selfie")->first()->Link ?? asset('images/selfie.png')}}">
                                        <input type="file" name="selfie" class="d-none">
                                        <button type="button" class="btn btn-primary mt-1 changeImg">
                                            {{__('web/charity.selfie_change')}}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <div class="row match-height">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{__('web/charity.monthly_household_income_proof')}}</h4>
                        </div>
                        <div class="card-content">
                            <div class="card-body">
                                <p class="card-text">{{__('web/charity.monthly_household_income_proof_desc')}}</p>
                                <ul>
                                    <li>Salary slip (past 3 months)</li>
                                    <li>Latest Income Tax Return Form</li>
                                    <li>Bank statement (past 3 months)</li>
                                    <li>Latest EPF statement</li>
                                    <li>JKM (Jabatan kebajikan Masyarakat) approval document</li>

                                </ul>
                                <div class="dropzone" id="doc_upload">
                                    @csrf
                                    <div class="dz-message">{{__('web/charity.monthly_household_income_proof_desc')}}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    {{__('web/charity.save')}}
                </button>
            </div>
        </form>
        </div>
    </section>

@endsection
@section('mystyle')
    <link rel="stylesheet" type="text/css" href="{{asset('vendors/css/extensions/nouislider.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset(('vendors/css/file-uploaders/dropzone.css'))}}">
    <link rel="stylesheet" type="text/css" href="{{asset(('vendors/css/file-uploaders/dropzone.min.css'))}}">
@endsection
@section('myscript')
    <script src="{{asset('vendors/js/extensions/nouislider.min.js')}}"></script>
    <script src="{{asset('vendors/js/extensions/dropzone.min.js')}}"></script>
    <script>
        $("form").on("submit",function (e) {
            Validation.clearAllValidation();
            var about_self = $("input[name=about_self]");
            var sponsor_thank_note = $("input[name=sponsor_thank_note]");
            var selfie_img = $("#selfie-img");
            var docs = $("#doc_upload");
            if(Validation.empty(about_self)) {
                Validation.setInvalid(about_self, '{{__('web/auth.required')}}');
                return false;
            }
            if(Validation.empty(sponsor_thank_note)) {
                Validation.setInvalid(sponsor_thank_note, '{{__('web/auth.required')}}');
                return false;
            }

            if(selfie_img.attr('data-selected') != '1'){
                Validation.setInvalid(selfie_img, '{{__('web/auth.required')}}');
                return false;
            }
            if(document.getElementById('doc_upload').dropzone.files.length == 0){
                Validation.setInvalid(docs, '{{__('web/charity.select_doc_error')}}');
                return false;
            }

            return  true;
        })
    </script>
    <script>
        function readURL(input,selector) {

            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function(e) {
                    $(selector).attr('src', e.target.result);
                    $(selector).attr('data-selected', '1');
                };

                reader.readAsDataURL(input.files[0]);
                $.fn.matchHeight._update();
            }
        }
        $("input[name=selfie]").change(function() {
            readURL(this,'#selfie-img');
        });
        $("#selfie-img , .changeImg").on("click",function (e) {
            $("input[name=selfie]").click();
        })

        var personal = $("input[name=dependants]").val();

        var dependants_slider = document.getElementById('dependants_slider');
        noUiSlider.create(dependants_slider, {
            start: 0,
            connect: [true, false],
            behaviour: 'drag',
            range: {
                'min': 0,
                'max': 11
            }
        });
        dependants_slider.noUiSlider.on('update', function (values, handle) {
            var value = Math.round(values[0]);
            $("#dependants_value").html(value == 11 ? '10+' : value);
            $("input[name=dependants]").val(value);
        });
        dependants_slider.noUiSlider.set([personal]);

        Dropzone.options.docUpload = {
            url: "{{route('userpanel.charity.upload.doc',$charity->uuid)}}",
            paramName: "image", // The name that will be used to transfer the file
            maxFilesize: 5, // MB
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            maxFiles: 10,
            parallelUploads: 5,
            addRemoveLinks: true,
            dictMaxFilesExceeded: "{{__('web/product.upload_limit_error')}}",
            dictRemoveFile: "",
            dictCancelUploadConfirmation: "{{__('web/product.cancel_upload_alert')}}",
            accept: function (file, done) {
                if ((file.type).toLowerCase() != "image/jpg" &&
                    (file.type).toLowerCase() != "image/gif" &&
                    (file.type).toLowerCase() != "image/jpeg" &&
                    (file.type).toLowerCase() != "image/png" &&
                    (file.type).toLowerCase() != "application/pdf"
                ) {
                    this.removeFile(file);
                    Swal.fire({
                        title: 'Information',
                        text: "{{__('web/product.only_pdf_img')}}",
                        type: 'info',
                        showCancelButton: false,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Ok',
                        confirmButtonClass: 'btn btn-primary',
                        buttonsStyling: false,
                    });
                }
                else {
                    done();
                }
            },
            removedfile: function(file) {
                var name = file.name;

                $.ajax({
                    type: 'GET',
                    url: "{{route('userpanel.charity.upload.remove',$charity->uuid)}}",
                    data: "id="+name,
                    dataType: 'html'
                });
                var _ref;
                return (_ref = file.previewElement) != null ? _ref.parentNode.removeChild(file.previewElement) : void 0;
            },
            init: function () {

                var Dropzone = this;
                this.on("addedfile", function(file) {
                        if (file.type == 'application/pdf') {
                            this.emit("thumbnail", file, "{{asset('images/pdf.png')}}");
                        }
                    });
                @foreach($charity->documents()->where("type","salary_proof")->get() as $doc)
                    @if($doc->ext == 'pdf')
                        var mockFile = { name: "{{$doc->name}}", size: {{$doc->size}}, type: 'application/pdf' };
                        this.options.addedfile.call(this, mockFile);
                        this.options.thumbnail.call(this, mockFile, "{{asset('images/pdf.png')}}");
                    @else
                        var mockFile = { name: "{{$doc->name}}", size: {{$doc->size}}, type: 'image/jpg' };
                        this.options.addedfile.call(this, mockFile);
                        this.options.thumbnail.call(this, mockFile, "{{$doc->thumbLink}}");
                    @endif

                    mockFile.previewElement.classList.add('dz-success');
                    mockFile.previewElement.classList.add('dz-complete');
                    this.files.push(mockFile);

                @endforeach
                $.fn.matchHeight._update()
            }
        };


    </script>
@endsection
