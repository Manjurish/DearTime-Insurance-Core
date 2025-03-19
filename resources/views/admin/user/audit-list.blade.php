@extends('layouts.contentLayoutMaster')
@section('title','User Audit Trail')
@section('mystyle')
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
@endsection
@section('content')
    <div class="">

        <div class="content-header row">
            <div class="content-header-left col-md-9 col-12 mb-2">
                <div class="row breadcrumbs-top">
                </div>
            </div>
        </div>

        <div class="content-body">
            <section id="description" class="card">
                <div class="card-header">
                    <h4 class="card-title"></h4>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="card-text">
                            <section>
                                <!-- Begin Audit -->
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row justify-content-between">

                                            <div class="ml-2">


                                            </div>
                                        </div>
                                        <div class="card-dashboard">
                                            <div class="table-responsive">

                                                <div id="table_wrapper"
                                                     class="dataTables_wrapper dt-bootstrap4 no-footer">
                                                    <div class="row">
                                                        <div class="col-sm-12 col-md-6">
                                                            <div class="dataTables_length" id="table_length"><label>Show
                                                                    <select name="table_length" aria-controls="table"
                                                                            class="custom-select custom-select-sm form-control form-control-sm">
                                                                        <option value="20">20</option>
                                                                        <option value="50">50</option>
                                                                        <option value="-1">All</option>
                                                                    </select> entries</label></div>
                                                        </div>
                                                        <div class="col-sm-12 col-md-6">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                            <table id="table"
                                                                   class="display table table-data-width dataTable no-footer"
                                                                   role="grid" aria-describedby="table_info">
                                                                <thead>
                                                                <tr role="row">
                                                                    <th class="sorting_disabled" rowspan="1" colspan="1"
                                                                        style="">i
                                                                    </th>
                                                                    <th class="sorting_disabled" rowspan="1" colspan="1"
                                                                        style="">Values
                                                                    </th>

                                                                    <th class="sorting_disabled" rowspan="1" colspan="1"
                                                                        style="">Detail
                                                                    </th>
                                                                    <th class="sorting_disabled" rowspan="1" colspan="1"
                                                                        style="">Date
                                                                    </th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>

                                                                @if(count($data)==0)
                                                                    <td valign="top" colspan="5"
                                                                        class="dataTables_empty">No data available in table
                                                                    </td>
                                                                @else
                                                                    @foreach($data as $key=>$row)
                                                                        <tr @if($key%2==0)class="odd"@endif>
                                                                            <td>{{ $key+1 }}</td>
                                                                            <td>
                                                                                @foreach($row['new_values'] as $newKey=>$newValue)
                                                                                    <div class="audit-item">
                                                                                        @if(!empty($row['clearValues']['old_values'][$newKey]) || !empty($row['old_values'][$newKey]))
                                                                                        <span class="audit-values bg-red">Old {{ $newKey }}: </span>
                                                                                        <span>{{ !empty($row['clearValues']['old_values'][$newKey])? $row['clearValues']['old_values'][$newKey]: ($row['old_values'][$newKey] ?? 'not set') }}</span>
                                                                                        @endif
                                                                                        <span class="audit-values bg-green">New {{ $newKey }}: </span>
                                                                                        <span>{{ !empty($row['clearValues']['new_values'][$newKey])? $row['clearValues']['new_values'][$newKey]: ($newValue ?? 'not set') }}</span>
                                                                                    </div>
                                                                                @endforeach
                                                                            </td>
                                                                            <td>
                                                                                {{ $row['ip_address'] }}
                                                                                ({{ $row['event'] }})
                                                                                ({{ str_replace('App\\','',$row['auditable_type']) }})
                                                                            </td>
                                                                            <td>
                                                                                {{ \Carbon\Carbon::parse($row['created_at'])->format(config('static.datetime_format')) }}
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                @endif

                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-sm-12 col-md-5">
                                                            <div class="dataTables_info" id="table_info" role="status"
                                                                 aria-live="polite">Page {{ $data->currentPage() }} to
                                                                {{ $data->lastPage() }} of {{ $data->total()}} entries
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-12 col-md-7">
                                                            <div class="dataTables_paginate paging_simple_numbers"
                                                                 id="table_paginate">

                                                                <ul class="pagination">
                                                                    <li class="paginate_button page-item previous @if($data->previousPageUrl()=='') disabled @endif"
                                                                        id="table_previous"><a href="{{ $data->previousPageUrl() ?? '' }}"
                                                                                               aria-controls="table"
                                                                                               data-dt-idx="0"
                                                                                               tabindex="0"
                                                                                               class="page-link">Previous</a>
                                                                    </li>
                                                                    <li class="paginate_button page-item next @if($data->nextPageUrl()=='') disabled @endif"
                                                                        id="table_next"><a href="{{ $data->nextPageUrl() ?? '' }}"
                                                                                           aria-controls="table"
                                                                                           data-dt-idx="1" tabindex="0"
                                                                                           class="page-link">Next</a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <!-- End User Audit -->
                            </section>

                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
