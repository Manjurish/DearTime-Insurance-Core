@isset($pageConfigs)
{!! \App\Helpers::updatePageConfig($pageConfigs) !!}
@endisset

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width,initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title') - DearTime</title>
        <link rel="shortcut icon" type="image/x-icon" href="{{asset('images/deartime/favicon-large.png')}}">
        <link rel="apple-touch-icon" sizes="192x192" href="{{asset('images/deartime/favicon-large.png')}}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{asset('images/deartime/favicon-small.png')}}">
        <link rel="icon" type="image/png" sizes="192x192" href="{{asset('images/deartime/favicon-large.png')}}">
        <link rel="manifest" href="{{asset('images/deartime/favicon-large.png')}}">
        {{-- Include core + vendor Styles --}}
        @include('panels/styles')
        {{-- Include page Style --}}
        @yield('mystyle')

        @livewireStyles
    </head>

    {{-- {!! Helper::applClasses() !!} --}}
    @php
    use App\Helpers;
    $configData = Helpers::applClasses();
    @endphp
    @extends((( $configData["mainLayoutType"] === 'horizontal') ? 'layouts/horizontalLayoutMaster' : 'layouts.verticalLayoutMaster' ))
