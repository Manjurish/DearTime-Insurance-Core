@extends('layouts.contentLayoutMaster')
@section('title', __('web/hospital.panel_hospital'))
@section('content')
    <div class="content-body">
        <section class="card">
            <div class="card-header">
                <h4 class="card-title">Coverages</h4>
            </div>
            <div class="card-content">
                <div class="card-body">
                    <div class="card-text">
                        <section>
                            <!-- Begin Users Profile -->
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-dashboard">
                                        <div class="table-responsive">
                                            <table id="table" class="display table table-data-width">
                                                <thead>
                                                    <tr>
                                                        <th>i</th>
                                                        <th>Policy ID</th>
                                                        <th>Policy Name</th>
                                                        <th>Owner Name</th>
                                                        <th>Payer Name</th>
                                                        <th>Premium</th>
                                                        <th>Status</th>
                                                        <th>Claim</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($coverages as $coverage)
                                                    <tr>
                                                        <td>{{$loop->index +1}}</td>
                                                        <td>{{$coverage->ref_no}}</td>
                                                        <td>{{$coverage->product_name}}</td>
                                                        <td>{{$coverage->owner->name ?? '-'}}</td>
                                                        <td>{{$coverage->payer->name ?? '-'}}</td>
                                                        <td>RM{{number_format($coverage->Payable)}}</td>
                                                        <td>{{$coverage->status}}</td>
                                                        <td><a href="{{route('userpanel.hospital.coverage',$coverage->uuid)}}"><i class="feather icon-monitor"></i></a></td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </section>

                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@section('mystyle')
    <link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/datatables.min.css')}}">
    <link rel="stylesheet" href="{{ asset('css/pages/data-list-view.css') }}">

@endsection
@section('myscript')
    <script src="{{asset('vendors/js/tables/datatable/datatables.min.js')}}"></script>
    <script src="{{asset('vendors/js/tables/datatable/datatables.bootstrap4.min.js')}}"></script>
<script>
    $('#table').DataTable( {

        select: {
            style:    'os',
            selector: 'td:first-child'
        },
        order: [[ 1, 'asc' ]],

        "lengthMenu": [[20, 50, -1], [20, 50, "All"]],
        language: {
            search: "_INPUT_",
            "search": '<i class="fa fa-search"></i>',
            "searchPlaceholder": "search",
        }


    } );
</script>
@endsection
