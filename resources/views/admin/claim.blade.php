@extends('layouts.contentLayoutMaster')
@section('title','Claim Details')
@section('content')
   
    <div class="row match-height">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Claim</h4>
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
                                    <th>Coverage Amount</th>
                                    <th>Hospital</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>{{$claim->ref_no}}</td>
                                    <td>{{$claim->ClaimantName}}</td>
                                    <td>{{$claim->OwnerName}}</td>
                                    <td>{{$claim->PolicyName}}</td>
                                    <td>{{ number_format($claim->coverage->coverage) }}</td>
                                    <td>
                                        @if(!empty($claim->panel_id))
                                            {{ $claim->hospital->name }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{$claim->status}}</td>
                                    <td>


                                    <button data-toggle="modal" data-target="#editModal"
                                         class="btn btn-primary">Log</button>
                                         
                                
                                        <button data-toggle="modal" data-target="#claim-{{ $claim->id }}" {{ ($claim->status=='draft'||$claim->status=='notified')? '' : 'disabled' }}
                                         class="btn btn-primary">Cancel</button>
                                         <br/><br/>
                                         @include('admin.user.canreason')

                                    <form name="claimstatus" class="form form-horizontal" method="GET" enctype="multipart/form-data">
                                    <!-- @csrf -->
                                            <div class="col-12">
                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="col-md-8">
                                                            <select onchange="document.claimstatus.submit()" name="claim_status" class="form-control select">
                                                             @if($claim->status == 'cancelled')
                                                             <option @if(($claim->status ?? null) == 'cancelled') selected @endif value="$claimst1">{{$claimst1}} 
                                                                 {{empty(\App\ClaimStatusLogs::where('claim_id',$claim->id)->where('status','cancelled')->first())?'': \App\ClaimStatusLogs::where('claim_id',$claim->id)->where('status','cancelled')->first()->created_at->format('(d-m-Y H:i:s)')}}
                                                            </option> 
                                                            @endif
                                                              <option @if(($claim->status ?? null) == 'draft') selected @endif value="draft">draft {{$claim->created_at->format('(d-m-Y H:i:s)')}}</option>                                                              
                                                              <option @if(($claim->status ?? null) == 'notified') selected @endif value="notified">notified {{empty(\App\ClaimStatusLogs::where('claim_id',$claim->id)->where('status','notified')->first())?'': \App\ClaimStatusLogs::where('claim_id',$claim->id)->where('status','notified')->latest()->first()->created_at->format('(d-m-Y H:i:s)')}}</option>
                                                                <option @if(($claim->status ?? null) == 'pending for os document') selected @endif value="pending for os document">
                                                                    pending for os document {{empty(\App\ClaimStatusLogs::where('claim_id',$claim->id)->where('status','pending for os document')->first())?'': \App\ClaimStatusLogs::where('claim_id',$claim->id)->where('status','pending for os document')->latest()->first()->created_at->format('(d-m-Y H:i:s)')}}</option>
                                                              
                                                                <option  @if(($claim->status ?? null) == 'pending for approval') selected @endif value="pending for approval">
                                                                    pending for approval {{empty(\App\ClaimStatusLogs::where('claim_id',$claim->id)->where('status','pending for approval')->first())?'': \App\ClaimStatusLogs::where('claim_id',$claim->id)->where('status','pending for approval')->latest()->first()->created_at->format('(d-m-Y H:i:s)')}}</option>
                                                       
                                                                <option  @if(($claim->status ?? null) == 'approved') selected @endif value="approved">
                                                                    approved {{empty(\App\ClaimStatusLogs::where('claim_id',$claim->id)->where('status','approved')->first())?'': \App\ClaimStatusLogs::where('claim_id',$claim->id)->where('status','approved')->latest()->first()->created_at->format('(d-m-Y H:i:s)')}}</option>
                                                          
                                                                <option  @if(($claim->status ?? null) == 'settled') selected @endif value="settled">
                                                                    settled {{empty(\App\ClaimStatusLogs::where('claim_id',$claim->id)->where('status','settled')->first())?'': \App\ClaimStatusLogs::where('claim_id',$claim->id)->where('status','settled')->latest()->first()->created_at->format('(d-m-Y H:i:s)')}}</option>
                                                     
                                                                <option  @if(($claim->status ?? null) == 'rejected') selected @endif value="rejected">
                                                                    rejected {{empty(\App\ClaimStatusLogs::where('claim_id',$claim->id)->where('status','rejected')->first())?'': \App\ClaimStatusLogs::where('claim_id',$claim->id)->where('status','rejected')->latest()->first()->created_at->format('(d-m-Y H:i:s)')}}</option>
                                                     
                                                                <option  @if(($claim->status ?? null) == 'closed') selected @endif value="closed">
                                                                    closed {{empty(\App\ClaimStatusLogs::where('claim_id',$claim->id)->where('status','closed')->first())?'': \App\ClaimStatusLogs::where('claim_id',$claim->id)->where('status','closed')->latest()->first()->created_at->format('(d-m-Y H:i:s)')}}</option>
                                                         
                                                                <option  @if(($claim->status ?? null) == 'ex-gratia') selected @endif  value="ex-gratia">
                                                                    ex-gratia {{empty(\App\ClaimStatusLogs::where('claim_id',$claim->id)->where('status','ex-gratia')->first())?'':\App\ClaimStatusLogs::where('claim_id',$claim->id)->where('status','ex-gratia')->latest()->first()->created_at->format('(d-m-Y H:i:s)')}}</option>


                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                        </div>
                                    </div>
                                    </form>


                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="card-header">
                            <h4 class="card-title">Documents</h4>
                        </div>
                        
                        <form id="uploader" enctype="multipart/form-data" action="{{route('userpanel.hospital.upload.doc',$claim->uuid)}}" method="post">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <td class="text-center">Item</td>
                                    <!-- <td class="text-center">Template</td> -->
                                    <td class="text-center">Processed Document</td>
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
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    

    <section id="description" class="card">
        <div class="card-header">
            <h4 class="card-title">Answers</h4>
            <a href="{{ route('admin.claim.export.answers', $claim->uuid) }}" class="btn btn-outline-success"><strong>EXPORT</strong></a>
        </div>
        <div class="card-content">
            <div class="card-body">
                <div class="card-text">
                    <div class="card">

                        <div class="card-content">
                            <div class="card-body">
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="col-12">
                                                <div class="form-group row">
                                                        <div class="col-md-2">
                                                            <span>#</span>
                                                        </div>
                                                        
                                                        <div class="col-md-4">
                                                            <span>Question</span>
                                                        </div>
                                                        
                                                        <div class="col-md-6">
                                                            <span>Answer</span>
                                                        </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        @foreach($ans as $key=>$answer)
                                            <div class="col-12">
                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        
                                                        <div class="col-md-2">
                                                            <span>{{($key+1)}}</span>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <span>{{$answer->title}}</span>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <span class="data-ProfileType">
                                                                {{$answer->value}}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach




                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </section>

    

        <!-- Modal -->
        <div wire:ignore.self class="modal fade" id="editModal" tabindex="-1" role="dialog"
        aria-labelledby="editModalLabel" aria-hidden="true">

        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Claim Status Changes</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="modal-body">
                    <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <td class="text-center">Item</td>
                                    <td class="text-center">Status</td>
                                    <td class="text-center">Cancellation Reason</td>
                                    <td class="text-center">Updated by</td>
                                    <td class="text-center">Date</td>
                                </tr>
                                </thead>
                                <tbody>
                    @foreach($statusChanges as $stChange)
                        <tr>
                            <td>
                                <div  class="d-flex justify-content-center align-items-center">
                                    {{$loop->index +1 }}
                                </div>
                            </td>
                            <td>
                                <div  class="d-flex justify-content-center align-items-center">
                                    {{$stChange['status']}}
                                </div>
                            </td>
                            <td>
                                <div  class="d-flex justify-content-center align-items-center">
                                    {{$stChange['reason']}}
                                </div>
                            </td>
                            <td>
                                <div  class="d-flex justify-content-center align-items-center">
                                    {{$stChange['updated_by']}}
                                </div>
                            </td>
                            <td>
                                <div  class="d-flex justify-content-center align-items-center">
                                    {{$stChange['created_at']}}
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">
                        Close
                    </button>
                </div>

            </div>
        </div>
    </div>

    
@endsection
@section('mystyle')

@endsection
@section('myscript')
<script>
    function changeStatus(value){
        console.log("vv",value);
        if(value == 'Rejected'){
            $("input[id=verification_details]").parents('.col-12').show();
        }else{
            $("input[id=verification_details]").parents('.col-12').hide();
        }
    }
    $("select[name=verification_status]").on("change",function (e) {

        let value = $(this).val();
        changeStatus(value);
    });
</script>

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

@section('myscript')

    <script type="text/javascript">
        window.addEventListener('editModalHide', event => {
            $('#editModal').modal('hide');
        });
    </script>
@endsection