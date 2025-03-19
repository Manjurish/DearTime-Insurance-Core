@extends('layouts.contentLayoutMaster')
@section('title','Screening Details')
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
                            <form method="post" class="form form-horizontal" action="" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="id" value="{{ $data->id }}">
                                <div class="card-body">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="row">
                                                    <div class="col-8">
                                                        <div class="clearfix"></div>
                                                        <h3 class="mb-3">
                                                            Name: {{$data->user->profile->name ?? '-'}}</h3>
                                                        <div class="clearfix"></div>
                                                    </div>
                                                    <hr>
                                                    <div class="col-4">
                                                        <small class="mb-3">Date: {{$data->created_at ?? '-'}}</small>
                                                    </div>
                                                </div>
                                                <hr>
                                            </div>
                                            <div class="col-12">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="form-group border-right-black">
                                                            <div class="col-md-5">
                                                                <span>Search Refrence</span>
                                                            </div>
                                                            <div class="col-md-7">
                                                                <strong class="data-ProfileType">
                                                                    {{$data->ref }}
                                                                </strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <div class="col-md-5">
                                                                <span>Total Hits</span>
                                                            </div>
                                                            <div class="col-md-7">
                                                                <strong class="data-ProfileType">
                                                                    {{$data->total_hits }}
                                                                </strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="clearfix"></div>
                                            </div>

                                            <div class="col-12">
                                                <div class="row">
                                                    <div class="col-4">
                                                        <div class="form-group border-right-black">
                                                            <div class="col-md-5">
                                                                <span>Match Status</span>
                                                            </div>
                                                            <div class="col-md-7">
                                                                <strong class="data-ProfileType">
                                                                    {{$data->match_status }}
                                                                </strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="form-group border-right-black">
                                                            <div class="col-md-5">
                                                                <span>Fuzziness</span>
                                                            </div>
                                                            <div class="col-md-7">
                                                                <strong class="data-ProfileType">
                                                                    {{$data->fuzziness }}
                                                                </strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="form-group">
                                                            <div class="col-md-5">
                                                                <span>Assignee Id</span>
                                                            </div>
                                                            <div class="col-md-7">
                                                                <strong class="data-ProfileType">
                                                                    {{$data->assignee_id }}
                                                                </strong>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                                <hr>
                                                <div class="clearfix"></div>
                                            </div>

                                            <div class="col-12">
                                                <div class="row">
                                                    <div class="col-4">
                                                        <div class="form-group">
                                                            <div class="col-md-5">
                                                                <span>Risk Level</span>
                                                            </div>
                                                            <div class="col-md-7">
                                                                <strong class="data-ProfileType">
                                                                    {{$data->risk_level ?? '-' }}
                                                                </strong>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-6">
                                                        <div class="form-group border-left-black">
                                                            <div class="col-md-8">
                                                                <label>Status</label>
                                                                <select class="form-control" name="status" id="status"
                                                                        onchange="changeStatus()">
                                                                    <option
                                                                        {{ ($data->status == 'approve' ? "selected":"") }} value="approve">
                                                                        Approve
                                                                    </option>
                                                                    <option
                                                                        {{ ($data->status == 'reject' ? "selected":"") }} value="reject">
                                                                        Reject
                                                                    </option>
                                                                    <option
                                                                        {{ ($data->status == 'pending' ? "selected":"") }} value="pending">
                                                                        Pending
                                                                    </option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>


                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="form-group mb-0">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                                            Save
                                        </button>
                                        <a href="{{ url()->previous() }}"
                                           class="btn btn-danger waves-effect waves-light float-right">
                                            Back
                                        </a>
                                    </div>
                                </div>
                            </form>
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
        function changeStatus() {
            console.log("vv");
            let status = $('#status').val();
        }
    </script>
@endsection
