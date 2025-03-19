@extends('layouts.contentLayoutMaster')
@section('title', 'Edit Company Profile')
@section('content')
    <div class="clearfix"></div>
    <section id="validation">
        <div class="row">
            <div class="offset-0 col-md-12">

                <form action="{{route('userpanel.dashboard.profile.save')}}" method="post" id="profileForm" >
                    @csrf
                    <div class="row match-height">
                        <div class="col-md-6">
                            <section class="card">
                                <div class="card-header">
                                    <h4 class="card-title">{{__("web/profile.address_header")}}</h4>
                                </div>
                                <div class="card-content">
                                    <div class="card-body">
                                        <div class="card-text">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label>{{__("web/profile.company_reg_no")}}</label>
                                                        <input type="text" class="form-control" name="reg_no" value="{{$profile->reg_no ?? ''}}" disabled placeholder="{{__("web/profile.company_reg_no")}}">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label>{{__("web/profile.company_name")}}</label>
                                                        <input type="text" class="form-control required" name="name" value="{{$profile->name ?? ''}}" placeholder="{{__("web/profile.company_name")}}">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label>{{__("web/profile.company_phone")}}</label>
                                                        <input type="text" class="form-control required" name="tel_no" value="{{$profile->tel_no ?? ''}}" placeholder="{{__("web/profile.company_phone")}}">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label>{{__("web/profile.residential_address")}} 1</label>
                                                        <input type="text" class="form-control required" name="address1" value="{{$profile->address->address1 ?? ''}}" placeholder="{{__("web/profile.residential_address")}} 1">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label>{{__("web/profile.residential_address")}} 2</label>
                                                        <input type="text" class="form-control" name="address2" value="{{$profile->address->address2 ?? ''}}" placeholder="{{__("web/profile.residential_address")}} 2">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label>{{__("web/profile.residential_address")}} 3</label>
                                                        <input type="text" class="form-control" name="address3" value="{{$profile->address->address3 ?? ''}}" placeholder="{{__("web/profile.residential_address")}} 3">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label>{{__('web/profile.state')}}</label>
                                                        <select  data-value="{{$profile->address->state ?? ''}}" class="form-control required select2"  name="state">

                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label>{{__('web/profile.city')}}</label>

                                                        <select data-value="{{$profile->address->city ?? ''}}" class="form-control required select2"  name="city">

                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label>{{__('web/profile.zipcode')}}</label>

                                                        <select data-value="{{$profile->address->postcode ?? ''}}" class="form-control required select2" name="zip_code">

                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label>{{__('web/profile.relationship')}}</label>
                                                        <select data-value="{{$profile->relationship ?? ''}}" class="form-control required select2" name="relationship">
                                                            <option value=""></option>
                                                            @foreach((config('static.relationships') ?? [] ) as $key => $relationship)
                                                                <option @if(($profile->relationship ?? '') == $key) selected @endif value="{{$key}}">{{$relationship}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">{{__('web/product.company_registration_certificate')}}</h4>
                                    </div>
                                    <div class="card-content">
                                        <div class="card-body">
                                            <p class="card-text">{{__('web/product.upload_your_company_documents')}}</p>
                                            <ul>
                                                <li>Business Registration Certificate</li>
                                                <li>Form 24</li>
                                                <li>Form 49</li>
                                                <li>Letter form company in :
                                                    <ul>
                                                        <li>Specifying purpose of paying insurance premium for others</li>
                                                        <li>Indicate authorized signatory person to represent the company</li>
                                                    </ul>
                                                </li>
                                                <li>Copy of mykad/passport of the authorized signatory person</li>
                                            </ul>
                                            <div class="dropzone" id="doc_upload">
                                                @csrf
                                                <div class="dz-message">{{__('web/product.company_documents')}}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary mr-1 mb-1 waves-effect waves-light submit-btn">{{__('web/product.submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
@section('mystyle')
    <link rel="stylesheet" type="text/css" href="{{asset('vendors/css/forms/wizard.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset(('vendors/css/file-uploaders/dropzone.css'))}}">
    <link rel="stylesheet" type="text/css" href="{{asset(('vendors/css/file-uploaders/dropzone.min.css'))}}">
    <style>
        .error{
            color:red;
        }
        .invalid-feedback{
            display: block;
        }
        li{
            padding: 8px;
        }
    </style>

@endsection
@section('myscript')
    <script src="{{asset('vendors/js/extensions/dropzone.min.js')}}"></script>

    <script>

        $("input[name=tel_no]").inputmask("9{1,20}");

        $("#profileForm").on("submit",function (e) {
            Validation.clearAllValidation();
            var tel_no = $("input[name=tel_no]");
            var address = $("input[name=address1]");
            var state = $("select[name=state]");
            var city = $("select[name=city]");
            var zip_code = $("select[name=zip_code]");
            var relationship = $("select[name=relationship]");
            var name = $("input[name=name]");

            if(name.val() == '') {
                Validation.setInvalid(name, '@lang('web/auth.required')');
                return false;
            }
            if(Validation.empty(tel_no)){
                Validation.setInvalid(tel_no,'@lang('web/auth.required')');
                return false;
            }
            if(Validation.empty(address)){
                Validation.setInvalid(address,'@lang('web/auth.required')');
                return false;
            }
            if(Validation.empty(state)){
                Validation.setInvalid(state,'@lang('web/auth.required')');
                return false;
            }
            if(Validation.empty(city)){
                Validation.setInvalid(city,'@lang('web/auth.required')');
                return false;
            }
            if(Validation.empty(zip_code)){
                Validation.setInvalid(zip_code,'@lang('web/auth.required')');
                return false;
            }
            if(Validation.empty(relationship)){
                Validation.setInvalid(relationship,'@lang('web/auth.required')');
                return false;
            }

            if(document.getElementById("doc_upload").dropzone.files.length == 0){
                Validation.setInvalid($(".dropzone"),'{{__('web/charity.select_doc_error')}}');
                return false;
            }

            $(".loading").show();
            return true;
        })

        $("select[name=city]").on("select2:opening",function(e){
            if($(this).find("option").length == 0){
                e.preventDefault();
                $("select[name=state]").select2("open");
            };
        });
        $("select[name=zip_code]").on("select2:opening",function(e){
            if($(this).find("option").length == 0){
                e.preventDefault();
                $("select[name=city]").select2("open");
            };
        });

        $(document).ready(function () {
            $(".loading").show();
            $.get("{{route('wb-api.initPostRegisterIndividual')}}",{},function (res) {
                $(".loading").hide();
                if(res.status == 'success'){

                    var states = '<option value="">Please Select ...</option>';
                    res.data.states.map((val,i)=>{
                        states += '<option value="'+val.uuid+'">'+val.name+'</option>';
                    })
                    $("select[name=state]").html(states);
                    if($("select[name=state]").data('value') != '') {
                        val = $("select[name=state]").data('value');
                        $("select[name=state]").attr('data-value', '');
                        $("select[name=state]").val(val).change();
                    }else{

                    }
                }
            })
        });
        $("select[name=state]").on("change",function(e){
            if($(this).val() =='' || $(this).val() ==null)
                return;
            $.get("{{route('stateList')}}",{id:$(this).val()},function(res){
                $(".loading").hide();
                if(res.status == 'success'){
                    var cities = '<option value="">Please Select ...</option>';
                    res.data.map((val,i)=>{
                        cities += '<option value="'+val.uuid+'">'+val.name+'</option>';
                    })
                    $("select[name=city]").html(cities);
                    if($("select[name=city]").data('value') != '') {
                        var val = $("select[name=city]").data('value');
                        $("select[name=city]").attr('data-value','');
                        $("select[name=city]").val(val).change();

                    }else{

                    }
                }
            })
        })
        $("select[name=city]").on("change",function(e){
            if($(this).val() =='' || $(this).val() ==null)
                return;
            $.get("{{route('stateList')}}",{id:$(this).val()},function(res){
                $(".loading").hide();
                if(res.status == 'success'){
                    var zip_codes = '<option value="">Please Select ...</option>';
                    res.data.map((val,i)=>{
                        zip_codes += '<option value="'+val.uuid+'">'+val.name+'</option>';
                    })
                    $("select[name=zip_code]").html(zip_codes);
                    if($("select[name=zip_code]").data('value') != '') {
                        var val = $("select[name=zip_code]").data('value');
                        $("select[name=zip_code]").attr('data-value','');
                        $("select[name=zip_code]").val(val).change();

                    }else{
                        $(".loading").hide();
                    }
                }
            })
        })
    </script>
    <script>
        Dropzone.options.docUpload = {
            url: "{{route('userpanel.dashboard.profile.doc')}}",
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
                    url: "{{route('userpanel.dashboard.profile.doc.remove')}}",
                    data: "id="+name,
                    dataType: 'html'
                });
                var _ref;
                return (_ref = file.previewElement) != null ? _ref.parentNode.removeChild(file.previewElement) : void 0;
            },
            init: function () {

                this.on("addedfile", function(file) {
                    if (file.type == 'application/pdf') {
                        this.emit("thumbnail", file, "{{asset('images/pdf.png')}}");
                    }
                });

                var Dropzone = this;
                @foreach($profile->documents()->where("type","company_doc")->get() as $doc)


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
