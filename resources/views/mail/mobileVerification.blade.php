@extends('mail.layout.master')
@section('content')
    <!-- title -->
    <tr>
        <td style="padding:16px 32px;background-color:#fff;">
            <h1 style="font-size:28px;font-weight:bold;color:#000000;letter-spacing:-1px;">{!! $title !!}</h1>
        </td>
    </tr>
    <!-- title end -->
    <!-- email /phone verification -->
    <tr>
        <td style="padding:16px 32px;background-color:#fff;">
            <p>
                {!! $content !!}
            </p>
            <p>Regards,<br>
                DearTime</p>
        </td>
    </tr>

@endsection
