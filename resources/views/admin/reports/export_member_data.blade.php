@extends('layouts.contentLayoutMaster')
@section('title','Export Member Data')
@section('content')
    <section id="description" class="card">
        <div class="card-header">
            <h4 class="card-title">Export Member Data</h4>
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

                            <form class="form form-horizontal" method="POST" action="{{route('admin.reports.export.member.data')}}">
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