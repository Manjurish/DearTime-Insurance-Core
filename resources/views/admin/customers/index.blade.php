@extends('layouts.contentLayoutMaster')
@section('title','Customer List')
@section('content')
    <section id="description" class="card">
        {{--<div class="card-header">
            <h4 class="card-title"></h4>
        </div>--}}
        <div class="card-content">
            <div class="card-body">
                <div class="card-text">
                    <div class="card">
                        <div class="card-content">
                            <livewire:tables.customers-table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
