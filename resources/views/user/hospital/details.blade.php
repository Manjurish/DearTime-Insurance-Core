@extends('layouts.contentLayoutMaster')
@section('title', __('web/hospital.panel_hospital'))
@section('content')
    <div class="row match-height">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Documents</h4>
                </div>

                <div class="card-content">
                    <div class="card-body">
                        <div>
                            <table id="table" class="display table table-data-width">
                                <thead>
                                <tr>
                                    <th>Claim ID</th>
                                    <th>Claimant Name</th>
                                    <th>Owner Name</th>
                                    <th>Coverage Name</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>{{$claim->ref_no}}</td>
                                    <td>{{$claim->ClaimantName}}</td>
                                    <td>{{$claim->OwnerName}}</td>
                                    <td>{{$claim->PolicyName}}</td>
                                    <td>{{$claim->status}}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        @if($coverage->product_name == 'Medical')
                            <div class="d-flex justify-content-center align-items-center">
                                <img src="{{asset('images/medical_front.jpeg')}}" style="width: 40%;border: 2px solid #ccc;">
                            </div>
                            <div class="d-flex justify-content-center align-items-center">
                                <img src="{{asset('images/medical_back.jpeg')}}" style="width: 40%;border: 2px solid #ccc;">
                            </div>
                        @else
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
                                                <div  class="d-flex">
                                                    @if(stripos($doc['name'], 'consent') !== false && $hasConsent)
                                                    consent.pdf
                                                    @else
                                                    {{$doc['name']}}
                                                    @endif
                                                    @if(!empty($doc['link']))
                                                        @if(stripos($doc['name'], 'consent') !== false && $hasConsent)
                                                            <a class="badge badge-primary ml-1" href="{{ $consent_doc['link'] }}" target="_blank"><i class="feather icon-download"></i></a>
                                                        @else
                                                            <a class="badge badge-primary ml-1" href="{{ route('download.resource', $doc['link']) }}" target="_blank"><i class="feather icon-download"></i></a>
                                                        @endif
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
                                                            @if($doc['name'] != 'Consent')
                                                               <a class="badge badge-primary ml-1 remove" href="#" data-href="{{route('userpanel.hospital.upload.remove',[$_doc->url])}}" target="_blank"><i class="feather icon-trash-2"></i></a>
                                                            @endif
                                                            </span>
                                                        </p>
                                                    @endforeach
                                                @endif
                                            </td>
                                            <td>
                                                    @if(stripos($doc['name'], 'consent') !== false && $hasConsent)
                                                    @else
                                                    <div  class="d-flex justify-content-center align-items-center">
                                                        @csrf
                                                        <input type="file" name="claim_form[{{$doc['name']}}]" class="d-none">
                                                        <a href="#" class="badge badge-primary ml-1 upload"><i class="feather icon-upload"></i></a>
                                                    </div>
                                                @endif
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
@endsection
@section('mystyle')
@endsection
@section('myscript')
    <script src="{{asset('js/jquery.form.js')}}"></script>
    <script>
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
                    window.location = '';
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
            window.location = '';
        });
        return false;
    })
</script>
@endsection
