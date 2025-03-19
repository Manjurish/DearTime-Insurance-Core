@extends('layouts.contentLayoutMaster')
@section('title','Verification')
@section('content')
    <section id="description" class="card">
        <div class="card-header">
            <h4 class="card-title"></h4>
        </div>
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <form class="form form-horizontal" method="POST" action="{{route('admin.Verification.submitVerify',$kyc->id)}}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-body">
                            <div class="row">
                                <div class="col-12">
                                    <div class="col-12">
                                        <div class="clearfix"></div>
                                        <hr>
                                        <h3 class="mb-3">KYC : ({{$data->profile->name ?? '-'}})</h3>
                                        <div class="clearfix"></div>

                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="col-12">
                                        <div class="form-group row">
                                            <div class="col-md-4">
                                                <span>Name   <span class="required">*</span> </span>
                                            </div>
                                            <div class="col-md-8">

                                                            <span class="data-profile__hasone__name">
                                                                {{$data->profile->name}} (<a href="{{route('admin.User.show',$data->uuid)}}">show</a>)
                                                            </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="col-12">
                                        <div class="form-group row">
                                            <div class="col-md-4">
                                                <span>Nationality   <span class="required">*</span> </span>
                                            </div>
                                            <div class="col-md-8">
                                                            <span class="data-profile__hasone__nationality">
                                                                @php
                                                                    if(empty($data->profile->nationality))
                                                                        echo 'Malaysian';
                                                                    else
                                                                        echo $data->profile->nationality;
                                                                @endphp
                                                            </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="col-12">
                                        <div class="form-group row">
                                            <div class="col-md-4">
                                                <span>NRIC   <span class="required">*</span> </span>
                                            </div>
                                            <div class="col-md-8">
                                                            <span class="data-profile__hasone__nric">
                                                                {{$data->profile->nric}}
                                                                <!-- //EKYC changes Print the extracted text -->
                                                                [Match Details : {{$data->profile->selfieMatch->image_id ?? ''}}]
                                                            </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="col-12">
                                        <div class="form-group row">
                                            <div class="col-md-4">
                                                <span>Date Of Birth   <span class="required">*</span> </span>
                                            </div>
                                            <div class="col-md-8">
                                                            <span class="data-profile__hasone__dob">
                                                                @php
                                                                    if(!empty($data->profile->dob))
                                                                        echo Carbon\Carbon::parse($data->profile->dob ?? '')->format("Y/m/d");
                                                                @endphp
                                                            </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="col-12">
                                        <div class="form-group row">
                                            <div class="col-md-4">
                                                <span>Gender   <span class="required">*</span> </span>
                                            </div>
                                            <div class="col-md-8">

                                                            <span class="data-profile__hasone__gender">
                                                                {{$data->profile->gender}}
                                                            </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="col-12">
                                        <div class="form-group row">
                                            <div class="col-md-4">
                                                <span>Mobile   <span class="required">*</span> </span>
                                            </div>
                                            <div class="col-md-8">

                                                            <span class="data-profile__hasone__mobile">
                                                                {{$data->profile->mobile}}
                                                            </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if($data->profile->nationality != 'Malaysian')
                                    <div class="col-12">
                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-4">
                                                    <span>Passport Expiry Date  </span>
                                                </div>
                                                <div class="col-md-8">

                                                            <span class="data-profile__hasone__passport_expiry_date">
                                                                @php

                                                                    if(!empty($data->profile->passport_expiry_date))
                                                                        echo Carbon\Carbon::parse($data->profile->passport_expiry_date ?? '')->format("Y/m/d");

                                                                @endphp
                                                            </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($data->profile->verification && $data->profile->verification->lastDetail->type =='auto'))
                                   
                                   <div class="col-12">
                                       <div class="col-12">
                                           <div class="form-group row">
                                               <div class="col-md-4">
                                                   <span>Selfie  </span>
                                               </div>
                                               <div class="col-md-8">
                                                               <span class="data-user_selfie">
                                                                   <a href="{{$data->profile->verification->lastDetail->documents()->where("type","selfie")->get()->first()->Link ?? ''}}"  data-lightbox="image-{{rand(1,99999)}}" data-title="image">
                                                                       <img style="width: 30%" src="{{$data->profile->verification->lastDetail->documents()->where("type","selfie")->get()->first()->Link ?? ''}}">
                                                                   </a>
                                                               </span>
                                               </div>
                                           </div>
                                       </div>
                                   </div>

                                   <div class="col-12">
                                       <div class="col-12">
                                           <div class="form-group row">
                                               <div class="col-md-4">
                                                   <span>MyKad/Passport  </span>
                                               </div>
                                               <div class="col-md-8">
                                                               <span class="data-user_mykad">
                                                                   <a href="{{$data->profile->verification->lastDetail->documents()->where("type","mykad")->get()->first()->Link ?? ''}}"  data-lightbox="image-{{rand(1,99999)}}" data-title="image">
                                                                        <img style="width: 30%" src="{{$data->profile->verification->lastDetail->documents()->where("type","mykad")->get()->first()->Link ?? ''}}"> 
                                                                   
                                                                   </a>
                                                               </span>
                                               </div>
                                           </div>
                                       </div>
                                   </div>
                                   <div class="col-12">
                                       <div class="col-12">
                                           <div class="form-group row">
                                               <div class="col-md-4">
                                                   <span>Visa </span>
                                               </div>
                                               <div class="col-md-8">
                                                           <span class="data-user_mykad">
                                                               <a href="{{$data->profile->verification->lastDetail->documents()->where("type","visa")->get()->first()->Link ?? ''}}"  data-lightbox="image-{{rand(1,99999)}}" data-title="image">
                                                                   <img style="width: 30%" src="{{$data->profile->verification->lastDetail->documents()->where("type","visa")->get()->first()->Link ?? ''}}">
                                                               </a>
                                                           </span>
                                               </div>
                                           </div>
                                       </div>
                                   </div>
                                   <div class="col-12">
                                       <div class="col-12">
                                           <div class="form-group row">
                                               <div class="col-md-4">
                                                   <span>Selfie Match Percent </span>
                                               </div>
                                               <div class="col-md-8">
                                                           <span class="data-user_mykad">
                                                               {{$data->profile->selfieMatch->percent ?? 0}} %
                                                           </span>
                                               </div>
                                           </div>
                                       </div>
                                   </div>
                                @elseif(!empty($data->profile->verification && $data->profile->verification->lastDetail->type !='auto'))
                                      @if($doc_exist==True)
                                            <div class="col-12">
                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="col-md-4">
                                                            <span>Selfie  </span>
                                                        </div>
                                                        <div class="col-md-8">
                                                                        <span class="data-user_selfie">
                                                                            <a href="{{$ver_doc->documents()->where("type","selfie")->first()->ThumbLink ?? ''}}"  data-lightbox="image-{{rand(1,99999)}}" data-title="image"> 
                                                                            <img style="width: 30%" src="{{$ver_doc->documents()->where("type","selfie")->first()->ThumbLink ?? ''}}">  
                                                                            </a>
                                                                        </span> 
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="col-md-4">
                                                            <span>MyKad/Passport  </span>
                                                        </div>
                                                        <div class="col-md-8">
                                                                        <span class="data-user_mykad">
                                                                            <a href="{{$ver_doc->documents()->where("type","mykad")->first()->ThumbLink ?? ''}}"  data-lightbox="image-{{rand(1,99999)}}" data-title="image"> 
                                                                                <img style="width: 30%" src="{{$ver_doc->documents()->where("type","mykad")->first()->ThumbLink ?? ''}}">  
                                                                                </a> 
                                                                        </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="col-md-4">
                                                            <span>Visa </span>
                                                        </div>
                                                        <div class="col-md-8">
                                                                    <span class="data-user_mykad">
                                                                        <a href="{{$ver_doc->documents()->where("type","visa")->first()->ThumbLink ?? ''}}"  data-lightbox="image-{{rand(1,99999)}}" data-title="image"> 
                                                                            <img style="width: 30%" src="{{$ver_doc->documents()->where("type","visa")->first()->ThumbLink ?? ''}}">  
                                                                            </a> 
                                                                    </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="col-12">
                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="col-md-4">
                                                            <span>Selfie  </span>
                                                        </div>
                                                        <div class="col-md-8">
                                                                        <span class="data-user_selfie">
                                                                            <a href="{{$data->profile->verification->lastUserDetail->documents()->where("type","selfie")->get()->first()->Link ?? ''}}"  data-lightbox="image-{{rand(1,99999)}}" data-title="image"> 
                                                                            <img style="width: 30%" src="{{$data->profile->verification->lastUserDetail->documents()->where("type","selfie")->get()->first()->Link ?? ''}}">  
                                                                            </a>
                                                                        </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="col-md-4">
                                                            <span>MyKad/Passport  </span>
                                                        </div>
                                                        <div class="col-md-8">
                                                                        <span class="data-user_mykad">
                                                                            <a href="{{$data->profile->verification->lastUserDetail->documents()->where("type","mykad")->get()->first()->Link ?? ''}}"  data-lightbox="image-{{rand(1,99999)}}" data-title="image">
                                                                                <img style="width: 30%" src="{{$data->profile->verification->lastUserDetail->documents()->where("type","mykad")->get()->first()->Link ?? ''}}"> 
                                                                            
                                                                            </a>
                                                                        </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="col-md-4">
                                                            <span>Visa </span>
                                                        </div>
                                                        <div class="col-md-8">
                                                                    <span class="data-user_mykad">
                                                                        <a href="{{$data->profile->verification->lastUserDetail->documents()->where("type","visa")->get()->first()->Link ?? ''}}"  data-lightbox="image-{{rand(1,99999)}}" data-title="image">
                                                                            <img style="width: 30%" src="{{$data->profile->verification->lastUserDetail->documents()->where("type","visa")->get()->first()->Link ?? ''}}">
                                                                        </a>
                                                                    </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                   
                                    <div class="col-12">
                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-4">
                                                    <span>Selfie Match Percent </span>
                                                </div>
                                                <div class="col-md-8">
                                                            <span class="data-user_mykad">
                                                                {{$data->profile->selfieMatch->percent ?? 0}} %
                                                            </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="col-12">
                                        <div class="col-12">
                                            <div class="form-group row">
                                                <div class="col-md-4">
                                                    <span>KYC Verification Details </span>
                                                </div>
                                                <div class="col-md-8">
                                                            <span class="data-user_mykad">
                                                                Incomplete
                                                            </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif



                                <div class="col-12">
                                    <div class="col-12">
                                        <div class="form-group row">
                                            <div class="col-md-4">
                                                <span>Manual Verification</span>
                                            </div>
                                            <div class="col-md-8">
                                                <select name="verification_status" class="form-control select2">
                                                    <option value="">Please select</option>
                                                    <option @if(($data->profile->verification->verification_status ?? null) == 'Accepted') selected @endif value="Accepted">Pass</option>
                                                    <option @if(($data->profile->verification->verification_status ?? null) == 'Rejected') selected @endif value="Rejected">Fail</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="col-12">
                                    <div class="col-12" style="display: none">
                                        <div class="form-group row">
                                            <div class="col-md-4">
                                                <span>Request to Re-do eKYC</span>
                                            </div>
                                            <div class="col-md-8">
                                                <select name="redo_request" class="form-control select2">
                                                    <option value="">Please select</option>
                                                    <option value="1">Yes</option>
                                                    <option value="2">No</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                             <div class="col-12">
                                    <div class="col-12" style="display: none">
                                        <div class="form-group row">
                                            <div class="col-md-4">
                                                <span> Reason for Failure (Notified to User)  </span>
                                            </div>
                                                <div class="col-md-8">
                                                    <option value="Multiple choice of reason as per below :">Multiple choice of reason as per below :</option>
                                                    <select id="verification_details" name="verification_details[]" class="form-control select2" multiple="multiple"  onchange="showfield(this.options[this.selectedIndex].value)">>
                                                      <option value="Selfie taken does not match with the photo in MyKad/Passport">Selfie taken does not match with the photo in MyKad/Passport</option>
                                                      <option value="The keyed-in MyKad/Passport number does not match with the number in MyKad/Passport">The keyed-in MyKad/Passport number does not match with the number in MyKad/Passport</option>
                                                      <option value="The captured photo of the MyKad/Passport is blurry">The captured photo of the MyKad/Passport is blurry.</option>
                                                      <option value="The selfie is blurry">The selfie is blurry</option>
                                                      <option value="Other">Other</option>
                                                    </select>
                                                      <br><br>
                                                    <div id="div1"> </div>               
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="col-12">
                                        <div class="form-group row">
                                            <div class="col-md-4">
                                                <span>Description (Internal Only)  </span>
                                            </div>
                                            <div class="col-md-8">
                                                <textarea class="form-control" name="description"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                @if($data1 == True)
                                <div class="col-12">
                                    <div class="col-12">
                                        <div class="form-group row">
                                            <div class="col-md-4">
                                                <span>FAR Classification</span>
                                            </div>
                                            <div class="col-md-8">
                                                <select name="classification" class="form-control select2">
                                                    <option value="">Please select</option>
                                                    <option value="True Positive">True Positive</option>
                                                    <option value="True Negative">True Negative</option>
                                                    <option value="False Positive">False Positive</option>
                                                    <option value="False Negative">False Negative</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="col-12">
                                    <div class="col-12">
                                        <div class="form-group row">
                                            <div class="col-md-4">
                                                <span>FAR Classification</span>
                                            </div>
                                            <div class="col-md-8">
                                                <select name="classification" class="form-control select2" disabled>
                                                    <option value="$ver_data">{{$ver_data}}</option> 
                                                    <option value="True Positive">True Positive</option>
                                                    <option value="True Negative">True Negative</option>
                                                    <option value="False Positive">False Positive</option>
                                                    <option value="False Negative">False Negative</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary waves-effect waves-light">
                                    Save
                                </button>
                                <button type="button" onClick="refreshPage()" class="btn btn-primary waves-effect waves-light">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <h2>History</h2>
                    <table class="table">
                        <thead>
                            <tr>
                                <td>id</td>
                                <td>Status</td>
                                <td>Description</td>
                                <td>FAR Classification</td>
                                <td>Reason for Failure (Notified to User)</td>
                                <td>Notes </td>
                                <td>Created By</td>
                                <td>Created At</td>
                                <td>Classification Created By</td>
                                <td>Classification Created At</td>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kyc->details()->orderBy("created_at","desc")->get() as $history)
                                <tr>
                                    <td>{{$history->id}}</td>
                                    <td>{{$history->status}}</td>
                                    <td>{{$history->description}}</td>
                                    <td>{{$history->classification}}</td>
                                    <td>{{$history->note}}</td>
                                    <td>{{$history->reason_for_ekyc}}</td>
                                    <td>{{$history->creator->name ?? '-'}}</td>
                                    <td>{{$history->created_at}}</td>
                                    <td>{{$history->classification_created_by ?? '-'}}</td>
                                    <td>{{$history->classification_created_at }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('mystyle')

@endsection
@section('myscript')
<script>
    function changeStatus(value){
        console.log("vv",value);
        if(value == 'Rejected'){
            $("[id=verification_details]").parents('.col-12').show();
            $("[name=redo_request]").parents('.col-12').show();
           
        }else{
            $("[id=verification_details]").parents('.col-12').hide();
            $("[name=redo_request]").parents('.col-12').hide();
           
        }
    }
    $("select[name=verification_status]").on("change",function (e) {

        let value = $(this).val();
        changeStatus(value);
    });
    
     function showfield(name){
      if(name=='Other')document.getElementById('div1').innerHTML='<input type="text" class="form-control" name=note>';
      else document.getElementById('div1').innerHTML='';
    }
    
    function refreshPage(){
    window.location.reload();
} 
</script>
@endsection