@extends('layouts.contentLayoutMaster')
@section('title','Reports')
@section('content')
   
    <section id="description" class="card">
        <div class="card-header">
            <h4 class="card-title">Add Voucher</h4>
        </div>
        <div class="card-content">
            <div class="card-body">
                <div class="card-text">
                    <div class="card">
                        <div class="card-content">
                           
                            <form class="form form-horizontal" method="GET" action="{{route('admin.insertvou.view')}}">
                                @csrf
                                <div class="form-group">
                                    <label>{{ __('Number starts with') }}</label><br/>
                                    <input type="text" id="Number_starts_with" name="Number_starts_with" style=" border: 1px solid #555">
                                    </div>
                                    <div class="form-group">
                                    <label>{{ __('Number of records') }}</label><br/>
                                    <input type="text" id="Number_of_records" name="Number_of_records" style=" border: 1px solid #555">
                                    </div>
                                    <div class="form-group">
                                    <label>{{ __('Campaign ID') }}</label><br/>
                                    <input type="text" id="campaign_id" name="campaign_id" style=" border: 1px solid #555">
                                    </div>

                                    <div class="col-md-6">
                                            <div class="form-group" >
                                                <label>{{__('Valid From')}}</label>
                                                <input type="text" name="valid_from" value="{{ old('valid_from') }}" class="form-control date-from" placeholder="yyyy/mm/dd">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group" >
                                                <label>{{__('Valid Till')}}</label>
                                                <input type="text" name="valid_till" value="{{ old('valid_till') }}" class="form-control date-from" placeholder="yyyy/mm/dd">
                                            </div>
                                        </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary round waves-effect waves-light mt-1">
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
            onSet: function(context) {
            // Get the selected date
            const selectedDate = this.get('select', 'yyyy/mm/dd');
            $(this.$node).val(`${selectedDate} 00:00:00`);
        }
        });
    </script>
@endsection

