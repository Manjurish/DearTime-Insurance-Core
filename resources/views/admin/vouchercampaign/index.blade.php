@extends('layouts.contentLayoutMaster')
@section('title','Voucher Campaign Import')
@section('content')
    <section>
        <div class="card">
            <div class="card-body">

                <!-- @if (\Session::has('addedRow'))
                    @if(\Session::get('addedRow') > 0) -->
                    <div class="alert alert-success">
                        <h3> Successfully Added</h3>
                    </div>
                    @endif
                @endif

                <!-- @if (\Session::has('duplicateRow'))
                    <div class="alert alert-success">
                        <h3>{{ count(\Session::get('duplicateRow')) }} Duplicate Areka Coverage Ref:</h3>
                        <ul>
                            @foreach(\Session::get('duplicateRow') as $item)
                                <li>{!! $item !!}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif -->

                <!-- Voucher Campaign Data Import -->
                <div class="card-dashboard">
                    <h3>{{'Voucher Campaign Import'}}</h3>
                    <form action="{{ route('admin.areka.import.csv') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <input class="form-control" type="file" name="file" wire:model="file">

                                @error('file')
                                <div class="alert alert-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <button type="submit" class="btn btn-primary round">
                                {{ __('web/messages.import') }}
                            </button>
                        </div>
                    </form>

                    
                </div>

                <div class="card-dashboard">
                    <h2>{{'Sarawak Import'}}</h2>
                    <form action="{{ route('admin.sarawak.import.csv') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <input class="form-control" type="file" name="file" wire:model="file">

                                @error('file')
                                <div class="alert alert-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <button type="submit" class="btn btn-primary round">
                                {{ __('web/messages.import') }}
                            </button>
                        </div>
                    </form>

                    
                </div>
            </div>
        </div>
    </section>
    @endsection



