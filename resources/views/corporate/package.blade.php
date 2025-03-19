@extends('layouts.contentLayoutMaster')
@section('title',__('web/package.package'))
@section('content')
    <section id="basic-examples">
        <div class="row">
            <div class="col-md-12">

                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{__('web/package.package')}}</h4>
                        </div>
                    <div class="card-content">
                        <div class="card-body">
                            <p>{{__('web/package.package_desc')}}</p>

                                @csrf
                                <div class="addNewC addNewData">
                                    <a href="{{route('userpanel.groupPackage.newPackage')}}" class="btn btn-outline-primary ">
                                        {{__('web/package.add_new')}}
                                    </a>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <div class="row nominees">
                                            @foreach($packages as $package)
                                                <div class="col-lg-4 col-sm-12 nomineeData" data-email="{{$package->uuid}}">
                                                    <div class="card text-white bg-gradient-dark bg-white text-left">
                                                        <div class="card-content d-flex">
                                                            <div class="card-body">

                                                                <h4 class="card-title text-white mt-2">{{$package->name}}</h4>
                                                                <p class="card-text mb-0">{{__('web/package.payment_term')}} : {{$package->payment_term}}</p>
                                                                <p class="card-text mb-3">{{__('web/package.members')}} : {{$package->members()->count()}} Person</p>
                                                                <a data-href="{{route('userpanel.groupPackage.destroyPackage',$package->uuid)}}" class="remove">
                                                                    <i class="feather icon-trash-2 white font-size-large  mr-1 mt-1 mb-1  position-absolute" style="bottom: 0px;right: 0px"></i>
                                                                </a>
                                                                <a href="{{route('userpanel.groupPackage.editPackage',$package->uuid)}}">
                                                                    <i class="feather icon-edit-2 white font-size-large  mr-4 mt-1 mb-1  position-absolute" style="bottom: 0px;right: 0px"></i>
                                                                </a>
                                                                <a href="{{route('userpanel.groupPackage.packageMembers',$package->uuid)}}">
                                                                    <i class="feather icon-users white font-size-large   mt-1 mb-1  position-absolute" style="bottom: 0px;right: 0px;margin-right: 6rem"></i>
                                                                </a>
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

@endsection
@section('mystyle')

@endsection
@section('myscript')
<script>
    $(".remove").on("click",function (e) {
        var href = $(this).data("href");

        Swal.fire({
            title: '{{__('web/package.confirm')}}',
            text: "{{__('web/product.product_group_delete')}}",
            type: 'error',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            confirmButtonClass: 'btn btn-primary',
            cancelButtonClass: 'btn btn-danger ml-1',
            buttonsStyling: false,
        }).then(function (result) {
            if (result.value) {
                window.location = href;
            }
        })
    });

</script>
@endsection
