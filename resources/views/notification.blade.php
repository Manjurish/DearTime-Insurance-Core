<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{!! $notification->title  ?? '' !!}</title>
    <style>
        @font-face{font-family:Rubik;src:url("{{asset('fonts/rubik_light.ttf')}}")};
        body{
            font-family: Rubik;
            font-size: 12px;
        }
    </style>
</head>
<body>
@php
    $text = $notification->full_text;
    $text = str_replace("\n","<br>",$text);
@endphp
@if($from_web)
<h3 style="font-family: Rubik">{!! $notification->title  ?? '' !!}</h3>
@endif
<p  style="font-family: Rubik;line-height: @if($from_web) 2rem @else 1.5rem @endif">
    {!! $text  ?? '' !!}
</p>
@if($from_web)
    @php
        $data = json_decode($notification->data);
        $buttons = $data->buttons ?? [];
    @endphp
    @foreach($buttons as $button)
        @if(!empty($button->title))
            <a class="btn btn-outline-primary px-75 waves-effect waves-light action" href="#" data-uuid="{{$notification->uuid}}" data-action="{{$button->action}}">{{$button->title}}</a>
        @else
            @if($loop->index == 0)
                <a class="btn btn-outline-primary px-75 waves-effect waves-light" href="{{\App\Helpers::route($data->data)}}">{{$button}}</a>
            @else
                <a class="btn btn-outline-primary px-75 waves-effect waves-light" href="{{route('userpanel.dashboard.main')}}">{{$button}}</a>

            @endif
        @endif
    @endforeach
    <script>
        $(".action").on("click",function(e){
            $(".loading").show();
            $.post('{{route('wb-api.msgAction')}}',{uuid:$(this).data('uuid'),action:$(this).data('action'),_token:'{{csrf_token()}}'},function (e) {
                $(".loading").hide();

                window.location = e.url || '';

            })
        })
    </script>
@endif
</body>
</html>
