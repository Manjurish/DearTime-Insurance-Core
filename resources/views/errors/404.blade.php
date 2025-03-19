@extends('errors::minimal')
@section('img')
    <img src="{{asset('images/404.svg')}}" style="min-width: 500px" alt="DearTime">
@endsection
@section('title', __('Not Found'))
@section('code', '404')
@section('message', __('Not Found'))
