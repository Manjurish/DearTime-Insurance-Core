<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
@if($mobile == '1')
    <iframe  src="{{route('doc.generate',['q'=>$queries])}}" scrolling="yes" frameborder="0" height="100%" width="100%" style="position:absolute; clip:rect (190px,1100px,800px,250px);">

    @endif
@include('panels.loading')

<script src="{{asset('vendors/js/vendors.min.js')}}"></script>
<script src="{{asset('vendors/js/lottie.min.js')}}" type="text/javascript"></script>

<script>

       var loading = bodymovin.loadAnimation({container: document.getElementById('loading'), path: '{{asset('images/loading.json')}}', renderer: 'svg', loop: true, autoplay: true});


       @if($mobile == '1')
       {{--window.location = "{{route('doc.generate',['q'=>$queries])}}";--}}
       @else
       $('.loading').show();
       var e = document.createElement('embed');
       e.src = "{{route('doc.generate',['q'=>$queries])}}";
       e.type = "application/pdf";
       e.width = '100%';
       e.height = '100%';
       document.body.appendChild(e);

       e.addEventListener('load', function()
       {
           $('.loading').hide();

           // Operate upon the SVG DOM here
           $('#loading-container').remove();
       });

    @endif


</script>
</body>


</html>
