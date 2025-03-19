<div>
    <div class="row match-height">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{__('web/messages.new_claim')}}</h4>
                </div>
                <div class="row">
                    {{--<div class="col-md-6">
                        <div class="card-content">
                            <div class="card-body">
                                <form wire:submit.prevent="authorize">
                                    @csrf

                                    <div class="form-row">
                                        <div class="form-group col-md-12">
                                            <label >{{ __('web/messages.nric') }}</label>
                                            <input type="text" name="nric" wire:model.defer="nric" class="form-control">
                                            @error('nric')
                                            <div class="alert alert-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-12">
                                            <label >{{ __('web/messages.mobile') }}</label>
                                            <input type="text" name="mobile" wire:model.defer="mobile" class="form-control">
                                            @error('mobile')
                                            <div class="alert alert-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <button type="submit" class="btn btn-primary round">
                                            {{ __('web/messages.check') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>--}}
                    <div class="col-md-6">
                        <div class="card-content">
                            <div class="card-body">
                                <form wire:submit.prevent="authorize('claim')">
                                    @csrf

                                    <div class="form-row">
                                        <div class="form-group col-md-12">
                                            <label >{{ __('web/messages.claim_no') }}</label>
                                            <input type="text" name="ref_no" style="text-transform: uppercase" wire:model.defer="ref_no" class="form-control">
                                            @error('ref_no')
                                            <div class="alert alert-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <button type="submit" class="btn btn-primary round">
                                            {{ __('web/messages.check') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($myCoverages) && !empty($beneficiaryCoverages))
        <div class="row match-height">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{__('web/messages.list_active_policies')}}</h4>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h3>Your Coverages</h3>
                                            <table class="table">
                                                <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Mobile</th>
                                                    <th>Email</th>
                                                    <th>NRIC</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr>
                                                    <td scope="row">{{ $this->profile->name }}</td>
                                                    <td>{{ $this->profile->mobile }}</td>
                                                    <td>{{ $this->profile->user->email }}</td>
                                                    <td>{{ $this->profile->nric }}</td>
                                                </tr>
                                                </tbody>
                                            </table>

                                            <div class="list-group mt-1">
                                                @foreach($myCoverages as $coverage)
                                                    <button type="button" class="list-group-item list-group-item-action" wire:click="gotoDetail('{{ $coverage['uuid'] }}')">
                                                        {{ $coverage['product_name'] }} | Coverage: {{ number_format($coverage->coverage) }} |
                                                        @if(count($coverage->claims) > 0)
                                                            <span class="badge badge-info ">{{ __('web/messages.existing_claim') }}</span>
                                                        @endif
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h3>Beneficiary</h3>
                                            <div class="list-group">
                                                @php $owners = []; @endphp
                                                @foreach($beneficiaryCoverages as $coverage)
                                                        @if(!in_array( $coverage->owner_id,$owners))
	                                                        <div>
                                                                <table class="display table table-data-width">
                                                                    <thead>
                                                                    <tr>
                                                                        <th>Name</th>
                                                                        <th>Mobile</th>
                                                                        <th>Email</th>
                                                                        <th>NRIC</th>
                                                                    </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    <tr>
                                                                        <td scope="row">{{ $this->profile->name }}</td>
                                                                        <td>{{ $this->profile->mobile }}</td>
                                                                        <td>{{ $this->profile->user->email }}</td>
                                                                        <td>{{ $this->profile->nric }}</td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            @php array_push($owners, $coverage->owner_id) @endphp
                                                        @endif
                                                    <button type="button" class="list-group-item list-group-item-action" wire:click="gotoDetail('{{ $coverage['uuid'] }}')">
                                                        {{ $coverage['product_name'] }} | Coverage: {{ number_format($coverage->coverage) }} |
                                                        @if(count($coverage->claims) > 0)
                                                            <span class="badge badge-info ">{{ __('web/messages.existing_claim') }}</span>
                                                        @endif
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!--- docs --->
                        <div class="col-md-6">
                            <div class="card-content">
                                <div class="card-body">
                                    @if(!empty($docs))
                                    <table class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <td class="text-center">Item</td>
                                            <td class="text-center">Template</td>
                                            <td class="text-center">Processed Document</td>
                                            <td class="text-center">Upload</td>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($docs as $doc)
                                            <tr>
                                                <td>
                                                    <div  class="d-flex justify-content-center align-items-center">
                                                        {{$loop->index +1 }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div  class="d-flex justify-content-center align-items-center">
                                                        {{$doc['name']}}
                                                        @if(!empty($doc['link']))
                                                            <a class="badge badge-primary ml-1" href="{{asset('documents/1. DS - Death (for PH) 20201017.xlsm')}}" target="_blank"><i class="feather icon-download"></i></a>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    @if(!empty($claim))
                                                        {{--                                                <ul style="list-style: none">--}}
                                                        @foreach($claim->documents()->where("type",$doc['name'])->get() ?? [] as $_doc)
                                                            <p class="m-1 p-0  d-flex justify-content-between align-items-center"><a href="{{$_doc->link}}">{{$_doc->name}}</a>
                                                                <span class="">
                                                                    <a class="badge badge-primary ml-1" href="{{$_doc->link}}" target="_blank"><i class="feather icon-download"></i></a>
                                                                    <a class="badge badge-primary ml-1 remove" href="#" data-href="{{route('userpanel.hospital.upload.remove',[$_doc->url])}}" target="_blank"><i class="feather icon-trash-2"></i></a>
                                                                </span>
                                                            </p>
                                                        @endforeach
                                                        {{--                                                </ul>--}}
                                                    @endif
                                                </td>
                                                <td>
                                                    <div  class="d-flex justify-content-center align-items-center">
                                                        <form wire:submit="upload" enctype="multipart/form-data">
                                                            @csrf
                                                            <input type="file" {{--name="file[{{$doc['name']}}]" --}}wire:model="file">
                                                            <br>
                                                            <br>
                                                            <button type="submit" class="btn btn-sm btn-primary round">
                                                                {{ __('web/messages.upload') }}
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>

@section('myscript')
    <script>
        window.addEventListener('swal:modal', e => {
            swal({
                type: e.detail.type,
                title: e.detail.title,
                text: e.detail.text,
                icon: e.detail.icon,
            });
        });
    </script>
@endsection