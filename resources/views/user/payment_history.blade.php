@extends('layouts.contentLayoutMaster')
@section('title', __('web/policy.list_of_transactions'))
@section('content')
    <section>
        <div class="row match-height">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{__('web/policy.list_of_transactions')}}</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <div class="row">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <td>i</td>
                                            <td>{{__('web/policy.transaction_id')}}</td>
                                            <td>{{__('web/policy.card_no')}}</td>
                                            <td>{{__('web/policy.total')}}</td>
                                            <td>{{__('web/policy.date')}}</td>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($histories as $history)
                                            <tr>
                                                <td>{{$loop->index +1}}</td>
                                                <td>{{$history->transaction_id}}</td>
                                                <td>{{$history->card_no}}</td>
                                                <td>RM{{number_format($history->amount)}}</td>
                                                <td>{{$history->created_at}}</td>

                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>

@endsection
