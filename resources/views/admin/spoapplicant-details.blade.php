@extends('layouts.contentLayoutMaster')


@section('content')
    <section id="description" class="card">
        <div class="card-header">
            <h4 class="card-title"></h4>
        </div>
        <div class="card-content">
            <div class="card-body">
                <section id="nav-justified">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card overflow-hidden">
                                <div class="card-header">
                                    <h4 class="card-title">Sponsored Insurance Applicant Details</h4>
                                </div>
                                <div class="card-content">
                                    <div class="card-body">
                        
                                        <p>Name : {{$name}}</p>
                                        <p>Application No : {{$data->ref_no}}</p>
                                        <p>Application Status:</p>
                                        
                                        <div class="col-md-4">
                                        <form class="form form-horizontal" action="{{route('admin.Spo.statusupdate')}}"  method="post" enctype="multipart/form-data">
                                            @csrf
                                        <input type="hidden" name="id" value="{{ $data->uuid }}">
                                        <select name="applicant_status" class="form-control select" {{ $data->status=='REJECTED'|| $data->status=='QUEUE'||$data->status=='ACTIVE'||$data->status=='CANCELLED'? 'disabled':''}} >
                                        {{-- <option @if(($data->status ?? null) == 'SUBMITTED') selected @endif value="SUBMITTED">Submitted</option> --}}
                                        {{-- <option @if(($data->status ?? null) == 'PENDING') selected @endif value="PENDING">Pending</option> --}}
                                        <option value="">Please select</option>
                                        <option @if(($data->status ?? null) == 'REJECTED'||($data->status ?? null) == 'CANCELLED') selected @endif value="REJECTED">REJECTED</option>
                                        <option @if(($data->status ?? null) == 'QUEUE'||($data->status ?? null) == 'ACTIVE') selected @endif value="QUEUE">Verified</option>
                                        {{-- <option @if(($data->status ?? null) == 'APPROVED') selected @endif value="APPROVED"> Approved </option> --}}
                                        </select>
                                        <br/>
                                        <button type="submit" class="btn btn-primary waves-effect waves-light" >
                                            Save
                                        </button>
                                      </form>
                                        </div>
                                        

                                        <br/>
                                        <table class="table table-bordered">
                                            <thead>
                                            <tr>
                                                <td class="text-center">Item</td>
                                                <!-- <td class="text-center">Template</td> -->
                                                <td class="text-center">Uploaded Document</td>
                                                <!-- <td class="text-center">Upload</td> -->
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
                                                            {{$doc['name']}}
                                                            {{-- @if(!empty($doc['link']))
                                                                <a class="badge badge-primary ml-1" href="{{ route('download.resource', base64_encode($doc['link'])) }}" target="_blank"><i class="feather icon-download"></i></a>
                                                            @endif --}}
                                                        </div>
                                                    </td>
                                                    <td>
            
                                                        <p class="m-1 p-0  d-flex justify-content-between align-items-center">
                                                            <a  class="badge badge-primary ml-1"  href="{{ route('admin.dashboard.documentResize',['actual',$doc['url'], $doc['ext']]) }}" target="_blank"><i class="feather icon-download"></i></a>
                                                            </a>
                                                            </p>
                                                    </td>
                                                    {{--<td>
                                                        <div  class="d-flex justify-content-center align-items-center">
                                                            @csrf
                                                            <input type="file" name="claim_form[{{$doc['name']}}]" class="d-none">
                                                            <a href="#" class="badge badge-primary ml-1 upload"><i class="feather icon-upload"></i></a>
                                                        </div>
                                                    </td>--}}
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                        <table class="table table-bordered">
                                            <thead>
                                            <tr>
                                                <td class="text-center">Members</td>
                                                <!-- <td class="text-center">Template</td> -->
                                                <td class="text-center">Uploaded Document</td>
                                                <!-- <td class="text-center">Upload</td> -->
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($members as $member)
                                            @foreach($member->documents()->get() as $document)
                                                <tr>
                                                    
                                                    <td>
                                                        <div  class="d-flex justify-content-center align-items-center">
                                                            {{$member->name}}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div  class="d-flex">
                                                            {{-- {{$member->documents->pluck('name')->implode(' ')}} --}}
                                                            {{$document->name}}
                                                            {{-- @if(!empty($doc['link']))
                                                                <a class="badge badge-primary ml-1" href="{{ route('download.resource', base64_encode($doc['link'])) }}" target="_blank"><i class="feather icon-download"></i></a>
                                                            @endif --}}
                                                        </div>
                                                    </td>
                                                    <td>
            
                                                        <p class="m-1 p-0  d-flex justify-content-between align-items-center">
                                                            <a  class="badge badge-primary ml-1"  href="{{ route('admin.dashboard.documentResize',['actual',$document->url, $document->ext]) }}" target="_blank"><i class="feather icon-download"></i></a>
                                                            </a>
                                                            {{-- {{ $doc['url'] }} --}}
                                                            {{-- {{$member->documents->pluck('url')->implode(' ')}} --}}
                                                            </p>
                                                    </td>
                                                    {{--<td>
                                                        <div  class="d-flex justify-content-center align-items-center">
                                                            @csrf
                                                            <input type="file" name="claim_form[{{$doc['name']}}]" class="d-none">
                                                            <a href="#" class="badge badge-primary ml-1 upload"><i class="feather icon-upload"></i></a>
                                                        </div>
                                                    </td>--}}
                                               
                                                </tr>
                                                @endforeach
                                            @endforeach
                                            </tbody>
                                        </table>
                                        {{-- <p>Date Of Birth  : {{$nominee->dob}}</p> --}}
                                       
                                        <br>
                                        <br>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </section>
@endsection
