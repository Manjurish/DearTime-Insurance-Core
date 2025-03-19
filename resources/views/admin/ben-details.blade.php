@extends('layouts.contentLayoutMaster')
{{-- @section('title','Beneficiary Details') --}}
@section('content')
    <section id="description" class="card">
        <div class="card-header">
            <h4 class="card-title"></h4>
        </div>
        <div class="card-content">
            <div class="card-body">
                <section id="nav-justified">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card overflow-hidden">
                                <div class="card-header">
                                    <h4 class="card-title">Beneficiary Details</h4>
                                </div>
                                <div class="card-content">
                                    <div class="card-body">
                                        <p> Nationality : {{$nationality}}</p>
                                        <p>Email : {{$nominee->email}}</p>
                                        <p>NRIC  : {{$nominee->nric}}</p>
                                        <p>Name : {{$nominee->name}}</p>
                                        <p>Date Of Birth  : {{$nominee->dob}}</p>
                                       
                                        <br>
                                        <br>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </section>
@endsection
