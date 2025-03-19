@extends('layouts.contentLayoutMaster')
@section('title','Referral')

<link rel="stylesheet" href="//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">

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
                                    <h4 class="card-title">Referral Details</h4>
                                </div>
                                <div class="card-content">
                                    <div class="card-body">
                        
                                        <p>Referee Name : {{$data->to_referee_name}}</p>
                                        <p>Referral Name : {{$data->from_referral_name}}</p>
                                        <p>Amount:{{$data->amount}}</p>
                                        <p>Payment Status:</p>
                                        
                                        <div class="col-md-4">
                                        <form class="form form-horizontal" action="{{route('admin.referral.statusupdate',$uuid)}}"  method="post" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $data->to_referee }}" >
                                        <select name="payment_status" class="form-control select"  placeholder="Payment Status..." required  {{ $data->payment_status =='ON HOLD' || $data->payment_status =='CLOSED' || $data->payment_status =='CANCEL-FL' || $data->payment_status =='NO ACTION NEEDED' || $data->payment_status =='PAID' ? 'disabled':''}} >
                                        <option value="">Please select</option>
                                        <option @if(($data->payment_status ?? null) == 'PAID') selected @endif value="PAID">PAID</option>

                                        </select>
                                        <br/>
                                        <p> Transaction Date:</p>
                                        <input type="text" name="from" value="{{$data->transaction_date != '' ? $transaction_date : ''}}" class="form-control date-from" placeholder="yy-mm-dd" {{ $data->payment_status =='ON HOLD' || $data->payment_status =='CLOSED' || $data->payment_status =='CANCEL-FL' || $data->payment_status =='NO ACTION NEEDED' || $data->transaction_date != '0000-00-00' ? 'disabled':''}}> </p>
                                        <br/>
                                        <p> Transaction Reference:<input type="text" id="transaction_ref" name="transaction_ref" value="{{$data->transaction_ref != '' ? $transaction_ref : ''}}" class="form-control" placeholder="Transaction Reference..." required {{ $data->payment_status =='ON HOLD' || $data->payment_status =='CLOSED' || $data->payment_status =='CANCEL-FL' || $data->payment_status =='NO ACTION NEEDED' || $data->transaction_ref != '' ? 'disabled':''}}> </p>
                                        <button type="submit" class="btn btn-primary waves-effect waves-light" >
                                            Save
                                        </button>
                                      </form>
                                        </div>
                                        <br/>
                                        
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
            format: 'yy-mm-dd',
            selectYears: 100,
            max:"Now",
        });
    </script>
@endsection
