@extends('layouts.contentLayoutMaster')
@section('title', 'Underwriting List')
@section('content')
    <section id="description" class="card">
        {{-- <div class="card-header">
            <h4 class="card-title"></h4>
        </div> --}}
        <div class="card-content">
            <div class="card-body">
                <div class="card-text">
                    <div class="card">
                        <div class="card-content">
                            <livewire:underwritings.underwritings></livewire:underwritings.underwritings>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </section>
@endsection
