@isset($pageConfigs)
    @if(count($pageConfigs) > 0)
        @foreach ($pageConfigs as $config => $val)
            {{ Config::set('custom.custom.'.$config, $val) }}
        @endforeach
    @endif
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
        <link rel="icon" href="{{asset('images/deartime/favicon-small.png')}}" sizes="32x32"/>
        <link rel="icon" href="{{asset('images/deartime/favicon-large.png')}}" sizes="192x192"/>
        <link rel="apple-touch-icon" href="{{asset('images/deartime/favicon-large.png')}}"/>
        <meta name="msapplication-TileImage" content="{{asset('images/deartime/favicon-large.png')}}"/>


        {{-- Include core + vendor Styles --}}
        @include('panels/styles')

        {{-- Include page Style --}}
        @yield('pageStyle')

    </head>

    {{-- {!! Helper::applClasses() !!} --}}
    @php
    use App\Helpers;
    $configData = Helpers::applClasses();
    @endphp

    <body class="vertical-layout vertical-menu-modern 1-column blank-page {{ $configData['bodyClass'] }} {{ $configData['theme'] }}" data-menu="vertical-menu-modern" data-col="1-column">

        <!-- BEGIN: Content-->
        <div class="app-content content">
            <div class="content-wrapper">
                <div class="content-body">

                    {{-- Include Startkit Content --}}
                    @include('panels.loading')
                    @yield('content')

                </div>
            </div>
        </div>
        <!-- End: Content-->

        {{-- include default scripts --}}
        @include('panels/scripts')

        {{-- Include page script --}}
        @yield('pageScript')

    </body>
</html>
