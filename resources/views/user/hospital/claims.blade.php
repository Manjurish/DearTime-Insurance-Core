@extends('layouts.contentLayoutMaster')
@section('title', __('web/hospital.panel_hospital'))
@section('content')
    <div class="content-body">
        <section class="card">
            <div class="card-header">
                <h4 class="card-title"></h4>
            </div>
            <div class="card-content">
                <div class="card-body">
                    <div class="card-text">
                        <section>
                            <!-- Begin Users Profile -->
                            <div class="card">
                                <div class="card-body">
                                    <div class="row justify-content-between">
                                        <div class="ml-2">
                                            <a href="{{route('userpanel.hospital.scan')}}" class="btn btn-primary round waves-effect waves-light">
                                                New Claim
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card-dashboard">
                                        <div class="table-responsive">
                                            <table id="table" class="display table table-data-width">
                                                <thead>
                                                    <tr>
                                                        <th>i</th>
                                                        <th>Claim ID</th>
                                                        <th>Claimant Name</th>
                                                        <th>Owner Name</th>
                                                        <th>Coverage Name</th>
                                                        <th>Status</th>
                                                        <th>Edit</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($claims as $claim)
                                                    <tr>
                                                        <td>{{$loop->index +1}}</td>
                                                        <td>{{$claim->ref_no}}</td>
                                                        <td>{{$claim->ClaimantName}}</td>
                                                        <td>{{$claim->OwnerName}}</td>
                                                        <td>{{$claim->PolicyName}}</td>
                                                        <td>{{$claim->status}}</td>
                                                        {{--<td><a href="{{route('userpanel.hospital.claim.detail',[$claim->coverage->uuid, $claim->profile->uuid])}}"><i class="feather icon-edit-2"></i></a></td>--}}
                                                        <td><a href="{{route('userpanel.hospital.claim.detail',$claim->uuid)}}"><i class="feather icon-edit-2"></i></a></td>
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
