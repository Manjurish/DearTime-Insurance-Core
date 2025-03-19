@extends('layouts.contentLayoutMaster')
@section('title',(!empty($show_bank)) ? __('web/bank.bank') : __('web/bank.card'))
@section('content')
    <section id="basic-examples">
        <div class="row match-height">
            @if(!empty($show_bank))
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{__('web/bank.bank')}}</h4>
                        </div>
                        <div class="card-content">
                            <div class="card-body">
                                <p>{{__('web/bank.bank_desc')}}</p>

                                    <div class="row mt-2">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <form id="add-form" action="{{route('userpanel.bank_account.store')}}" method="post">
                                                    <div >
                                                        @if(request()->has('mn'))
                                                            <input type="hidden" name="mn" value="{{request()->has('mn') ? '1':'0'}}">
                                                        @endif
                                                        <div class="col-12">
                                                            <div class="form-group" >
                                                                <label>{{__('web/bank.account_no')}}</label>
                                                                <input type="text" class="form-control @error('account_no') is-invalid @enderror" name="account_no" value="{{$accounts[0]->account_no ?? ''}}" placeholder="{{__('web/bank.account_no')}}">
                                                                @error('account_no')
                                                                <div class="invalid-feedback">
                                                                    {{$message}}
                                                                </div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="form-group" >
                                                                <label>{{__('web/bank.bank_name')}}</label>

                                                                <select  class="form-control @error('bank_name') is-invalid @enderror" name="bank_name">
                                                                    @foreach(config('static.banks') as $bank)
                                                                        <option @if(($accounts[0]->bank_name ?? '') == $bank)  selected @endif value="{{$bank}}">{{$bank}}</option>
                                                                    @endforeach
                                                                </select>
                                                                @error('bank_name')
                                                                <div class="invalid-feedback">
                                                                    {{$message}}
                                                                </div>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                    </div>
                                                    @csrf

                                                </form>
                                            </div>

                                        </div>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @if(!empty($show_card))
                <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{__('web/bank.card')}}</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <p>{{__('web/bank.card_desc')}}</p>

                                @csrf
                            @if(count($cards) == 0)
                                <div class="">
                                    <a href="#" class="btn btn-outline-primary addNewDataCard">
                                        {{__('web/bank.add_new_card')}}
                                    </a>
                                </div>
                            @endif
                                <div class="row mt-2">
                                    <div class="col-md-12">
                                        <div class="row">
                                            @foreach($cards as $card)
                                                <div class="col-md-6 ">
                                                    <div class="card text-white bg-gradient-dark bg-white text-left">
                                                        <div class="card-content d-flex">
                                                            <div class="card-body">
                                                                <h4 class="card-title text-white mt-3">{{$card->cc}}</h4>
                                                                <p class="card-text mb-3">{{$card->cvv}}</p>
                                                                <div class="badge badge-primary badge-md mr-1 mb-1 position-absolute" style="top: 12px;right: 0px">{{$card->expiry_date}}</div>
                                                                <i class="feather icon-trash-2 white font-size-large  mr-1 mt-1 mb-1  position-absolute remove" data-href="{{route('userpanel.bank_account.destroy',$card->uuid)}}" style="bottom: 0px;right: 0px"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach

                                        </div>

                                    </div>

                                    <hr>
                                    <div class="row ml-0">

                                        <div class="col-8 mb-2">
                                            <fieldset>
                                                <p class="mb-0">{{__('mobile.source_of_fund_question')}}</p>
                                                <select class="form-control required select2 w-50" name="fund_source">
                                                    @php
                                                        $card_fund_source = auth()->user()->profile->fund_source ?? null;
                                                    @endphp
                                                    <option value="">{{__('mobile.please_select')}}</option>
                                                    <option @if($card_fund_source == 'self_employed_income') selected @endif value="self_employed_income">{{__('mobile.self_employed_income')}}</option>
                                                    <option @if($card_fund_source == 'income_from_employment') selected @endif value="income_from_employment">{{__('mobile.income_from_employment')}}</option>
                                                    <option @if($card_fund_source == 'investment') selected @endif value="investment">{{__('mobile.investment')}}</option>
                                                    <option @if($card_fund_source == 'savings') selected @endif value="savings">{{__('mobile.savings')}}</option>
                                                    <option @if($card_fund_source == 'inheritance') selected @endif value="inheritance">{{__('mobile.inheritance')}}</option>
                                                </select>
                                            </fieldset>
                                        </div>
                                        <div class="col-12">
                                            <fieldset>
                                                <div class="custom-control custom-switch custom-switch-success mr-2 mb-1">
                                                    <p class="mb-0">{{__('web/bank.auto_billing')}}</p>
                                                    <input name="auto_debit" type="checkbox" @if(($cards->first()->auto_debit ?? 0) == '1') checked @endif class="custom-control-input" id="customSwitch11">
                                                    <label class="custom-control-label" for="customSwitch11">
                                                        <span class="switch-icon-left"><i class="feather icon-check"></i></span>
                                                        <span class="switch-icon-right"><i class="feather icon-check"></i></span>
                                                    </label>
                                                </div>
                                            </fieldset>
                                        </div>
                                        <div class="col-12">
                                            <fieldset>
                                                <div class="vs-checkbox-con vs-checkbox-primary">
                                                    <input type="checkbox" name="accept" style="width: 5%" value="1">
                                                    <span class="vs-checkbox vs-checkbox-lg">
                                                      <span class="vs-checkbox--check">
                                                        <i class="vs-icon feather icon-check"></i>
                                                      </span>
                                                    </span>
                                                    <span class="">{{__('web/bank.i_agree')}} <a class="openPage" href="#" data-src="{{route('page.index',['CreditCardTerms','mobile'=>'1'])}}">{{__('web/bank.terms_condition')}}</a> {{__('web/bank.for_credit_auth')}}</span>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                </div>

                        </div>
                    </div>
            </div>
            </div>
            @endif
        </div>
        <div class="form-group my-2">
            <button type="submit" class="btn btn-primary storeBtn">
                Save
            </button>
        </div>
        <div class="add-new-data-sidebar">
            <div class="overlay-bg"></div>
            <div class="modal fade text-left" id="add-card-modal" tabindex="-1" role="dialog" data-backdrop="false" aria-labelledby="myModalLabel160" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"  role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-primary white">
                            <h5 class="modal-title" id="myModalLabel160">{{__('web/bank.new_card')}}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form id="add-form-card" action="{{route('userpanel.bank_card.store')}}" method="post">
                            <div class="modal-body">

                                <div >
                                    <div class="col-12">
                                        <div class="form-group" >
                                            <label>{{__('web/bank.cc')}}</label>
                                            <input type="text" class="form-control" name="cc" maxlength="16" value="" placeholder="{{__('web/bank.cc')}}">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group" >
                                            <label>{{__('web/bank.cvv')}}</label>
                                            <input type="text" class="form-control" name="cvv" value="" placeholder="{{__('web/bank.cvv')}}">

                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group" >
                                            <label>{{__('web/bank.expiry_date')}}</label>
                                            <input type="text" class="form-control" name="expiry_date" value="" placeholder="{{__('web/bank.expiry_date')}}">

                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label>{{__('web/bank.is_primary')}}</label>
                                        <div class="custom-control custom-switch mr-2 my-50">
                                            <input type="checkbox" class="custom-control-input" id="customSwitch3">
                                            <label class="custom-control-label" for="customSwitch3"></label>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="modal-footer">
                                @csrf
                                <button type="submit" class="btn btn-primary">{{__('web/bank.submit')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="modal fade" id="pageModal" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalScrollableTitle">Information</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                </div>
            </div>
        </div>
    </div>

@endsection
@section('mystyle')
    <link rel="stylesheet" href="{{ asset('css/pages/data-list-view.css') }}">
    <style>
        .invalid-feedback{
            display: block !important;
        }
    </style>
@endsection
@section('myscript')
    <script>
        $(".openPage").on('click',function (e) {
            $(".loading").show();
            $.get($(this).data('src'),{},function(d){
                $(".loading").hide();
                $("#pageModal .modal-body").html(d);
                $("#pageModal").modal();
            });

        })
    </script>
    <script src="{{asset('js/scripts/data-list-view.js')}}"></script>

<script>
    $(".storeBtn").on("click",function (e) {
        Validation.clearAllValidation();
        @if(!empty($show_card))
            @if(!$isFlowDone)
            var accept = $("[name=auto_debit]").is(":checked");
            if(!accept){
                return Validation.setInvalid($("[name=auto_debit]"),"{{__('web/auth.required')}}");
            }
            @endif
        var term = $("[name=accept]").is(":checked");
        if(!term){

            return Validation.setInvalid($("[name=accept]").parent(),"{{__('web/auth.required')}}");
        }
        if($("[name=fund_source]").val() == ''){
            return Validation.setInvalid($("[name=fund_source]"),"{{__('web/auth.required')}}");

        }
            @if($cards->count() == 0)
                return Swal.fire({
                    title: 'Bank Card',
                    text: '{{__('web/bank.enter_at_least_one_card')}}',
                    type: 'error',
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Ok',
                    confirmButtonClass: 'btn btn-primary',
                    buttonsStyling: false,
                });
            @else
                if($("[name=fund_source]").val() == ''){
                    return Validation.setInvalid($("[name=fund_source]").parent(),"{{__('web/auth.required')}}");
                }
                $(".loading").show();
                $.post("{{route('userpanel.bank_account.setFundSource')}}",{fund_source : $("[name=fund_source]").val(),_token:'{{csrf_token()}}'},function (e) {
                    $(".loading").hide();


                    @if(request()->has('mn'))
                        return window.location = '';
                    @else
                    @if($skipBankDetails)
                        return window.location = '{{route('userpanel.Verification.index')}}';
                    @else
                        return window.location = '{{route('userpanel.bank_account.index')}}';
                    @endif
                    @endif

                })


            @endif
        @endif

        $("#add-form").submit();
    })
    $(".addNewDataCard").on("click",function (e) {

        e.preventDefault();
        window.location = '{{$auth_url}}';

        // $("#add-card-modal").modal('show');
    });

    $(".overlay-bg").on("click",function (e) {
        //$(".hide-data-sidebar").click();
    });
    $("#add-form").on("submit",function (e) {
        Validation.clearAllValidation();
        var account_no = $("input[name=account_no]");
        var bank_name = $("select[name=bank_name]");
        if(Validation.empty(account_no) || account_no.val().length < 8 ||  account_no.val().length > 20 ){
            Validation.setInvalid(account_no,"{{__('web/bank.account_no_validation')}}");
            return false;
        }
        if(Validation.empty(bank_name)){
            Validation.setInvalid(bank_name,"{{__('web/auth.required')}}");
            return false;
        }
        return true;

    });
    $("input[name='expiry_date']").inputmask("99/99",{placeholder:" ", clearMaskOnLostFocus: true });
    $("input[name='cvv']").inputmask("9999",{placeholder:" ", clearMaskOnLostFocus: true });
    $("#add-form-card").on("submit",function (e) {
        Validation.clearAllValidation();
        var cc = $("input[name=cc]");
        var cvv = $("input[name=cvv]");
        var expiry_date = $("input[name=expiry_date]");

        if(Validation.empty(cc) || cc.val().length != 16){
            Validation.setInvalid(cc,"{{__('web/bank.cc_validation')}}");
            return false;
        }
        if(Validation.empty(cvv) || cvv.val().length > 100){
            Validation.setInvalid(cvv,"{{__('web/bank.cvv_validation')}}");
            return false;
        }

        expiry_date_val = replaceAll(expiry_date.val()," ","");
        if(Validation.empty(expiry_date) || expiry_date_val.length != 5){
            Validation.setInvalid(expiry_date,"{{__('web/bank.expiry_date_validation')}}");
            return false;
        }
        return true;

    });

    $(".remove").on("click",function (e) {
        var href = $(this).data("href");

        Swal.fire({
            title: 'Confirm',
            text: "{{__('web/bank.confirm_delete')}}",
            type: 'error',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            confirmButtonClass: 'btn btn-primary',
            cancelButtonClass: 'btn btn-danger ml-1',
            buttonsStyling: false,
        }).then(function (result) {
            if (result.value) {
                window.location = href;
            } else {

            }

        })
    });

</script>
@endsection
