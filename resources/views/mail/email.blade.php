@extends('mail.layout.master')
@section('content')
    <!-- title -->
    <tr>
        <td style="padding:16px 32px;background-color:#fff;">
            <h1 style="font-size:28px;font-weight:bold;color:#000000;letter-spacing:-1px;">{!! $title !!}</h1>
        </td>
    </tr>
    <!-- title end -->

    <tr>
        <td style="padding:16px 32px;background-color:#fff;@if(isset($confetti)) background-image:url({{ asset('email/email/img/confetti.png') }}); @endif">
            <p>{!! $content !!}</p>

            {{-- if neeed button start--}}
            @include('mail.partial.custom-buttons')
            {{-- if neeed button end --}}

            {{-- if neeed downlaod app start--}}
            @if($downloadApp)
                @include('mail.partial.download-button')
            @endif
            {{-- if neeed downlaod app end --}}

            {{-- if neeed downlaod app start--}}
            @if($referrer)
                @include('mail.partial.referrer')
            @endif
            {{-- if neeed downlaod app end --}}


        </td>

    </tr>

@endsection
