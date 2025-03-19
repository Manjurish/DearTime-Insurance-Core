@extends('layouts.contentLayoutMaster')
@section('title',__('web/messages.claim_data_import'))
@section('content')
    <section>
        <div class="card">
            <div class="card-body">

                @if (\Session::has('addedRow'))
                    @if(\Session::get('addedRow') > 0)
                    <div class="alert alert-success">
                        <h3>{{ \Session::get('addedRow') }} item added</h3>
                    </div>
                    @endif
                @endif

                @if (\Session::has('duplicateRow'))
                    <div class="alert alert-success">
                        <h3>{{ count(\Session::get('duplicateRow')) }} Duplicate Claim No:</h3>
                        <ul>
                            @foreach(\Session::get('duplicateRow') as $item)
                                <li>{!! $item !!}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Claim Data Import -->
                <div class="card-dashboard">
                    <h2>{{__('web/messages.claim_data_import')}}</h2>
                    <form action="{{ route('admin.claims.import.csv') }}" method="POST" enctype="multipart/form-data">
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

    <section>
        <h2>Claim Data</h2>
        <livewire:tables.t-p-a-claim-table/>
    </section>

@endsection
