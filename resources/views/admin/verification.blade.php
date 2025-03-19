@extends('layouts.contentLayoutMaster')
@section('title','Profile Verification')
@section('content')
    <section id="description" class="card">
        <div class="card-header">
            <h4 class="card-title"></h4>
        </div>
        <div class="card-content">
            <div class="card-body">
                <div class="card-text">
                    <div class="card">

                        <div class="card-content">
                            <div class="card-body">

                                <form class="form form-horizontal" method="POST" action="{{route('admin.User.verification',$data->uuid)}}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="col-12">
                                                    <div class="clearfix"></div>
                                                    <hr>
                                                    <h3 class="mb-3">Profile Review ({{$data->profile->name ?? '-'}})</h3>
                                                    <div class="clearfix"></div>

                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="col-md-4">
                                                            <span>Profile Type  </span>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <span class="data-ProfileType">
                                                                {{$data->isIndividual() ? 'Individual' : 'Corporate'}}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="col-md-4">
                                                            <span>Profile Status  </span>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <span class="data-ProfileStatus">
                                                                @php
                                                                    if(empty($data->profile->verification->status))
                                                                        echo "Data Entry Pending";
                                                                    else echo $data->profile->verification->status;
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
                                                            <span>Charity Application  </span>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <span class="data-CharityApplication">
                                                                @php
                                                                    if (($data->profile->household_income ?? 0) > 3000)
                                                                        echo "Non-Eligible";
                                                                    else
                                                                        if (empty($data->profile->charity) || $data->profile->charity->active != '1') {
                                                                            echo "Not Active" . " (<a href='" . route('admin.CharityApplicant.details', [$data->uuid ?? 0]) . "'>Details</a>)";
                                                                        } else {
                                                                            echo "Active" . " (<a href='" . route('admin.CharityApplicant.details', [$data->uuid ?? 0]) . "'>Details</a>)";
                                                                        }
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
                                                            <span>Name   <span class="required">*</span> </span>
                                                        </div>
                                                        <div class="col-md-8">

                                                            <span class="data-profile__hasone__name">
                                                                {{$data->profile->name}}
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
                                                                [Match Details :: {{$data->profile->selfieMatch->image_id ?? $data->profile->selfieMatch->image_id}}]
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

                                            <div class="col-12">
                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="col-md-4">
                                                            <span>Personal Income   <span class="required">*</span> </span>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <span class="data-profile__hasone__personal_income">
                                                                RM {{$data->profile->personal_income}}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="col-md-4">
                                                            <span>Household Income   <span class="required">*</span> </span>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <span class="data-profile__hasone__household_income">
                                                                RM {{$data->profile->household_income}}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="col-md-4">
                                                            <span>Industry   <span class="required">*</span> </span>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <span class="data-industry">
                                                                {{$data->profile->occupationJob->industry->name ?? '-'}}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="col-md-4">
                                                            <span>Occupation   <span class="required">*</span> </span>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <span class="data-profile__hasone__occ">
                                                                {{$data->profile->occupationJob->name}}
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

                                            <div class="col-12">
                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="col-md-4">
                                                            <span>Address  </span>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <span class="data-user_address">
                                                                {{$data->profile->address->address ?? '-'}}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="col-md-4">
                                                            <span>State  </span>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <span class="data-state">
                                                                {{$data->profile->address->stateDetail->name ?? '-'}}

                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="col-md-4">
                                                            <span>City  </span>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <span class="data-city">
                                                                {{$data->profile->address->cityDetail->name ?? '-'}}

                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="col-md-4">
                                                            <span>ZipCode  </span>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <span class="data-zip_code">
                                                                {{$data->profile->address->postcodeDetail->name ?? '-'}}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            @if(!empty($data->profile->verification))
                                            <div class="col-12">
                                                <div class="col-12">
                                                    <div class="form-group row">
                                                        <div class="col-md-4">
                                                            <span>Selfie  </span>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <span class="data-user_selfie">
                                                                <a href="{{$data->profile->verification->documents()->where("type","selfie")->get()->first()->Link ?? ''}}"  data-lightbox="image-{{rand(1,99999)}}" data-title="image">
                                                                    <img style="width: 30%" src="{{$data->profile->verification->documents()->where("type","selfie")->get()->first()->Link ?? ''}}">
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
                                                                <a href="{{$data->profile->verification->documents()->where("type","mykad")->get()->first()->Link ?? ''}}"  data-lightbox="image-{{rand(1,99999)}}" data-title="image">
                                                                    <img style="width: 30%" src="{{$data->profile->verification->documents()->where("type","mykad")->get()->first()->Link ?? ''}}">
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
                                                            <span>Verification Status  </span>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <select name="verification_status" class="form-control select2">
                                                                <option value="">Please select</option>
                                                                <option @if(($data->profile->verification->status ?? null) == 'Pending') selected @endif value="Pending">Pending</option>
                                                                <option @if(($data->profile->verification->status ?? null) == 'Approved') selected @endif value="Approved">Approved</option>
                                                                <option @if(($data->profile->verification->status ?? null) == 'Rejected') selected @endif value="Rejected">Rejected</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12" style="display: none;">
                                                <div class="col-12" style="display: none;">
                                                    <div class="form-group row">
                                                        <div class="col-md-4">
                                                            <span>Verification Rejection Details  </span>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <input type="text" class="form-control " name="verification_details" placeholder="Verification Rejection Details" value="">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="form-group row mb-0">
                                        <div class="col-md-8 offset-md-4">
                                            <button type="submit" class="btn btn-primary waves-effect waves-light">
                                                Save
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>


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
@endsection
