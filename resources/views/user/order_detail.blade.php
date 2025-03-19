@extends('layouts.contentLayoutMaster')
@section('title',__('web/order.confirm_order'))
@section('content')
    <section id="basic-examples">
        <div class="row match-height">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">

                            @csrf
                            <div class="row mt-2">
                                <div class="col-md-12 m-2">
                                    <div>
                                        <div class="mb-2">
                                            <h4>{{__('web/order.policy_summary')}}</h4>
                                            <div class="col-12 policy_summary">

                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group my-2">
                    <button type="submit" class="btn btn-primary storeBtn">
                        {{__('web/product.place_order')}}
                    </button>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">

                            @csrf
                            <div class="row mt-2">
                                <div class="col-md-12 m-2">
                                    <div>
                                        <div class="mb-2">
                                            <h4>{{__('web/order.beneficiary_details')}}</h4>
                                            <div class="col-12 beneficiary_details">
                                            </div>
                                        </div>
                                        <div class="mb-2">

                                        <h4>{{__('web/order.thanksgiving')}}</h4>
                                            <div class="col-12 thanksgiving">

                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <h4>{{__('web/order.payment_details')}}</h4>
                                            <div class="col-12 payment_details">

                                            </div>
                                            <div class="agreement">
                                                <fieldset class="checkbox mt-1">
                                                    <div class="vs-checkbox-con vs-checkbox-primary acceptChk">
                                                        <input type="checkbox" name="accept" value="1">
                                                        <span class="vs-checkbox">
                                                        <span class="vs-checkbox--check">
                                                            <i class="vs-icon feather icon-check"></i>
                                                        </span>
                                                    </span>
                                                        <div>
                                                            <p>{{__('mobile.order_review_confirm_a_a')}}</p>
                                                            <p>a. {{__('mobile.order_review_confirm_a_b')}}</p>
                                                            <p>b. {{__('mobile.order_review_confirm_a_c')}}</p>
                                                            <p>c. {{__('mobile.order_review_confirm_a_d')}}</p>
                                                        </div>
                                                    </div>
                                                </fieldset>
                                                <fieldset class="checkbox mt-1">
                                                    <div class="vs-checkbox-con vs-checkbox-primary acceptChk">
                                                        <input type="checkbox" name="accept" value="1">
                                                        <span class="vs-checkbox">
                                                        <span class="vs-checkbox--check">
                                                            <i class="vs-icon feather icon-check"></i>
                                                        </span>
                                                    </span>
                                                        <div>
                                                            <p>{{__('mobile.order_review_confirm_b_a')}}</p>
                                                            <p>a. {{__('mobile.order_review_confirm_b_b')}}</p>
                                                            <p>b. {{__('mobile.order_review_confirm_b_c')}}</p>
                                                            <p>c. {{__('mobile.order_review_confirm_b_d')}}</p>
                                                        </div>
                                                    </div>
                                                </fieldset>
                                                <fieldset class="checkbox mt-1">
                                                    <div class="vs-checkbox-con vs-checkbox-primary acceptChk">
                                                        <input type="checkbox" name="accept" value="1">
                                                        <span class="vs-checkbox">
                                                        <span class="vs-checkbox--check">
                                                            <i class="vs-icon feather icon-check"></i>
                                                        </span>
                                                    </span>
                                                        <div>
                                                            <p>{{__('mobile.order_review_confirm_c')}}</p>
                                                        </div>
                                                    </div>
                                                </fieldset>
                                                <fieldset class="checkbox mt-1">
                                                    <div class="vs-checkbox-con vs-checkbox-primary acceptChk">
                                                        <input type="checkbox" name="accept" value="1">
                                                        <span class="vs-checkbox">
                                                        <span class="vs-checkbox--check">
                                                            <i class="vs-icon feather icon-check"></i>
                                                        </span>
                                                    </span>
                                                        <div>
                                                            <p>{{__('mobile.order_review_confirm_d_a')}} <a  class="openPage" data-src="{{route('page.index',['page'=>'order_pdpa'])}}">{{__('mobile.order_review_confirm_d_b')}}</a></p>
                                                        </div>
                                                    </div>
                                                </fieldset>
                                            </div>

                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>


        </div>
    </section>

@endsection
@section('mystyle')
    <style>
        .box{
            border: 1px solid #ccc;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        .acceptChk div{
            width: 90%;
        }
        .vs-checkbox-con{
            align-items: start;
        }
        .vs-checkbox-con input{
            width: 8% !important;
        }

    </style>
@endsection
@section('myscript')
<script>
    $(document).ready(function () {
        $(".loading").show();
        $.get("{{route('wb-api.order-review')}}",{fill_type:'{{(!empty($uid) ? 'pay_for_others' : '')}}',user_id : '{{(!empty($uid) ? $uid : '')}}'},function (data) {
            if(data.status == 'error'){
                window.location = '{{route('userpanel.product.index')}}';
                return;
            }
            $(".loading").hide();
            if(data.data.next_url){

                return Swal.fire({
                    title: 'Information',
                    text: data.data.msg,
                    type: 'error',
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Ok',
                    confirmButtonClass: 'btn btn-primary',
                    buttonsStyling: false,
                }).then(function (result) {
                    window.location = data.data.next_url;
                });
            }
            console.log(data);
            data.data.beneficiaries.map(function (val) {
                $(".beneficiary_details").append("<div class='form-group box d-flex justify-content-center align-items-start flex-column p-1 position-relative' ><label>"+val.name+"</label><p class='m-0'>"+val.relationship+"</p><div class=\"badge badge-primary badge-md mr-1 mt-1 mb-1 position-absolute\" style=\"top: 0px;right: 0px\">"+val.percentage+" %</div></div>")
            });
            if(data.data.beneficiaries.length == 0){
                $(".beneficiary_details").parents('.mb-2').hide();
            }
            data.data.thanksgiving.map(function (val) {
                $(".thanksgiving").append("<div class='form-group box d-flex justify-content-center align-items-start flex-column p-1 position-relative' ><label>"+val.type+"</label><div class=\"badge badge-primary badge-md mr-1 mt-1 mb-1 position-absolute\" style=\"top: 0px;right: 0px\">"+val.percentage+" %</div></div>")
            });
            if(data.data.thanksgiving.length == 0){
                $(".thanksgiving").parents('.mb-2').hide();
            }

            data.data.coverages.map(function (val) {
                var price = val.payment_term == 'annually' ? val.payment_annually : val.payment_monthly;
                $(".policy_summary").append("<div class='form-group box d-flex justify-content-center align-items-start flex-column p-1 position-relative' >" +
                    "<h5>"+val.product_name+"</h5>" +
                    "<p class='m-50'><b>{{__('mobile.coverage')}}</b> : "+('RM' + numberWithCommas(parseInt(val.coverage)) + '' + (val.diff != 0 ? "("+(numberWithCommas(parseInt(val.coverage) - parseInt(val.diff)) + (val.diff > 0 ? ' + ' : ' - ') + numberWithCommas((Math.abs(val.diff))))+")" : ''))+"</p>" +
                    "<p class='m-50'><b>{{__('mobile.price')}}</b> : "+('RM' + numberWithCommas(price * 0.9) + ' /' + (val.payment_term == 'annually' ? '{{__('mobile.year')}}' : '{{__('mobile.month')}}'))+"</p>" +
                    "<p class='m-50'><b>{{__('mobile.thanksgiving')}}</b> : "+('RM' + numberWithCommas(price * 0.1) + ' /' + (val.payment_term == 'annually' ? '{{__('mobile.year')}}' : '{{__('mobile.month')}}'))+"</p>" +
                    "<p class='m-50'><b>{{__('mobile.total')}}</b> : "+('RM' + numberWithCommas(price) + ' /' + (val.payment_term == 'annually' ? '{{__('mobile.year')}}' : '{{__('mobile.month')}}'))+"</p></div>")
            });
            if(data.data.coverages.length == 0){
                $(".policy_summary").parents('.mb-2');
            }

            $(".payment_details").append("<div class='form-group box d-flex justify-content-center align-items-start flex-column p-1 position-relative' ><label>{{__('mobile.payment_method')}}</label><p class='m-0'>"+data.data.payment+"</p></div>")
            $(".payment_details").append("<div class='form-group box d-flex justify-content-center align-items-start flex-column p-1 position-relative' ><label>{{__('mobile.total')}}</label><p class='m-0'>RM"+numberWithCommas(data.data.total)+"</p></div>")
            $(".payment_details").append("<div class='form-group box d-flex justify-content-center align-items-start flex-column p-1 position-relative' ><label>{{__('mobile.transaction_fee')}}</label><p class='m-0'>RM"+numberWithCommas(data.data.transaction_fee)+"</p></div>")

            $.fn.matchHeight._update()
        })

    });
    $(".storeBtn").on("click",function (e) {

        if($(".agreement").find("input[type=checkbox]:checked").length != 4){
            return Swal.fire({
                title: '{{__('web/product.error')}}',
                html: '{{__('mobile.accept_all_terms')}}',
                type: 'error',
                showCancelButton: false,
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Ok',
                confirmButtonClass: 'btn btn-primary',
                buttonsStyling: false,
            });
        }

        $(".loading").show();
        $.post("{{route('wb-api.order-process')}}",{_token:'{{csrf_token()}}',fill_type:'{{(!empty($uid) ? 'pay_for_others' : '')}}',user_id : '{{(!empty($uid) ? $uid : '')}}'},function (data) {
            $(".loading").hide();

            if(data.status == 'success'){
                Swal.fire({
                    title: '{{__('web/order.we_got_you_covered')}}',
                    text: data.message || "{{__('web/order.thank_for_subscription')}}",
                    type: 'success',
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Ok',
                    confirmButtonClass: 'btn btn-primary',
                    buttonsStyling: false,
                }).then(function (result) {
                    window.location = '{{route('userpanel.dashboard.main')}}';
                });
            }else{
                Swal.fire({
                    title: 'Error',
                    text: data.message,
                    type: 'error',
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Ok',
                    confirmButtonClass: 'btn btn-primary',
                    buttonsStyling: false,
                });
            }
            console.log(data);
        });
    });
    function numberWithCommas(value) {
        value = value.toFixed(2);
        const type = typeof value;
        const parts =
            type === 'string' ? value.split('.') : value.toString().split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return parts.join('.');
    }
</script>
@endsection
