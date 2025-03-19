@extends('layouts.contentLayoutMaster')
@section('title', __('web/foreign.foreign'))
@section('content')
    <section id="basic-examples">
        <form id="Form" action="{{route('userpanel.foreign.store')}}" method="post">
            @csrf
            <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title"></h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <div id="surveyQuestion">
                                @foreach($foreign_questions as $foreign_question)
                                    @php
                                        $data = json_decode($foreign_question->data);
                                        if(!empty($data->source))
                                            $answers = $sources[$data->source] ?? [];
                                        else
                                            $answers = $foreign_question->answers;
                                    @endphp

                                    @if($foreign_question->type == 'select')
                                        <div class="form-group">
                                            <label>{{$foreign_question->title}}</label>
                                            <select class="form-control required select2" data-id="{{$foreign_question->id}}" name="questions[{{$foreign_question->id}}]">
                                                <option value="-1">Please Select</option>
                                                @foreach($answers as $answer)
                                                    <option value="{{ $answer->uuid  ?? $answer->id  }}">{{!empty($data->fieldName) ? $answer->{$data->fieldName} : $answer->title}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @elseif($foreign_question->type == 'upload')
                                        <div class="form-group">
                                            <label>{{$foreign_question->title}}</label>
                                            <input type="file" data-id="{{$foreign_question->id}}" name="questions_files[{{$foreign_question->id}}]" class="form-control"/>

                                        </div>
                                    @elseif($foreign_question->type == 'date')
                                        <div class="form-group">
                                            <label>{{$foreign_question->title}}</label>
                                            <input type="text" data-id="{{$foreign_question->id}}" name="questions[{{$foreign_question->id}}]" class="form-control date"/>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <div class="form-group my-2">
                                <button type="submit" class="btn btn-primary storeBtn">
                                    {{__('web/medicalsurvey.save')}}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </form>

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
    <script>
        $(document).ready(function () {
            $("select").trigger('change')
        });
        $("#Form").submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            $(".loading").show();
            $.ajax({
                url: "{{route('wb-api.foreign')}}",
                type: 'POST',
                data: formData,
                success: function (data) {
                    $(".loading").hide();
                    if(data.status == 'success'){
                        if(data.data.accept){
                            window.location = '{{route('userpanel.dashboard.main')}}';
                        }else{
                            Swal.fire({
                                title: 'Error',
                                text: data.data.msg,
                                type: 'error',
                                showCancelButton: false,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'Ok',
                                confirmButtonClass: 'btn btn-primary',
                                buttonsStyling: false,
                            });
                        }
                    }else{
                        Swal.fire({
                            title: 'Error',
                            text: 'Something wrong happened',
                            type: 'error',
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'Ok',
                            confirmButtonClass: 'btn btn-primary',
                            buttonsStyling: false,
                        });
                    }
                },
                cache: false,
                contentType: false,
                processData: false
            });
        });

        $('.date').pickadate({
            selectYears: true,
            selectMonths: true,
            format: 'dd/mm/yyyy',
        });
        @foreach($foreign_questions as $foreign_question)
            @php
                $data = json_decode($foreign_question->data,true);
            @endphp
            @if(!empty($data['hide']))
                @foreach($data['hide'] as $k=>$v)
                    $("[data-id={{$k}}]").on("change",function (e) {
                        var val = $(this).val();
                        var vals = [];
                        vals.push('-1');
                        @foreach($v as $ki=>$vi)
                            vals.push('{{$vi}}');
                        @endforeach
                        if(vals.includes(val)){
                            $("[data-id={{$foreign_question->id}}]").parents('.form-group').hide();
                        }else{
                            $("[data-id={{$foreign_question->id}}]").parents('.form-group').show();
                        }
                    });
                @endforeach
            @endif
        @endforeach
    </script>
@endsection
