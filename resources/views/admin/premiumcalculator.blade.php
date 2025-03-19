@extends('layouts.contentLayoutMaster')
@section('title','Reports')
@section('content')
   
    <section id="description" class="card">
        <div class="card-header">
            <h4 class="card-title">Premium Calculator</h4>
        </div>
        <div class="card-content">
            <div class="card-body">
                <div class="card-text">
                    <div class="card">
                        <div class="card-content">
                           
                            <form class="form form-horizontal" method="GET" action="{{route('con.view')}}">
                                @csrf
                                <div class="form-group">
                                    <label>{{ __('product_id') }}</label><br/>
                                    <input type="text" id="product_id" name="product_id" style=" border: 1px solid #555">
                                    </div>
                                    <div class="form-group">
                                    <label>{{ __('coverage') }}</label><br/>
                                    <input type="text" id="coverage" name="coverage" style=" border: 1px solid #555">
                                    </div>
                                    <div class="form-group">
                                    <label>{{ __('user_uuid') }}</label><br/>
                                    <input type="text" id="user_uuid" name="user_uuid" style=" border: 1px solid #555">
                                    </div>
                                    <div class="form-group">
                                    <label>{{ __('Premium_amount') }}</label><br/>
                                    <p>Ref No : {{''}}</p>
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

