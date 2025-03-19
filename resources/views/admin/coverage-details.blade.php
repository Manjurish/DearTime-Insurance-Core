@extends('layouts.contentLayoutMaster')
@section('title','Coverage Details')
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
                                    <h4 class="card-title">Coverage Details</h4>
                                </div>
                                <div class="card-content">
                                    <div class="card-body">
                                        <p>Ref No : {{$coverage->ref_no}}</p>
                                        <p>Owner : <a href="{{route('admin.User.show',$coverage->owner->user->uuid ?? '')}}">{{($coverage->owner->user->name ?? '').' ('.($coverage->owner->user->ref_no ?? '').')'}}</a></p>
                                        <p>Payer : <a href="{{($coverage->owner->is_charity()||$coverage->sponsored)?"":(route('admin.User.show',$coverage->payer->uuid ?? ''))}}">{{($coverage->owner->is_charity()||$coverage->sponsored)?'DearTime Charity Fund':(($coverage->payer->name ?? '').' ('.($coverage->payer->ref_no ?? '').')')}}</a></p>
                                        <p>Covered : <a href="{{route('admin.User.show',$coverage->covered->user->uuid ?? '')}}">{{($coverage->covered->user->name ?? '').' ('.($coverage->covered->user->ref_no ?? '').')'}}</a></p>
                                        <p>Product Name : {{$coverage->product_name}}</p>
                                        <p>Status : {{$coverage->status}}</p>
                                        <p>State : {{$coverage->state}}</p>
                                        <p>Payment Mode : {{$coverage->payment_term}}</p>
                                        <p>Changed Payment Mode : {{$coverage->payment_term_new}}</p>
                                        <p>Coverage : {{'RM'.number_format($coverage->coverage)}}</p>
                                        <p>Premium : RM {{$coverage->payment_term == 'monthly' ? $coverage->payment_monthly : $coverage->payment_annually}}</p>
                                        <p>Renewal Date :{{$renewal_date}}</p>
                                        <p>Contract Start Date :{{$coverage->first_payment_on}}</p>
                                        <p>Premium at Next NDD : RM {{$premimum_amount_ndd }} </p>
                                        <p>Coverage Expiry Date : {{$coverage_expiry }} </p>
                                        <p>Coverage Duration : {{$cov_duration_format}} </p>
                                        <p>Last Payment On : {{$coverage->last_payment_on}}</p>
                                        <p>Next Payment On : {{$next_payment}}</p>
                                        @if($coverage->state == 'active')
                                        @if(!empty($coverage->payment_without_loading) && ($coverage->payment_term == 'monthly' ? number_format($coverage->payment_monthly-$coverage->payment_without_loading,2) > 0 : number_format($coverage->payment_annually-$coverage->payment_without_loading,2)> 0))
                                        <p>Loading:</p>
                                        <p>1.Loading Amount RM {{$coverage->payment_term == 'monthly' ? number_format($coverage->payment_monthly-$coverage->payment_without_loading,2) : number_format($coverage->payment_annually-$coverage->payment_without_loading,2)}} due to @if(!empty($occ_details) && ($coverage->product_id == 1? ($occ_details['occ_load'] > 0):($occ_details['occ_load'] > 1))) Occupation Loading({{\App\IndustryJob::where('id',$coverage->owner->occ)->first()->name}})@endif @if(!empty($title))@if(!empty($occ_details)&& ($coverage->product_id == 1? ($occ_details['occ_load'] > 0):($occ_details['occ_load'] > 1)))and @endif Illness ({{implode(",",$title)}})@endif</p>
                                        @endif
                                        @if(!empty($exceptions))
                                        <p>Exclusions:</p>
                                        @php
                                        $num =0;
                                        @endphp
                                        @foreach($exceptions as $exp)
                                        @php
                                        $num +=1;
                                        @endphp
                                        <p>{{ $num.".".$exp }}</p>
                                        @endforeach
                                       @endif
                                       @endif
                                        <p>PDS Reviewed On : {{\App\UserPdsReview::where("individual_id",$coverage->covered->id ?? 0)->where("product_id",$coverage->product_id)->first()->created_at ?? 'Not Reviewed'}}</p>
                                        <p>Download PDS : <a target="_blank" class="btn btn-info" href="{{ route('doc.view', ['app_view' => '2', 'coverage' => ($coverage->product_name == \App\Helpers\Enum::PRODUCT_NAME_MEDICAL)?$coverage->real_coverage ?? 2000 : $coverage->coverage, 'term' => $coverage->payment_term, 'type' => 'pds', 'p' => $coverage->product_name ?? '', 'uuid' => encrypt($coverage->covered->user_id)]) }}">Download</a></p>
                                        @if($coverage->state == \App\Helpers\Enum::COVERAGE_STATE_ACTIVE)<p>Download Contract : <a target="_blank" class="btn btn-info" href="{{ route('doc.view', ['app_view' => '2', 'coverage' => $coverage->uuid ?? '-1', 'type' => 'contract', 'uuid' => encrypt($coverage->covered->user_id)]) }}">Download</a></p>@endif
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
