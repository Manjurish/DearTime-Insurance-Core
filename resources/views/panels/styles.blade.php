{{-- Vendor Styles --}}
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600">
<link rel="stylesheet" href="{{asset('vendors/css/vendors.min.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/ui/prism.min.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/extensions/toastr.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/extensions/sweetalert2.min.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/extensions/lightbox.min.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/forms/select/select2.min.css')}}">
<link rel="stylesheet" href="{{asset('css/core/colors/palette-gradient.css')}}">

<link href="{{ asset('css/tailwind.css') }}" rel="stylesheet">

<!-- Theme Styles -->
<link rel="stylesheet" href="{{ asset('css/bootstrap.css') }}">
<link rel="stylesheet" href="{{ asset('css/bootstrap-extended.css') }}">
<link rel="stylesheet" href="{{ asset('css/colors.css') }}">
<link rel="stylesheet" href="{{ asset('css/components.css') }}">


{{-- {!! Helper::applClasses() !!} --}}
@php
use App\Helpers;
$configData = Helpers::applClasses();
@endphp

@if($configData['theme'] == 'dark-layout')
    <link rel="stylesheet" href="{{ asset('css/themes/dark-layout.css') }}">
@endif
@if($configData['theme'] == 'semi-dark-layout')
    <link rel="stylesheet" href="{{ asset('css/themes/semi-dark-layout.css') }}">
@endif

<!-- Page Styles -->
<link rel="stylesheet" href="{{ asset('css/core/menu/menu-types/vertical-menu.css') }}">
<link rel="stylesheet" href="{{ asset('css/core/menu/menu-types/horizontal-menu.css') }}">

<style>
    .modal-dialog-scrollable .modal-content{
        max-height: 90vh !important;
    }
    .modal-dialog-scrollable form{
        overflow: scroll !important;
    }
</style>
