@extends('vendor.ariel.layout')
@section('title','Reports')
@section('contenter')
    <section>
        <div class="card-header">
            <h4 class="card-title">Underwriting Rejection Analysis For "{{ $underwriting->individual->name }}"</h4>
        </div>
        <!-- Begin Users Profile -->
        <div class="card">
            <div class="card-body">
                <div class="card-dashboard">

                    <strong>Weight</strong>: {{ $underwriting->answers['weight'] }} <br>
                    <strong>Height</strong>: {{ $underwriting->answers['height'] }} <br>
                    {{--<strong>Smoke</strong>: {{ $underwriting->answers['smoke'] }} <br><br>--}}
                    <strong>How many cigarettes do you smoke each day?</strong> {{ $underwriting->answers['smoke'] }} <br><br>

                    @foreach($underwriting->answers['answers'] as $answer)
                        <strong>{{ \App\UwGroup::findOrFail(\App\Uw::findOrFail($answer)->group_id)->title }}</strong>
                        {{ \App\Uw::findOrFail($answer)->title }}
                        <br>
                        <br>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection
@section('mystyle')
@endsection
@section('myscript')
@endsection
