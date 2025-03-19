@extends('layouts.contentLayoutMaster')
@section('title','Reports')
@section('content')
    <section id="description" class="card">
        <div class="card-header">
            <h4 class="card-title">Underwriting Rejection Analysis</h4>
        </div>
        <div class="card-content">
            <div class="card-body">
                <div class="card-text">
                    <div class="card">
                        <div class="card-content">

                            @if (Session::has('error') )
                                <div class="alert alert-success">
                                    {!! Session::get('error') !!}
                                </div>
                            @endif

                            <form class="form form-horizontal" method="POST" action="{{route('admin.reports.underwriting.rejection.analysis')}}">
                                @csrf
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group" >
                                                <label>{{__('web/messages.date_from')}}</label>
                                                <input type="text" name="from" value="{{ old('from') }}" class="form-control date-from" placeholder="yyyy/mm/dd">
                                                @error('from')
                                                    <div class="alert alert-danger mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group" >
                                                <label>{{__('web/messages.date_to')}}</label>
                                                <input type="text" name="to" value="{{ old('to') }}" class="form-control date-to" placeholder="yyyy/mm/dd">
                                                @error('to')
                                                    <div class="alert alert-danger mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col">
                                            <div class="form-group" >
                                                <div class="vs-checkbox-con vs-checkbox-primary">
                                                    <input type="checkbox" name="accepted">
                                                    <span class="vs-checkbox">
                                                        <span class="vs-checkbox--check">
                                                            <i class="vs-icon feather icon-check"></i>
                                                        </span>
                                                    </span>
                                                    <span class="">Including The Accepted</span>
                                                </div>
                                                @error('accepted')
                                                <div class="alert alert-danger mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary round">
                                            {{ __('web/messages.generate') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @error('year')
    <div class="alert alert-danger mt-1">{{ $message }}</div>
    @enderror

    <section id="description" class="card">
        <div class="card-header">
            <h4 class="card-title">Insurance Penetration Ratio</h4>
        </div>
        <div class="card-content">
            <div class="card-body">
                <div class="card-text">
                    <div class="card">
                        <div class="card-content">
                            @if(!empty($minYearIndividual))
                            <form class="form form-horizontal" method="POST" action="{{route('admin.reports.insurance.penetration')}}">
                                @csrf
                                <div class="form-group">
                                    <label>{{ __('Month') }}</label><br/>
                                    <input type="month" id="start" name="start" min='2021-11' style=" border: 1px solid #555">
                                    @error('start')
                                    <div class="alert alert-danger mt-1">{{ $message}}</div>
                                   @enderror

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary round waves-effect waves-light mt-1">
                                            {{ __('web/messages.generate') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                            @else
                                {{ __('web/messages.no_data_exist_for_report') }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="description" class="card">
        <div class="card-header">
            <h4 class="card-title">Percentage of Decline Cover</h4>
        </div>
        <div class="card-content">
            <div class="card-body">
                <div class="card-text">
                    <div class="card">
                        <div class="card-content">
                            @if(!empty($minYearUnderwriting))
                            <form class="form form-horizontal" method="POST" action="{{route('admin.reports.underwriting.rejection')}}">
                                @csrf
                                <div class="form-group">
                                    <label>{{ ('Month') }}</label><br/>
                                    <input type="month" id="start" name="start" min='2021-11' style=" border: 1px solid #555">
                                    @error('start')
                                                    <div class="alert alert-danger mt-1">{{ $message}}</div>
                                    @enderror

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary round waves-effect waves-light mt-1">
                                            {{ __('web/messages.generate') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                            @else
                                {{ __('web/messages.no_data_exist_for_report') }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section id="description" class="card">
        <div class="card-header">
            <h4 class="card-title">Successful Transactions</h4>
        </div>
        <div class="card-content">
            <div class="card-body">
                <div class="card-text">
                    <div class="card">
                        <div class="card-content">
                        <form class="form form-horizontal" method="POST" action="{{route('admin.reports.transaction.successful')}}">
                                @csrf
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group" >
                                                <label>{{__('web/messages.date_from')}}</label>
                                                <input type="text" name="date_from" value="{{ old('date_from') }}" class="form-control date-from" placeholder="yyyy/mm/dd">
                                                @error('date_from')
                                                    <div class="alert alert-danger mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group" >
                                                <label>{{__('web/messages.date_to')}}</label>
                                                <input type="text" name="date_to" value="{{ old('date_to') }}" class="form-control date-to" placeholder="yyyy/mm/dd">
                                                @error('date_to')
                                                    <div class="alert alert-danger mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary round">
                                            {{ __('web/messages.generate') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="description" class="card">
        <div class="card-header">
            <h4 class="card-title">Customer List</h4>
        </div>
        <div class="card-content">
            <div class="card-body">
                <div class="card-text">
                    <div class="card">
                        <div class="card-content">
                        <form class="form form-horizontal" method="POST" action="{{route('admin.reports.customer.list')}}">
                                @csrf
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group" >
                                                <label>{{__('web/messages.date_from')}}</label>
                                                <input type="text" name="date_from" value="{{ old('date_from') }}" class="form-control date-from" placeholder="yyyy/mm/dd">
                                                @error('date_from')
                                                    <div class="alert alert-danger mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group" >
                                                <label>{{__('web/messages.date_to')}}</label>
                                                <input type="text" name="date_to" value="{{ old('date_to') }}" class="form-control date-to" placeholder="yyyy/mm/dd">
                                                @error('date_to')
                                                    <div class="alert alert-danger mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary round">
                                            {{ __('web/messages.generate') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
        <section id="description" class="card">
        <div class="card-header">
            <h4 class="card-title">Underwriting Report</h4>
        </div>
        <div class="card-content">
            <div class="card-body">
                <div class="card-text">
                    <div class="card">
                        <div class="card-content">
                        <form class="form form-horizontal" method="POST" action="{{route('admin.reports.uw.data')}}">
                                @csrf
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group" >
                                                <label>{{__('web/messages.date_from')}}</label>
                                                <input type="text" name="date_from" value="{{ old('date_from') }}" class="form-control date-from" placeholder="yyyy/mm/dd">
                                                @error('date_from')
                                                    <div class="alert alert-danger mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group" >
                                                <label>{{__('web/messages.date_to')}}</label>
                                                <input type="text" name="date_to" value="{{ old('date_to') }}" class="form-control date-to" placeholder="yyyy/mm/dd">
                                                @error('date_to')
                                                    <div class="alert alert-danger mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary round">
                                            {{ __('web/messages.generate') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="description" class="card">
        <div class="card-header">
            <h4 class="card-title">False Acceptance Rate(FAR)</h4>
        </div>
        <div class="card-content">
            <div class="card-body">
                <div class="card-text">
                    <div class="card">
                        <div class="card-content">
                            @if(!empty($minYearIndividual))
                            <form class="form form-horizontal" method="POST" action="{{route('admin.reports.far.classification')}}">
                                @csrf
                                <div class="form-group">
                                    <label>{{ __('Month') }}</label><br/>
                                    <input type="month" id="start" name="start" min='2021-11' style=" border: 1px solid #555">
                                    @error('start')
                                    <div class="alert alert-danger mt-1">{{ $message}}</div>
                                    @enderror

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary round waves-effect waves-light mt-1">
                                            {{ __('web/messages.generate') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                            @else
                                {{ __('web/messages.no_data_exist_for_report') }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section id="description" class="card">
        <div class="card-header">
            <h4 class="card-title">Timeliness of Policy Issuance(TAT)</h4>
        </div>
        <div class="card-content">
            <div class="card-body">
                <div class="card-text">
                    <div class="card">
                        <div class="card-content">
                            @if(!empty($minYearIndividual))
                            <form class="form form-horizontal" method="POST" action="{{route('admin.reports.tat.classification')}}">
                                @csrf
                                <div class="form-group">
                                    <label>{{ __('Month') }}</label><br/>
                                    <input type="month" id="start" name="start" min='2021-11' style=" border: 1px solid #555">
                                    @error('start')
                                    <div class="alert alert-danger mt-1">{{ $message}}</div>
                                     @enderror

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary round waves-effect waves-light mt-1">
                                            {{ __('web/messages.generate') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                            @else
                                {{ __('web/messages.no_data_exist_for_report') }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section id="description" class="card">
        <div class="card-header">
            <h4 class="card-title">Production Report</h4>
        </div>
        <div class="card-content">
            <div class="card-body">
                <div class="card-text">
                    <div class="card">
                        <div class="card-content">
                            @if(!empty($minYearIndividual))
                            <form class="form form-horizontal" method="POST" action="{{route('admin.reports.pud.classification')}}">
                                @csrf
                                <div class="form-group">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary round waves-effect waves-light mt-1">
                                            {{ __('web/messages.generate') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                            @else
                                {{ __('web/messages.no_data_exist_for_report') }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="description" class="card">
        <div class="card-header">
            <h4 class="card-title">Insurance Penetration Report</h4>
        </div>
        <div class="card-content">
            <div class="card-body">
                <div class="card-text">
                    <div class="card">
                        <div class="card-content">
                        <form class="form form-horizontal" id="insurance-form" method="POST" action="{{route('admin.reports.ipr.classification')}}">
                                @csrf
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group" >
                                                <label>{{__('web/messages.date_from')}}</label>
                                                <input type="text" id="date_from" name="date_from" value="{{ old('date_from') }}" class="form-control date-from" placeholder="yyyy/mm/dd">
                                               
                                                <div id="error-date-from" class="alert alert-danger mt-1" style="display: none;"></div>
                   
                                                @error('date_from')
                                                    <div  class="alert alert-danger mt-1">{{ $message }}</div>
                                                @enderror

                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group" >
                                                <label>{{__('web/messages.date_to')}}</label>
                                                <input type="text" id="date_to" name="date_to" value="{{ old('date_to') }}" class="form-control date-to" placeholder="yyyy/mm/dd">
                                              
                                                <div id="error-date-to" class="alert alert-danger mt-1" style="display: none;"></div>
                    
                                                @error('date_to')
                                                    <div class="alert alert-danger mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" id="generate-btn" class="btn btn-primary round">
                                            {{ __('web/messages.generate') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section id="description" class="card">
        <div class="card-header">
            <h4 class="card-title">Customer Details</h4>
        </div>
        <div class="card-content">
            <div class="card-body">
                <div class="card-text">
                    <div class="card">
                        <div class="card-content">
                            @if(!empty($minYearIndividual))
                            <form class="form form-horizontal" method="POST" action="{{route('admin.reports.cudr.classification')}}">
                                @csrf
                                <div class="form-group">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary round waves-effect waves-light mt-1">
                                            {{ __('web/messages.generate') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                            @else
                                {{ __('web/messages.no_data_exist_for_report') }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="description" class="card">
        <div class="card-header">
            <h4 class="card-title">PROD USER DETAILS</h4>
        </div>
        <div class="card-content">
            <div class="card-body">
                <div class="card-text">
                    <div class="card">
                        <div class="card-content">
                            @if(!empty($minYearIndividual))
                            <form class="form form-horizontal" method="POST" action="{{route('admin.reports.prod.userdetails.new')}}">
                                @csrf
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary round waves-effect waves-light mt-1">
                                            {{ __('web/messages.generate') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                            @else
                                {{ __('web/messages.no_data_exist_for_report') }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
@section('mystyle')
    <link rel="stylesheet" type="text/css" href="{{asset('vendors/css/pickers/pickadate/pickadate.css')}}">
@endsection

@section('myscript')
    <script src="{{asset('vendors/js/pickers/pickadate/picker.js')}}"></script>
    <script src="{{asset('vendors/js/pickers/pickadate/picker.date.js')}}"></script>
    <script src="{{asset('vendors/js/pickers/pickadate/picker.time.js')}}"></script>

    <script>
        $('.date-from,.date-to').pickadate({
            selectYears: true,
            selectMonths: true,
            format: 'yyyy/mm/dd',
            selectYears: 100,
        });
    </script>
@endsection
