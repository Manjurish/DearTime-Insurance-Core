@extends('layouts.contentLayoutMaster')
@section('title', __('web/claim.choose_policy'))
@section('content')
    <section>
        <div>
            <style>
                .invalid-feedback {
                    display: block;
                    width: auto;
                }
            </style>
            <form id="submit-claim" action="{{route('userpanel.claim.store')}}" enctype="multipart/form-data" method="post">

                @csrf
                <input type="hidden" name="policy" value="{{$uuid}}">
                <input type="hidden" name="uuid" value="{{$claim_uuid ?? ''}}">

                <div class="row match-height">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">{{__('web/claim.documents')}}</h4>
                            </div>
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="row d-flex justify-content-center align-items-center">
                                        @if($hospital == 0)
                                        <div class="col-md-6">
                                            @foreach($claim['data']['questions'] ?? [] as $claim_question)
                                                @php
                                                    $value = $claim_question['data']->value ?? '';
                                                    $value = $claim['data']['data'][$value] ?? '';

                                                    $required = $claim_question['data']->required ?? false;
                                                    $values_c = $claim_question['content'] ?? [];
                                                    $vals = [];
                                                    foreach ($values_c as $v) {
                                                       if(\Illuminate\Support\Str::contains($v['title'] ?? '','data|')){
                                                           $data = explode('data|',$v['title'] ?? '');
                                                           $data = $data[1] ?? '';
                                                           $data = $claim['data']['data'][$data] ?? '-';
                                                           $vals[$v['id']] = $data;
                                                       }else{
                                                           $vals[$v['id']] =$v['title'];
                                                       }
                                                    }


                                                @endphp
                                                @if($claim_question['type'] == 'checkbox')
                                                    <fieldset class="checkbox mt-1">
                                                        <div class="vs-checkbox-con vs-checkbox-primary acceptChk">
                                                            <input type="checkbox" name="questions[q_{{$claim_question['id']}}]" value="1"  data-id="{{$claim_question['id']}}" @if($required) required @endif>
                                                            <span class="vs-checkbox"><span class="vs-checkbox--check"><i class="vs-icon feather icon-check"></i></span></span>
                                                            <span class="">{{$claim_question['title']}}</span>
                                                        </div>
                                                    </fieldset>
                                                @elseif($claim_question['type'] == 'text')
                                                    <div class="form-group">
                                                        <label>{{$claim_question['title']}}</label>
                                                        <input type="text"  name="questions[q_{{$claim_question['id']}}]" class="form-control"  data-id="{{$claim_question['id']}}" value="{{$value}}"  @if($required) required @endif/>
                                                    </div>
                                                @elseif($claim_question['type'] == 'price')
                                                    <div class="form-group">
                                                        <label>{{$claim_question['title']}}</label>
                                                        <input type="number" name="questions[q_{{$claim_question['id']}}]" class="form-control"  data-id="{{$claim_question['id']}}" value="{{$value}}"  @if($required) required @endif/>
                                                    </div>
                                                @elseif($claim_question['type'] == 'upload')
                                                    <div class="form-group">
                                                        <label>{{$claim_question['title']}}</label>
                                                        <input type="file" multiple name="questions_files[{{$claim_question['id']}}]" class="form-control"  data-id="{{$claim_question['id']}}" value="{{$value}}"  @if($required) required @endif/>
                                                    </div>
                                                @elseif($claim_question['type'] == 'free_select')
                                                    <div class="form-group">
                                                        <label>{{$claim_question['title']}}</label>
                                                        <select name="questions[q_{{$claim_question['id']}}]" class="form-control select2"  data-id="{{$claim_question['id']}}"  @if($required) required @endif>
                                                            <option value="-2">{{__('mobile.please_select')}}</option>
                                                            @foreach($vals as $k=>$val)
                                                                <option @if($value == $val) selected @endif value="{{$k}}">{{$val}}</option>
                                                            @endforeach
                                                            <option value="-1">{{__('mobile.other')}}</option>
                                                        </select>
                                                        <input type="text" placeholder="{{$claim_question['title']}}" name="questions[qa_{{$claim_question['id']}}]" data-id="{{$claim_question['id']}}" style="display: none" class="form-control mt-2" value="{{$value}}"  @if($required) required @endif/>

                                                    </div>
                                                @elseif($claim_question['type'] == 'select')
                                                    <div class="form-group">
                                                        <label>{{$claim_question['title']}}</label>

                                                        <select name="questions[q_{{$claim_question['id']}}]" class="form-control select2" data-id="{{$claim_question['id']}}"  @if($required) required @endif>
                                                            <option value="-2">{{__('mobile.please_select')}}</option>
                                                            @foreach($vals as $k=>$val)
                                                                <option  @if($value == $val) selected @endif value="{{$k}}">{{$val}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @elseif($claim_question['type'] == 'date')
                                                    <div class="form-group">
                                                        <label>{{$claim_question['title']}}</label>
                                                        <input type="text" name="questions[q_{{$claim_question['id']}}]" data-id="{{$claim_question['id']}}" class="form-control date" value="{{$value}}"  @if($required) required @endif/>

                                                    </div>
                                                @elseif($claim_question['type'] == 'time')
                                                    <div class="form-group">
                                                        <label>{{$claim_question['title']}}</label>
                                                        <input type="time" name="questions[q_{{$claim_question['id']}}]" data-id="{{$claim_question['id']}}" class="form-control" value="{{$value}}"  @if($required) required @endif/>
                                                    </div>
                                                @endif

                                            @endforeach
                                        </div>
                                        @endif
                                        <div class="col-md-6 d-flex justify-content-center align-items-center flex-column" id="qrcode">
                                        <p>{{__('web/claim.time_to_refresh')}}: <span id="timer-counter"></span> {{__('web/claim.time_to_refresh_postfix')}}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        {{__('web/claim.save')}}
                    </button>
                </div>
            </form>
        </div>
    </section>

@endsection
@section('mystyle')
    <link rel="stylesheet" type="text/css" href="{{asset('vendors/css/pickers/pickadate/pickadate.css')}}">
    <style>
        .picker{
            bottom: 85% !important;
            top: unset !important;
        }
    </style>
@endsection
@section('myscript')
    <script src="{{asset('vendors/js/pickers/pickadate/picker.js')}}"></script>
    <script src="{{asset('vendors/js/pickers/pickadate/picker.date.js')}}"></script>
    <script src="{{asset('vendors/js/pickers/pickadate/picker.time.js')}}"></script>
    <script src="{{asset('vendors/js/pickers/pickadate/legacy.js')}}"></script>
    <script src="{{asset('js/filepond.min.js')}}"></script>
{{--    <script src="{{asset('js/filepond.jquery.js')}}"></script>--}}

    <script src="{{asset('vendors/js/qr/qrcode.js')}}"></script>
{{--    <script>--}}
{{--        $(function() {--}}
{{--            $('.my-pond').filepond({--}}
{{--                instantUpload:false--}}
{{--            });--}}
{{--        });--}}
{{--        </script>--}}
    <script>

     var qrcode;
        function getQR(uuid) {
            $.ajax({
                type: "POST",
                url: "{{route('wb-api.generateQR')}}",
                data: JSON.stringify({type: 'claim', uuid}),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                processData: false,
                contentType: "application/json",
                success: function (res) {
                    var expiry = res.data.expiry;
                    var qrTimer = setInterval(function () {
                        // console.log(expiry)

                        $('#timer-counter').html(expiry);
                        if (expiry === 0) {
                            clearInterval(qrTimer);
                            getQR(uuid);
                        }

                        expiry--;
                    }, 1000)
                    // console.log(qrcode)
                    if(qrcode)
                    {
                        qrcode.clear();
                        qrcode.makeCode(res.data.uuid);
                    } else
                     qrcode = new QRCode("qrcode", {
                        text: res.data.uuid,
                        // width: 256,
                        // height: 256,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.H
                    });

                }
            })
        }
     getQR('{{$uuid}}');

     $('.date').pickadate({
         selectYears: true,
         selectMonths: true,
         format: 'dd/mm/yyyy',
     });
     $(document).ready(function () {
         $("select").trigger('change')
     });
     $("select").on("change",function (e) {
         var val = $(this).val();
         if(val == '-1'){
             $(this).parents('.form-group').find("input").show();
         }else{
             $(this).parents('.form-group').find("input").hide();
         }
     })
     @foreach($claim['data']['questions'] ?? [] as $claim_question)
         @php
            $hide = (array) ($claim_question['data']->hide ?? null);
         @endphp
        @if(!empty($hide))
             @foreach($hide as $k=>$v)
                 $("[data-id={{$k}}]").on("change",function (e) {
                     var val = $(this).val();
                     var vals = [];
                    vals.push('-2');
                     @foreach($v as $ki=>$vi)
                     vals.push('{{$vi}}');
                     @endforeach
                     if(vals.includes(val)){
                         $("[data-id={{$claim_question['id']}}]").parents('.form-group').hide();
                     }else{
                         $("[data-id={{$claim_question['id']}}]").parents('.form-group').show();
                     }
                 });
            @endforeach
        @endif
        @endforeach

    </script>
@endsection
