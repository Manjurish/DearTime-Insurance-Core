@extends('layouts.contentLayoutMaster')
@section('title',__('web/claim.claim'))
@section('content')
    <section id="basic-examples">
        <div class="row match-height">
            <div class="col-md-6">
                <div class="card">
                    Lorem ipsum dolor sit amet, consectetur adipisicing elit. Deserunt esse incidunt ipsa iste itaque magnam nemo placeat sapiente? Ad alias aut dicta facere odio, quas quasi saepe sapiente ullam voluptate.
                    <div class="card-header">
                        <h4 class="card-title">{{__('web/claim.own_claim_desc')}}</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <div class="card-content scrollable-container">
                                <div class="card-body">
                                    <style>
                                        .action-btn{
                                            padding: 0.5rem;
                                            margin: 0.5rem;
                                            border-radius: 50%;
                                            background-color: rgb(245,246,250);
                                        }
                                        .list-group li:hover{
                                            background-color: #fff !important;
                                        }
                                    </style>
                                    <ul class="list-group ">
                                        @foreach($own_claims as $coverage)
                                            <li class="list-group-item d-flex flex-column p-2 parent">
                                                <span class="title">{{$coverage['covered']}}</span>
                                                <div class="w-100 mt-3 coverages" style="display: none">
                                                    <ul class="list-group ">
                                                        @foreach($coverage['coverages'] as $cov)
                                                            <li class="list-group-item d-flex p-2 align-items-center justify-content-between">
                                                                <span class="badge badge-primary position-absolute" style="right: 10px;top: 10px">{{$cov['status']}}</span>
                                                                <div class="d-flex flex-column">
                                                                    <div>
                                                                        <img src="{{asset('images/products/'.$cov['product_name'].'.png')}}" style="width: 45px"/>
                                                                        <span>{{$cov['product_name']}}</span>
                                                                    </div>
                                                                    <div>
                                                                        {{__('mobile.total_coverage')}} : RM{{number_format($cov['coverage'])}}
                                                                    </div>
                                                                </div>
                                                                <div class="actions-list">
                                                                    <a class="action-btn" data-uuid="{{$cov['uuid']}}" data-product="{{$cov['product_name']}}"><span class="feather icon-log-in"></span></a>
                                                                </div>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </li>
                                        @endforeach

                                    </ul>


                                </div>
                            </div>

                        </div>
                    </div>
            </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{__('web/claim.beneficiary_claim_desc')}}</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <div class="card-content scrollable-container">
                                <div class="card-body">
                                    <ul class="list-group ">
                                @foreach($beneficiary_claims as $coverage)
                                <li class="list-group-item d-flex flex-column p-2 parent">
                                    <span class="title">{{$coverage['covered'] ?? '-'}}</span>
                                    <div class="w-100 mt-3 coverages" style="display: none">
                                        <ul class="list-group ">
                                            @foreach($coverage['coverages'] ?? [] as $cov)
                                                <li class="list-group-item d-flex p-2 align-items-center justify-content-between">
                                                    <span class="badge badge-primary position-absolute" style="right: 10px;top: 10px">{{$cov['status']}}</span>
                                                    <div class="d-flex flex-column">
                                                        <div>
                                                            <img src="{{asset('images/products/'.$cov['product_name'].'.png')}}" style="width: 45px"/>
                                                            <span>{{$cov['product_name']}}</span>
                                                        </div>
                                                        <div>
                                                            {{__('mobile.total_coverage')}} : RM{{number_format($cov['coverage'])}}
                                                        </div>
                                                    </div>
                                                    <div class="actions-list">
                                                        <a class="action-btn" data-uuid="{{$cov['uuid']}}" data-product="{{$cov['product_name']}}"><span class="feather icon-log-in"></span></a>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </li>
                            @endforeach
                            </ul>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{__('web/claim.beneficiary_history_desc')}}</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <div class="card-content scrollable-container">
                                <div class="card-body">
                                    <ul class="list-group ">
                                        @foreach($claim_history as $history)
                                            <li class="list-group-item d-flex flex-column p-2 parent" @if(strtolower($history->status) == 'draft') onclick="window.location='{{route('userpanel.claim.create',['hospital'=>'0','uuid'=>$history->coverage_id,'claim_uuid'=>$history->uuid])}}' @endif">
                                                <span class="badge badge-primary position-absolute" style="right: 10px;top: 10px">{{$history->status}}</span>
                                                <div class="d-flex ">
                                                    <div class="mr-2">
                                                        <img src="{{asset('images/products/'.$history->PolicyName.'.png')}}" style="width: 45px"/>
                                                    </div>
                                                    <div>
                                                        <h5 class="title">{{$history->PolicyName}}</h5>
                                                        <span class="title">{{$history->OwnerName}}</span>
                                                    </div>
                                                </div>

                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
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


    $(".parent").on("click",function (e) {
        if (e.target !== this)
            return;

        $(this).find(".coverages").slideToggle();
        setTimeout(function () {
            $.fn.matchHeight._update();
        },500)

    })
    $(".parent .title").on("click",function (e) {
        $(this).parent(".parent").click();
    })
    $(".action-btn").on("click",function (e) {
        e.preventDefault();
        var uuid = $(this).data('uuid');
        var product = $(this).data('product');
        if(product == 'Accident' || product == 'Death'){
            Swal.fire({
                title: '{{__('mobile.ask_filling_claim')}}',
                text: "{{__('mobile.filling_your_hospital')}}",
                type: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '{{__('mobile.yes')}}',
                cancelButtonText: '{{__('mobile.no')}}',
                confirmButtonClass: 'btn btn-primary',
                cancelButtonClass: 'btn btn-danger ml-1',
                buttonsStyling: false,
            }).then(function (result) {
                if (result.value) {
                    window.location = '{{route('userpanel.claim.create')}}'+'?uuid='+uuid+'&hospital=1';
                }else{
                    window.location = '{{route('userpanel.claim.create')}}'+'?uuid='+uuid+'&hospital=0';
                }
            })
        }else{
            window.location = '{{route('userpanel.claim.create')}}'+'?uuid='+uuid+'&hospital=0';

        }

    })
</script>
@endsection
