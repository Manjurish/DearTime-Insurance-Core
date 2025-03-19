@extends('layouts.contentLayoutMaster')
@section('title','User Details')
@section('content')
    @include('panels.loading')

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
                                    <h4 class="card-title">User Details</h4>
                                </div>
                                <div class="card-content">
                                    <div class="card-body">
                                        <p>
                                            Full Name : {{$user->name}}
                                        </p>
                                        <p>
                                            Mobile : {{$user->profile->mobile ?? '-'}}
                                        </p>
                                        <p>
                                            Email : {{$user->email}}
                                        </p>
                                        <a href="{{ route('admin.user.change.basic.info', $user->uuid) }}"
                                           class="btn btn-outline-primary rounded">
                                            Change of Name, Nationality, NRIC, Passport, Address
                                        </a>
                                        @if($hasCoverage)
                                            <a href="{{ route('admin.user.change.payment.term', $user->uuid) }}"
                                               class="btn btn-outline-primary rounded">
                                                Change Payment Term
                                            </a>

                                            <a href="{{ route('admin.user.cancell.coverage', $user->uuid) }}"
                                               class="btn btn-outline-primary rounded">
                                                Cancel Coverage
                                            </a>
                                        @endif
                                        <br>
                                        <br>
                                        <ul class="nav nav-tabs nav-justified" id="myTab2" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" id="tab-1" data-toggle="tab" href="#tab-1-dt"
                                                   role="tab" aria-controls="tab-1-dt" aria-selected="true">Basic
                                                    Info</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="tab-2" data-toggle="tab" href="#tab-2-dt"
                                                   role="tab" aria-controls="tab-2-dt" aria-selected="true">Coverages
                                                    (Owner)</a>
                                            </li>
{{--                                            <li class="nav-item">--}}
{{--                                                <a class="nav-link" id="tab-3" data-toggle="tab" href="#tab-3-dt"--}}
{{--                                                   role="tab" aria-controls="tab-3-dt" aria-selected="true">Coverages--}}
{{--                                                    (Payer)</a>--}}
{{--                                            </li>--}}
{{--                                            <li class="nav-item">--}}
{{--                                                <a class="nav-link" id="tab-4" data-toggle="tab" href="#tab-4-dt"--}}
{{--                                                   role="tab" aria-controls="tab-4-dt"--}}
{{--                                                   aria-selected="true">Children's</a>--}}
{{--                                            </li>--}}
                                            <li class="nav-item">
                                                <a class="nav-link" id="tab-5" data-toggle="tab" href="#tab-5-dt"
                                                   role="tab" aria-controls="tab-5-dt"
                                                   aria-selected="true">Transactions</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="tab-11" data-toggle="tab" href="#tab-11-dt"
                                                   role="tab" aria-controls="tab-11-dt" aria-selected="true">Payment
                                                    Cards</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="tab-12" data-toggle="tab" href="#tab-12-dt"
                                                   role="tab" aria-controls="tab-12-dt" aria-selected="true">Bank Accounts</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="tab-6" data-toggle="tab" href="#tab-6-dt"
                                                   role="tab" aria-controls="tab-6-dt" aria-selected="true">Claims</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="tab-7" data-toggle="tab" href="#tab-7-dt"
                                                   role="tab" aria-controls="tab-7-dt" aria-selected="true">Underwritings</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="tab-8" data-toggle="tab" href="#tab-8-dt"
                                                   role="tab" aria-controls="tab-8-dt"
                                                   aria-selected="true">Thanksgiving</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="tab-9" data-toggle="tab" href="#tab-9-dt"
                                                   role="tab" aria-controls="tab-9-dt" aria-selected="true">Beneficiaries</a>
                                            </li>

                                            <li class="nav-item">
                                                <a class="nav-link" id="tab-10" data-toggle="tab" href="#tab-10-dt"
                                                   role="tab" aria-controls="tab-10-dt" aria-selected="true">Coverage
                                                    Moderation</a>
                                            </li>

                                        </ul>

                                        <!-- Tab panes -->
                                        <div class="tab-content pt-1">
                                            <div class="tab-pane active" id="tab-1-dt" role="tabpanel"
                                                 aria-labelledby="tab-1">
                                                <form class="form form-horizontal" method="POST"
                                                      action="{{route('admin.User.verification',$user->uuid)}}"
                                                      enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="form-body">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="col-12">
                                                                    <div class="clearfix"></div>
                                                                    <hr>
                                                                    <h3 class="mb-3">Profile Review
                                                                        ({{$user->profile->name ?? '-'}})</h3>
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
                                                                {{$user->isIndividual() ? 'Individual' : 'Corporate'}}
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
                                                                    if(empty($user->profile->verification->status))
                                                                        echo "Data Entry Pending";
                                                                    else echo $user->profile->verification->status;
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
                                                                    if (($user->profile->household_income ?? 0) > 3000)
                                                                        echo "Non-Eligible";
                                                                    else
                                                                        if (empty($user->profile->charity) || $user->profile->charity->active != '1') {
                                                                            echo "Not Active" . " (<a href='" . route('admin.CharityApplicant.details', [$user->uuid ?? 0]) . "'>Details</a>)";
                                                                        } else {
                                                                            echo "Active" . " (<a href='" . route('admin.CharityApplicant.details', [$user->uuid ?? 0]) . "'>Details</a>)";
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
                                                                {{$user->profile->name ?? ''}}
                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-12">
                                                                <div class="col-12">
                                                                    <div class="form-group row">
                                                                        <div class="col-md-4">
                                                                            <span>Nationality   <span
                                                                                    class="required">*</span> </span>
                                                                        </div>
                                                                        <div class="col-md-8">
                                                            <span class="data-profile__hasone__nationality">
                                                                @php
                                                                    if(empty($user->profile->nationality))
                                                                        echo 'Malaysian';
                                                                    else
                                                                        echo $user->profile->nationality;
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
                                                                            <span>Religion   <span
                                                                                    class="required">*</span> </span>
                                                                        </div>
                                                                        <div class="col-md-8">
                                                                            <span
                                                                                class="data-profile__hasone__religion">
                                                                                {{$user->profile->religion ?? ''}}
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
                                                                {{$user->profile->nric ?? ''}}
                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-12">
                                                                <div class="col-12">
                                                                    <div class="form-group row">
                                                                        <div class="col-md-4">
                                                                            <span>Date Of Birth   <span
                                                                                    class="required">*</span> </span>
                                                                        </div>
                                                                        <div class="col-md-8">
                                                            <span class="data-profile__hasone__dob">
                                                                @php
                                                                    if(!empty($user->profile->dob))
                                                                        echo Carbon\Carbon::parse($user->profile->dob ?? '')->format("d/m/Y");
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
                                                                            <span>Gender   <span
                                                                                    class="required">*</span> </span>
                                                                        </div>
                                                                        <div class="col-md-8">

                                                            <span class="data-profile__hasone__gender">
                                                                {{$user->profile->gender ?? ''}}
                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-12">
                                                                <div class="col-12">
                                                                    <div class="form-group row">
                                                                        <div class="col-md-4">
                                                                            <span>Mobile   <span
                                                                                    class="required">*</span> </span>
                                                                        </div>
                                                                        <div class="col-md-8">

                                                            <span class="data-profile__hasone__mobile">
                                                                {{$user->profile->mobile ?? ''}}
                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-12">
                                                                <div class="col-12">
                                                                    <div class="form-group row">
                                                                        <div class="col-md-4">
                                                                            <span>Personal Income   <span
                                                                                    class="required">*</span> </span>
                                                                        </div>
                                                                        <div class="col-md-8">
                                                            <span class="data-profile__hasone__personal_income">
                                                                RM {{$user->profile->personal_income ?? ''}}
                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-12">
                                                                <div class="col-12">
                                                                    <div class="form-group row">
                                                                        <div class="col-md-4">
                                                                            <span>Household Income   <span
                                                                                    class="required">*</span> </span>
                                                                        </div>
                                                                        <div class="col-md-8">
                                                            <span class="data-profile__hasone__household_income">
                                                                RM {{$user->profile->household_income ?? ''}}
                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-12">
                                                                <div class="col-12">
                                                                    <div class="form-group row">
                                                                        <div class="col-md-4">
                                                                            <span>Industry   <span
                                                                                    class="required">*</span> </span>
                                                                        </div>
                                                                        <div class="col-md-8">
                                                            <span class="data-industry">
                                                                {{$user->profile->occupationJob->industry->name ?? '-'}}
                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-12">
                                                                <div class="col-12">
                                                                    <div class="form-group row">
                                                                        <div class="col-md-4">
                                                                            <span>Occupation   <span
                                                                                    class="required">*</span> </span>
                                                                        </div>
                                                                        <div class="col-md-8">
                                                            <span class="data-profile__hasone__occ">
                                                                {{$user->profile->occupationJob->name ?? '-'}}
                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            @if($user->profile->nationality != 'Malaysian')
                                                                <div class="col-12">
                                                                    <div class="col-12">
                                                                        <div class="form-group row">
                                                                            <div class="col-md-4">
                                                                                <span>Passport Expiry Date  </span>
                                                                            </div>
                                                                            <div class="col-md-8">

                                                            <span class="data-profile__hasone__passport_expiry_date">
                                                                @php

                                                                    if(!empty($user->profile->passport_expiry_date))
                                                                        echo Carbon\Carbon::parse($user->profile->passport_expiry_date ?? '')->format("d/m/Y H:i A");

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
                                                                            <span>Address 1 </span>
                                                                        </div>
                                                                        <div class="col-md-8">
                                                            <span class="data-user_address">
                                                                {{$user->profile->address->address1 ?? '-'}}
                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-12">
                                                                <div class="col-12">
                                                                    <div class="form-group row">
                                                                        <div class="col-md-4">
                                                                            <span>Address 2 </span>
                                                                        </div>
                                                                        <div class="col-md-8">
                                                            <span class="data-user_address">
                                                                {{$user->profile->address->address2 ?? '-'}}
                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-12">
                                                                <div class="col-12">
                                                                    <div class="form-group row">
                                                                        <div class="col-md-4">
                                                                            <span>Address 3 </span>
                                                                        </div>
                                                                        <div class="col-md-8">
                                                            <span class="data-user_address">
                                                                {{$user->profile->address->address3 ?? '-'}}
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
                                                                {{$user->profile->address->stateDetail->name ?? '-'}}

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
                                                                {{$user->profile->address->cityDetail->name ?? '-'}}

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
                                                                                {{$user->profile->address->postcodeDetail->name ?? '-'}}
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-12">
                                                                <div class="col-12">
                                                                    <div class="form-group row">
                                                                        <div class="col-md-4">
                                                                            <span>Marketing Email</span>
                                                                        </div>
                                                                        <div class="col-md-8">
                                                                            <span class="data-zip_code">
                                                                                {{ $user->marketing_email ? 'Enable' : 'Disable' }}
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-12">
                                                                <div class="col-12">
                                                                    <div class="form-group row">
                                                                        <div class="col-md-4">
                                                                            <span>Audit Trail  </span>
                                                                        </div>
                                                                        <div class="col-md-8">
                                                                            <a class="btn btn-primary round waves-effect waves-light"
                                                                               href="{{ route('admin.User.audit',$user->uuid) }}">
                                                                                <span class="data-zip_code">
                                                                                    Show All
                                                                                </span>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-12">
                                                                <div class="col-12">
                                                                    <div class="form-group row">
                                                                        <div class="col-md-4">
                                                                            <span>Screening </span>
                                                                        </div>
                                                                        <div class="col-md-8">
                                                                            <a class="btn btn-primary round waves-effect waves-light"
                                                                               href="{{ route('admin.SanctionScreen.index',['user'=>$user->id]) }}">
                                                                                <span class="data-zip_code">
                                                                                    Show All
                                                                                </span>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-12">
                                                                <div class="col-12">
                                                                    <div class="form-group row">
                                                                        <div class="col-md-4">
                                                                            <span>Credits </span>
                                                                        </div>
                                                                        <div class="col-md-8">
                                                                            <a class="btn btn-primary round waves-effect waves-light"
                                                                               href="{{ route('admin.user.credit.show',['uuid'=>$user->uuid]) }}">
                                                                                <span class="data-zip_code">
                                                                                    Show All
                                                                                </span>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-12">
                                                                <div class="col-12">
                                                                    <div class="form-group row">
                                                                        <div class="col-md-4">
                                                                            <span>Promote Credits </span>
                                                                        </div>
                                                                        <div class="col-md-8">
                                                                            <a class="btn btn-primary round waves-effect waves-light"
                                                                               href="{{ route('admin.user.credit.show',['uuid'=>$user->uuid,'promote'=>true]) }}">
                                                                                <span class="data-zip_code">
                                                                                    Show All
                                                                                </span>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>


                                                        </div>
                                                    </div>

                                                </form>

                                            </div>
                                            <div class="tab-pane" id="tab-2-dt" role="tabpanel" aria-labelledby="tab-2">
                                                <p>Coverages List (Owner)</p>
                                                <table id="table" class="display table table-data-width">
                                                    <thead>
                                                    <tr>
                                                        <th>i</th>
                                                        <th>RefNo</th>
                                                        <th>Parent RefNo</th>
                                                        <th>Product</th>
                                                        <th>Owner</th>
                                                        <th>Payer</th>
                                                        <th>Coverage Status</th>
                                                        <th>UW</th>
                                                        <th>UW Decision</th>
                                                        <th>Payment Term</th>
                                                        <th>Changed Payment Term</th>
                                                        <th>Coverage</th>
                                                        <th>Premium</th>
                                                        <th>Sub-std Premium</th>
                                                        <th>Full Premium</th>
                                                        <th>{{ __('web/messages.created_at') }}</th>
                                                        <th>{{ __('web/messages.payment_at') }}</th>
                                                        <th>Details</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    
                                                    @foreach($coveragesOwner ?? [] as $coverage)
                                                        <tr>
                                                           
                                                            <td>{{$loop->index + 1}}</td>
                                                            <td>{{$coverage->ref_no}}</td>
                                                            <td>{{$parentRef[$coverage->parent_id] ?? " "}}</td>
                                                            <td>{{$coverage->product->name ?? '-'}}</td>
                                                            <td>{{$coverage->owner->name ?? '-'}}</td>
                                                            <td>{{($coverage->sponsored)?'DearTime Charity Fund':$coverage->payer->name ?? '-'}}</td>
                                                            <td>{{$coverage->status}}</td>
                                                            <td>
                                                                @if($coverage->uw_id != '')
                                                                    @if(strlen($coverage->uw_id) =='3')
                                                                        UW000{{$coverage->uw_id}}
                                                                    @elseif(strlen($coverage->uw_id) =='2')
                                                                        UW0000{{$coverage->uw_id}}
                                                                    @elseif(strlen($coverage->uw_id) =='1')
                                                                        UW00000{{$coverage->uw_id}}
                                                                    @elseif(strlen($coverage->uw_id) =='4')
                                                                        UW00000{{$coverage->uw_id}}
                                                                    @endif
                                                                @else
                                                                    {{$coverage->uw_id}} 
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @php 
                                                                $underwriting=\App\Underwriting::where('id',$coverage->uw_id)->first()??null;
                                                                if($underwriting){
                                                                $answers = $underwriting->answers;
                                                                  if(($underwriting->death =='1')||($underwriting->disability =='1')||($underwriting->ci =='1')||($underwriting->medical =='1'))
                                                                    {
                                                                        $underwriting->status = 'Accept';
                                                                    }
                                                                  else  
                                                                     { 
                                                                        $underwriting->status = 'Reject';
                                                                    }
                                                             
                                                                $uwloading =\App\UwsLoading::whereIn('uws_id',$answers['answers'])->where('product_id',$coverage->product_id)->get();
                                                                if($uwloading->isNotEmpty() && $underwriting->status == 'Accept' ){
                                                                    echo "Sub-std";
                                                                }else{
                                                                    if($underwriting->status == 'Accept'){
                                                                        echo "Std";
                                                                    }
                                                                }
                                                            }
                                                                @endphp
                                                                
                                                            </td>  
                                                                    
                                                            <td>{{$coverage->payment_term}}</td>
                                                            <td>{{$coverage->payment_term_new}}</td>
                                                            <td>RM{{number_format($coverage->coverage,2)}}</td>
                                                            <td>{{$coverage->payment_term == 'monthly' ? 'RM'.number_format($coverage->payment_monthly,2) : 'RM'.number_format($coverage->payment_annually,2)}}</td>
                                                            <td>{{empty($coverage->payment_without_loading)? "RM0.00":($coverage->payment_term == 'monthly' ? 'RM'.number_format($coverage->payment_monthly-$coverage->payment_without_loading,2) : 'RM'.number_format($coverage->payment_annually-$coverage->payment_without_loading,2))}}</td>
                                                            <td>RM{{number_format($coverage->full_premium,2)}}</td>   
                                                            <td>{{ \Carbon\Carbon::parse($coverage->created_at)->format(config('static.datetime_format')) }}</td>
                                                            <td>
                                                                @if($coverage->last_payment_on)
                                                                    {{ \Carbon\Carbon::parse($coverage->last_payment_on)->format(config('static.datetime_format')) }}
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td><a data-toggle="tooltip" class="" data-placement="top"
                                                                   title="" style="margin-left: 10px"
                                                                   href="{{route('admin.Coverage.show',$coverage->uuid)}}"
                                                                   data-original-title="View"><i
                                                                        class="feather icon-eye"></i></a></td>
                                                        </tr>
                                                    @endforeach

                                                    </tbody>
                                                </table>
                                            </div>
{{--                                            <div class="tab-pane" id="tab-3-dt" role="tabpanel" aria-labelledby="tab-3">--}}
{{--                                                <p>Coverages List (Payer)</p>--}}
{{--                                                <table id="table" class="display table table-data-width">--}}
{{--                                                    <thead>--}}
{{--                                                    <tr>--}}
{{--                                                        <th>i</th>--}}
{{--                                                        <th>RefNo</th>--}}
{{--                                                        <th>Product</th>--}}
{{--                                                        <th>Owner</th>--}}
{{--                                                        <th>Status</th>--}}
{{--                                                        <th>Payment Term</th>--}}
{{--                                                        <th>Coverage</th>--}}
{{--                                                        <th>Premium</th>--}}
{{--                                                        <th>{{ __('web/messages.created_at') }}</th>--}}
{{--                                                        <th>{{ __('web/messages.payment_at') }}</th>--}}
{{--                                                        <th>Details</th>--}}
{{--                                                    </tr>--}}
{{--                                                    </thead>--}}
{{--                                                    <tbody>--}}
{{--                                                    @foreach($user->profile->coverages_payer ?? [] as $coverage)--}}
{{--                                                        <tr>--}}
{{--                                                            <td>{{$loop->index + 1}}</td>--}}
{{--                                                            <td>{{$coverage->ref_no}}</td>--}}
{{--                                                            <td>{{$coverage->product->name ?? '-'}}</td>--}}
{{--                                                            <td>{{$coverage->owner->name ?? '-'}}</td>--}}
{{--                                                            <td>{{$coverage->status}}</td>--}}
{{--                                                            <td>{{$coverage->payment_term}}</td>--}}
{{--                                                            <td>RM{{number_format($coverage->coverage,2)}}</td>--}}
{{--                                                            <td>{{$coverage->payment_term == 'monthly' ? 'RM'.number_format($coverage->payment_monthly,2) : 'RM'.number_format($coverage->payment_annually,2)}}</td>--}}
{{--                                                            <td>{{ \Carbon\Carbon::parse($coverage->created_at)->format(config('static.datetime_format')) }}</td>--}}
{{--                                                            <td>--}}
{{--                                                                @if($coverage->last_payment_on)--}}
{{--                                                                    {{ \Carbon\Carbon::parse($coverage->last_payment_on)->format(config('static.datetime_format')) }}--}}
{{--                                                                @else--}}
{{--                                                                    ---}}
{{--                                                                @endif--}}
{{--                                                            </td>--}}
{{--                                                            <td><a data-toggle="tooltip" class="" data-placement="top"--}}
{{--                                                                   title="" style="margin-left: 10px"--}}
{{--                                                                   href="{{route('admin.Coverage.show',$coverage->uuid)}}"--}}
{{--                                                                   data-original-title="View"><i--}}
{{--                                                                        class="feather icon-eye"></i></a></td>--}}
{{--                                                        </tr>--}}
{{--                                                    @endforeach--}}

{{--                                                    </tbody>--}}
{{--                                                </table>--}}
{{--                                            </div>--}}
{{--                                            <div class="tab-pane" id="tab-4-dt" role="tabpanel" aria-labelledby="tab-4">--}}
{{--                                                <p>Children's</p>--}}
{{--                                                <table id="table" class="display table table-data-width">--}}
{{--                                                    <thead>--}}
{{--                                                    <tr>--}}
{{--                                                        <th>i</th>--}}
{{--                                                        <th>RefNo</th>--}}
{{--                                                        <th>Name</th>--}}
{{--                                                        <th>Email</th>--}}
{{--                                                        <th>Mobile</th>--}}
{{--                                                        <th>Locale</th>--}}
{{--                                                        <th>profileDone</th>--}}
{{--                                                        <th>profileVerification</th>--}}
{{--                                                        <th>Selfie Match</th>--}}
{{--                                                        <th>Created</th>--}}
{{--                                                    </tr>--}}
{{--                                                    </thead>--}}
{{--                                                    <tbody>--}}
{{--                                                    @foreach($user->profile->childs ?? [] as $child)--}}
{{--                                                        <tr>--}}
{{--                                                            <td>{{$loop->index + 1}}</td>--}}
{{--                                                            <td>{{$child->ref_no}}</td>--}}
{{--                                                            <td>{{$child->name}}</td>--}}
{{--                                                            <td>{{$child->email}}</td>--}}
{{--                                                            <td>{{$child->profile->mobile ?? '-'}}</td>--}}
{{--                                                            <td>{{$child->locale}}</td>--}}
{{--                                                            <td>{{$child->ProfileDoneText}}</td>--}}
{{--                                                            <td>{{$child->profileVerificationText}}</td>--}}
{{--                                                            <td>{{$child->profile->selfieMatch->percent ?? 0}}</td>--}}
{{--                                                            <td>{{ \Carbon\Carbon::parse($child->created_at)->format('d/m/Y H:i A') }}</td>--}}
{{--                                                        </tr>--}}
{{--                                                    @endforeach--}}

{{--                                                    </tbody>--}}
{{--                                                </table>--}}
{{--                                            </div>--}}
                                            <div class="tab-pane" id="tab-5-dt" role="tabpanel" aria-labelledby="tab-5">
                                                <p>Transactions</p>
                                                <table id="table" class="display table table-data-width">
                                                    <thead>
                                                    <tr>
                                                        <th>i</th>
                                                        <th>Transaction Reference</th>
                                                        <th>Transaction ID</th>
                                                        <th>Payer Name</th>
                                                        <th>Amount</th>
                                                        <th>Card Type</th>
                                                        <th>Card No.</th>
                                                        <th>Order Id</th>
                                                        <th>Date</th>
                                                        <th>Status</th>
                                                        <th>Created</th>

                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($user->paymentsHistory() ?? [] as $peyment)
                                                        <tr>
                                                            <td>{{$loop->index + 1}}</td>
                                                            <td>{{$peyment->transaction_ref}}</td>
                                                            <td>{{$peyment->transaction_id}}</td>
                                                            <td>{{$peyment->order->payer->profile->name}}</td>
                                                            <td>RM {{$peyment->amount}}</td>
                                                            <td>{{$peyment->card_type}}</td>
                                                            <td>{{$peyment->card_no}}</td>
                                                            <td>
                                                                <a href="{{ url('/internal/CoverageOrder?order='.$peyment->order_id) }}">
                                                                    {{ $peyment->order->ref_no }}
                                                                </a>
                                                            </td>
                                                            <td>
                                                                {{ \Carbon\Carbon::parse($peyment->date)->format(config('static.datetime_format')) }}
                                                            </td>
                                                            <td>
                                                                {{ $peyment->success ? 'Successful':'Unsuccessful' }}
                                                            </td>
                                                            <td>
                                                                {{ \Carbon\Carbon::parse($peyment->created_at)->format(config('static.datetime_format')) }}
                                                            </td>

                                                        </tr>
                                                    @endforeach

                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="tab-pane" id="tab-11-dt" role="tabpanel"
                                                 aria-labelledby="tab-11">
                                                <p>Payment Cards</p>
                                                <table id="table" class="display table table-data-width">
                                                    <thead>
                                                    <tr>
                                                        <th>i</th>
                                                        <th>Scheme</th>
                                                        <th>Masked Pan</th>
                                                        <th>Holders Name</th>
                                                        <th>Expiry Month</th>
                                                        <th>Expiry Year</th>
                                                        <th>Saved At</th>
                                                        <th>Deleted At</th>
                                                        <th>Status</th>

                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($user->profile->bankCards()->withTrashed()->latest()->get() ?? [] as $card)
                                                        <tr>
                                                            <td>{{$loop->index + 1}}</td>
                                                            <td>{{$card->scheme ?? 'N/A'}}</td>
                                                            <td>{{$card->masked_pan}}</td>
                                                            <td>{{$card->holder_name}}</td>
                                                            <td>{{$card->expiry_month}}</td>
                                                            <td>{{$card->expiry_year}}</td>
                                                            <td>{{\Carbon\Carbon::parse($card->created_at)->format(config('static.datetime_format'))}}</td>
                                                            <td>{{$card->deleted_at ? $card->deleted_at->format(config('static.datetime_format')) : 'N/A'}}</td>
                                                            <td>{{$card->deleted_at ? 'Deleted' : 'Added'}}</td>

                                                        </tr>
                                                    @endforeach

                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="tab-pane" id="tab-12-dt" role="tabpanel"
                                                 aria-labelledby="tab-11">
                                                <p>Bank Accounts</p>
                                                <table id="table" class="display table table-data-width">
                                                    <thead>
                                                    <tr>
                                                        <th>i</th>
                                                        <th>Account No</th>
                                                        <th>Bank Name</th>
                                                        <th>Saved At</th>
                                                        <th>Deleted At</th>
                                                        <th>Status</th>

                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($user->profile->bankAccounts()->withTrashed()->latest()->get() ?? [] as $bank)
                                                        <tr>
                                                            <td>{{$loop->index + 1}}</td>
                                                            <td>{{$bank->account_no}}</td>
                                                            <td>{{$bank->bank_name}}</td>
                                                            <td>{{$bank->created_at->format('d/m/Y H:i A')}}</td>
                                                            <td>{{$bank->deleted_at ? $bank->deleted_at->format(config('static.datetime_format')) : 'N/A'}}</td>
                                                            <td>{{$bank->deleted_at ? 'Deleted' : 'Added'}}</td>

                                                        </tr>
                                                    @endforeach

                                                    </tbody>
                                                </table>
                                            </div>

                                            <div class="tab-pane" id="tab-6-dt" role="tabpanel" aria-labelledby="tab-6">
                                                <p>Claims</p>
                                                <table id="table" class="display table table-data-width">
                                                    <thead>
                                                    <tr>
                                                        <th>i</th>
                                                        <th>RefNo</th>
                                                        <th>Claimant Name</th>
                                                        <th>Owner Name</th>
                                                        <th>Policy</th>
                                                        <th>Status</th>
                                                        <th>Date</th>

                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                       
                                                        @php
                                                        $check_coverage=$user->profile->claims()->orWhere('owner_id', '=', $user->profile->id)->orderBy('created_at','desc')->get();
                                                        @endphp
                                                        
                                                        @if($check_coverage->count() > 0)
                                                        
                                                            @foreach($check_coverage as $claim)
                                                        {{-- @foreach($user->profile->claims()->orderBy('created_at','desc')->get() ?? [] as $claim)  --}}
                                                             <tr>
                                                                
                                                                 <td>{{$loop->index + 1}}</td>
                                                                 <td>{{$claim->ref_no}}</td>
                                                                 <td>{{$claim->ClaimantName}}</td>
                                                                 
                                                                 <td>{{$claim->OwnerName}}</td>
                                                                 <td>{{$claim->PolicyName}}</td>
                                                                 <td>{{$claim->status}}</td>
                                                                 <td>{{ \Carbon\Carbon::parse($claim->created_at)->format(config('static.datetime_format')) }}</td>
     
                                                             </tr>
                                                         @endforeach
                                                                                                              
                                                        @endif
                                                 

                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="tab-pane" id="tab-7-dt" role="tabpanel" aria-labelledby="tab-7">
                                                <p>Underwritings</p>
                                                <table id="table" class="display table table-data-width">
                                                    <thead>
                                                    <tr>
                                                        <th>i</th>
                                                        <th>RefNo</th>
                                                        <th>CreatedBy</th>
                                                        <th>Answers</th>
                                                        <th>Allowed Products</th>
                                                        <th>Linked Coverages</th>
                                                        <th>Underwriting Status</th>
                                                        <th>UW Decision</th>
                                                        <th>Date</th>

                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($user->profile->medicalSurvey()->orderBy('created_at','desc')->get() ?? [] as $underwriting)
                                                        <tr>
                                                            <td>{{$loop->index + 1}}</td>
                                                            <td>{{$underwriting->ref_no}}</td>
                                                            <td>{{$underwriting->creator->profile->name}}</td>
                                                            <td>
                                                                @php
                                                                    $answers = $underwriting->answers;

                                                                    echo "Smoke :".($answers['smoke'] ?? '-')."<br>";
                                                                    echo "Height :".($answers['height'] ?? '-')."<br>";
                                                                    echo "Weight :".($answers['weight'] ?? '-')."<br>";
                                                                @endphp
                                                            </td>
                                                            <td>
                                                                @php
                                                                    $out = '';
                                                                    if($underwriting->death == '1')
                                                                        $out .='Death<br>';
                                                                    if($underwriting->disability == '1')
                                                                        $out .='Disability<br>';
                                                                    if($underwriting->ci == '1')
                                                                        $out .='Critical Illness<br>';
                                                                    if($underwriting->medical == '1')
                                                                        $out .='Medical<br>';

                                                                    echo $out;
                                                                @endphp
                                                            </td>
                                                            <td>
                                                                @foreach(\App\Coverage::where("uw_id",$underwriting->id)->where('state','active')->get() as $cov)
                                                                    <p>
                                                                        <a href="{{route('admin.Coverage.show',$cov->uuid)}}">{{$cov->product->name}}
                                                                            ({{$cov->RealCoverage}})</a></p>
                                                                @endforeach
                                                            </td>
                                                            <td>
                                                                @if (($underwriting->death =='1')||($underwriting->disability =='1')||($underwriting->ci =='1')||($underwriting->medical =='1'))
                                                                     {{ $underwriting->status = 'Accept'}}
                                                                @else  
                                                                     {{ $underwriting->status = 'Reject'}} 
                                                                @endif
                                                            </td> 
                                                            <td>
                                                                @php 
                                                                $answers = $underwriting->answers;
                                                                $uwloading =\App\UwsLoading::whereIn('uws_id',$answers['answers'])->get();
                                                                if($uwloading->isNotEmpty() && $underwriting->status == 'Accept' ){
                                                                    echo "Sub-std";
                                                                }
                                                                @endphp
                                                            </td>  

                                                            <td>{{ \Carbon\Carbon::parse($underwriting->created_at)->format(config('static.datetime_format')) }}</td>
                                                        </tr>
                                                    @endforeach

                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="tab-pane" id="tab-8-dt" role="tabpanel" aria-labelledby="tab-8">
                                                <p>Thanksgiving</p>
                                                <table id="table" class="display table table-data-width">
                                                    <thead>
                                                    <tr>
                                                        <th>i</th>
                                                        <th>Type</th>
                                                        <th>Percent</th>
                                                        <th>Created At</th>
                                                        <th>Deleted At</th>

                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach(!empty($user->profile->thanksgiving)? $user->profile->thanksgiving()->withTrashed()->get(): [] as $thanksgiving)
                                                        <tr>
                                                            <td>{{$loop->index + 1}}</td>
                                                            <td>{{$thanksgiving->type}}</td>
                                                            <td>{{$thanksgiving->percentage}}</td>
                                                            <td>{{ \Carbon\Carbon::parse($thanksgiving->created_at)->format(config('static.datetime_format')) }}</td>
                                                            <td>{{ $thanksgiving->deleted_at ? \Carbon\Carbon::parse($thanksgiving->deleted_at)->format(config('static.datetime_format')) : 'N/A'}}</td>
                                                        </tr>
                                                    @endforeach

                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="tab-pane" id="tab-9-dt" role="tabpanel" aria-labelledby="tab-9">
                                                <p>Beneficiaries</p>
                                                <table id="table" class="display table table-data-width">
                                                    <thead>
                                                    <tr>
                                                        <th>i</th>
                                                        <th>Name</th>
                                                        <th>relationship</th>
                                                        <th>percentage</th>
                                                        <th>status</th>
                                                        <th>Deleted At</th>
                                                        <th>Created At</th>
                                                        <th>Details</th>

                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach(!empty($user->profile->nominees)? $user->profile->nominees()->orderBy('id','desc')->withTrashed()->get():[] as $nominee)
                                                        <tr>
                                                            <td>{{$loop->index + 1}}</td>
                                                            <td>{{$nominee->name}}</td>
                                                            <td>{{$nominee->relationship}} @if($nominee->is_trust) <span class="label text-success"><b>(Trust)</b></span> @endif</td>
                                                            <td>{{$nominee->percentage}}%</td>
                                                            <td>{{str_replace('-',' ',$nominee->status)}}</td>
                                                            <td>@if(!empty($nominee->deleted_at)){{ \Carbon\Carbon::parse($nominee->deleted_at)->format(config('static.datetime_format')) }}@endif</td>
                                                            <td> 
                                                                 @if($nominee->type == 'hibah') {{ \Carbon\Carbon::parse($nominee->updated_at)->format(config('static.datetime_format')) }}
                                                                 @else{{ \Carbon\Carbon::parse($nominee->created_at)->format(config('static.datetime_format')) }}
                                                                 @endif</td>
                                                            <td><a data-toggle="modal"
                                                                   data-target="#nominee-{{ $nominee->id }}"><i
                                                                        class="feather icon-file"></i></a>
                                                                <a data-toggle="tooltip" class="" data-placement="top"
                                                                        title="" style="margin-left: 10px"
                                                                         href="{{route('admin.Beneficiary.show',$nominee->id)}}" 
                                                                        data-original-title="View"><i
                                                                             class="feather icon-eye"></i></a>      
                                                                        </td>
                                                        </tr>
                                                        @include('admin.user.beneficiary-modal')
                                                    @endforeach

                                                    </tbody>
                                                </table>
                                            </div>

                                            <div class="tab-pane" id="tab-10-dt" role="tabpanel"
                                                 aria-labelledby="tab-10">
                                                <p>Coverage Moderation</p>
                                                <table id="table" class="display table table-data-width">
                                                    <tr>
                                                        <th>Product Name</th>
                                                        <th>Active Coverage</th>
                                                        <th>Max Coverage</th>
                                                        <th>Current State</th>
                                                        <th>Actions</th>
                                                        <th>Logs</th>
                                                    </tr>

                                                    @foreach($data as $item)
                                                        <tr>
                                                            <th>{{ $item['product-name'] }}</th>
                                                            <td>
                                                            @if(is_int($item['active-coverage']))
                                                                {{ number_format($item['active-coverage'],2) }}
                                                            @else
                                                                {{ $item['active-coverage'] }}
                                                            @endif
                                                            <td>
                                                                @if(is_int($item['max-coverage']))
                                                                    {{ number_format($item['max-coverage'],2) }}
                                                                @else
                                                                    {{ $item['max-coverage'] }}
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <h4>
                                                                    @if(str_contains($item['current-state'], 'disallow'))
                                                                        <span id="state-{{ $item['product-id'] }}"
                                                                              class="badge rounded-pill bg-light text-dark bg-warning">{{ $item['current-state'] }}
                                                                        </span>
                                                                    @elseif(str_contains($item['current-state'], 'allow'))
                                                                        <span id="state-{{ $item['product-id'] }}"
                                                                              class="badge rounded-pill bg-light text-dark bg-success">{{ $item['current-state'] }}
                                                                        </span>
                                                                    @else
                                                                        <span id="state-{{ $item['product-id'] }}"
                                                                              class="badge rounded-pill bg-light text-dark">{{ $item['current-state'] }}
                                                                        </span>
                                                                    @endif
                                                                </h4>
                                                            </td>
                                                            <td>
                                                                @if(!empty($item['actions']))
                                                                    @foreach($item['actions'] as $action)
                                                                        @if($action == App\Helpers\Enum::COVERAGE_MODERATION_ACTION_NO)
                                                                            {{ $action }}
                                                                        @else
                                                                            <button class="btn btn-info action"
                                                                                    id="btn-{{ $item['product-id'] }}"
                                                                                    data-individual_id="{{ $user->profile->id }}"
                                                                                    data-product_id="{{ $item['product-id'] }}"
                                                                                    data-created_by="{{ auth('internal_users')->id() }}"
                                                                                    data-action="{{ $action }}">
                                                                                {{ $action }}
                                                                            </button>
                                                                        @endif
                                                                    @endforeach
                                                                @else
                                                                    NO ACTION
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <button class="log btn btn-outline-primary rounded"
                                                                        data-individual_id="{{ $user->profile->id }}"
                                                                        data-product_id="{{ $item['product-id'] }}"
                                                                >
                                                                    Log
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </table>
                                            </div>
                                        </div>
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

@section('myscript')
    <script src="{{asset('js/jquery.form.js')}}"></script>
    <script>
        // add coverage moderation
        $(".action").on("click", function (e) {
            e.preventDefault();
            let individual_id = $(e.currentTarget).attr("data-individual_id");
            let product_id = $(e.currentTarget).attr("data-product_id");
            let created_by = $(e.currentTarget).attr("data-created_by");
            let action = $(e.currentTarget).attr("data-action");

            Swal.fire({
                title: 'Action is ' + action,
                input: 'text',
                inputPlaceholder: 'Remark(optional)',
                inputAttributes: {
                    autocapitalize: 'off'
                },
                showCancelButton: true,
                confirmButtonText: 'Add',
                showLoaderOnConfirm: true,
                preConfirm: (remark) => {
                    $(".loading").show();
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: "POST",
                        url: "{{ route('admin.coverage.moderation.action.store') }}",
                        data: JSON.stringify({
                            individual_id: individual_id,
                            product_id: product_id,
                            created_by: created_by,
                            remark: remark,
                            action: action,
                        }),
                        processData: false,
                        contentType: "application/json",
                        success: function (res) {
                            $(".loading").hide();
                            $("#btn-" + res.product_id).attr('data-individual_id', res.individual_id);
                            $("#btn-" + res.product_id).attr('data-product_id', res.product_id);
                            $("#btn-" + res.product_id).attr('data-created_by', res.created_by);
                            $("#btn-" + res.product_id).attr('data-action', res.action);
                            $("#btn-" + res.product_id).html(res.action);
                            $("#state-" + res.product_id).text(res.current_state);

                            if (res.current_state.indexOf("disallow") >= 0) {
                                $("#state-" + res.product_id).removeClass();
                                $("#state-" + res.product_id).addClass('badge rounded-pill bg-light text-dark bg-warning');
                            } else if (res.current_state.indexOf("allow") >= 0) {
                                $("#state-" + res.product_id).removeClass();
                                $("#state-" + res.product_id).addClass('badge rounded-pill bg-light text-dark bg-success');
                            }
                        }
                    });
                },
            })
        });

        // show log
        $(".log").on("click", function (e) {
            e.preventDefault();
            let individual_id = $(e.currentTarget).data("individual_id");
            let product_id = $(e.currentTarget).data("product_id");
            $(".loading").show();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: "POST",
                url: "{{ route('admin.coverage.moderation.action.index') }}",
                data: JSON.stringify({
                    individual_id: individual_id,
                    product_id: product_id,
                }),
                processData: false,
                contentType: "application/json",
                success: function (res) {
                    $(".loading").hide();
                    Swal.fire({
                        title: 'Log',
                        html: res.table,
                        width: '40%',
                        showCancelButton: false,
                        confirmButtonText: 'OK',
                    })
                }
            });
        });
    </script>
@endsection
