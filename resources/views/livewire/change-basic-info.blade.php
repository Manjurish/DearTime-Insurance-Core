<div>
    <section>
        <div class="card">
            <div class="card-body">
                <!-- Name -->
                <div class="card-dashboard">
                    <h2>{{ __('web/messages.name') }}</h2>
                    <form wire:submit.prevent="addNameAction">
                        @csrf

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label >Name</label>
                                <input type="text" name="name" wire:model.defer="name" class="form-control">
                                @error('name')
                                <div class="alert alert-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <button type="submit" class="btn btn-primary round">
                                {{ __('web/messages.add_action') }}
                            </button>
                        </div>
                    </form>
                </div>
                <!-- Nationality -->
                <div class="card-dashboard mt-3">
                    <h2>{{__('web/profile.nationality')}}</h2>
                    <form wire:submit.prevent="addNationalityAction">
                        @csrf

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <div class="form-group" >
                                    <label>{{__('web/profile.nationality')}}</label>

                                    <div wire:ignore>
                                        <select name="country_id" class="form-control" wire:model="country_id" id="country_id">
                                            <option value="">{{ __('web/messages.please_select_item') }}</option>
                                            @if(!empty($country))
                                                @foreach($country as $region)
                                                    <option value="{{ $region->id }}">{{ $region->nationality }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>

                                    @error('country_id')
                                    <div class="alert alert-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group col-md-6">
                                <div class="form-group" >
                                    <label>{{ $labelNric }}</label>
                                    <input type="text" class="form-control required" wire:model="nric" name="nric" placeholder="{{ $nric }}">
                                    @error('nric')
                                    <div class="alert alert-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            @if(!$showNric)
                                <div class="form-group col-md-6">
                                    <div class="form-group" >
                                        <label>{{__('web/profile.passport_expiry_date')}}</label>
                                        <input type="text" name="passport_expiry_date" wire:model.defer="passport_expiry_date" class="form-control ped" placeholder="{{ Carbon\Carbon::parse($this->profile->passport_expiry_date)->format('d/m/y') }}">
                                        @error('passport_expiry_date')
                                        <div class="alert alert-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            @endif

                        </div>

                        <div class="form-row">
                            <button type="submit" id="changePedBtn" class="btn btn-primary round">
                                {{ __('web/messages.add_action') }}
                            </button>
                        </div>

                    </form>
                </div>
                <!-- Address -->
                <div class="card-dashboard mt-3">
                    <h2>{{__('web/messages.address')}}</h2>
                    <form wire:submit.prevent="addAddressAction">
                        @csrf

                        <div class="form-row">
                            <div class="form-group col-12">
                                <div class="form-group">
                                    <label>{{__("web/profile.residential_address_one")}}</label>
                                    <input type="text" class="form-control required" wire:model.defer="address1">
                                    @error('address1')
                                    <div class="alert alert-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group col-6">
                                <div class="form-group">
                                    <label>{{__("web/profile.residential_address_two")}}</label>
                                    <input type="text" class="form-control required" wire:model.defer="address2">
                                    @error('address2')
                                    <div class="alert alert-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group col-6">
                                <div class="form-group">
                                    <label>{{__("web/profile.residential_address_three")}}</label>
                                    <input type="text" class="form-control required" wire:model.defer="address3">
                                    @error('address3')
                                    <div class="alert alert-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <div class="form-group">
                                    <label>{{__('web/profile.state')}}</label>
                                    <div wire:ignore>
                                        <select class="form-control required"  wire:model="state_uuid" id="state_id" wire:change="getCities">
                                            <option value="">{{ __('web/messages.please_select_item') }}</option>
                                            @foreach($states as $state)
                                                <option value="{{ $state->uuid }}" >{{ $state->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('state_uuid')
                                    <div class="alert alert-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <div class="form-group">
                                    <label>{{__('web/profile.city')}}</label>
                                    <div>
                                        <select class="form-control required"  wire:model="city_uuid" id="city_id" wire:change="getZipCode">
                                            <option value="">{{ __('web/messages.please_select_item') }}</option>
                                            @if(!empty($cities))
                                                @foreach($cities as $city)
                                                    <option value="{{ $city->uuid }}" >{{ $city->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <div wire:loading wire:target="state_uuid">
                                            {{ __('web/messages.updating') }}
                                        </div>
                                    </div>
                                    @error('city_uuid')
                                    <div class="alert alert-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <div class="form-group">
                                    <label>{{__('web/profile.zipcode')}}</label>
                                    <div>
                                        <select class="form-control required" wire:model="zipcode_uuid" id="zipcode_id">
                                            <option value="">{{ __('web/messages.please_select_item') }}</option>
                                            @if(!empty($zipcodes))
                                                @foreach($zipcodes as $zipcode)
                                                    <option value="{{ $zipcode->uuid }}" >{{ $zipcode->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <div wire:loading wire:target="city_uuid">
                                            {{ __('web/messages.updating') }}
                                        </div>
                                    </div>
                                    @error('zipcode_uuid')
                                    <div class="alert alert-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <button type="submit" class="btn btn-primary round">
                                {{ __('web/messages.add_action') }}
                            </button>
                        </div>

                    </form>
                </div>
                <!-- Date of birth -->
                <div class="card-dashboard mt-3">
                    <h2>{{__('web/profile.date_of_birth')}}</h2>
                    <form wire:submit.prevent="addDobAction">
                        @csrf

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label >(YYYY/MM/DD)</label>
                                <input type="text" name="dob" wire:model.defer="dob" class="form-control dob" placeholder="{{ Carbon\Carbon::parse($this->profile->dob)->format('Y/m/d') }}">
                                @error('dob')
                                <div class="alert alert-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <button type="submit" id="addDobBtn" class="btn btn-primary round">
                                {{ __('web/messages.add_action') }}
                            </button>
                        </div>
                    </form>
                </div>
                <!-- Gender -->
                <div class="card-dashboard mt-3">
                    <h2>{{__('web/profile.gender')}}</h2>
                    <form wire:submit.prevent="addGenderAction">
                        @csrf

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <select name="gender" class="form-control" wire:model="gender">
                                    <option value="">{{ __('web/messages.please_select_item') }}</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>

                                @error('gender')
                                <div class="alert alert-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <button type="submit" class="btn btn-primary round">
                                {{ __('web/messages.add_action') }}
                            </button>
                        </div>
                    </form>
                </div>
                <!-- Occupation -->
                <div class="card-dashboard mt-3">
                    <h2>Occupation</h2>
                    <form wire:submit.prevent="addOccupationAction">
                        @csrf

                        <div class="form-row">

                            <div class="form-group col-md-6">
                                <div class="form-group">
                                    <label>{{__('web/profile.industry')}}</label>

                                    <div>
                                        <select name="industryId" class="form-control" wire:model="industryId" id="industryId" wire:change="getJobs">
                                            <option value="">{{ __('web/messages.please_select_item') }}</option>
                                            @if(!empty($industries))
                                                @foreach($industries as $industry)
                                                    <option value="{{ $industry->id }}">{{ $industry->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>

                                    @error('industryId')
                                    <div class="alert alert-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <div class="form-group">
                                    <label>{{__('web/profile.job')}}</label>

                                    <div>
                                        <select name="jobId" class="form-control" wire:model="jobId" id="jobId">
                                            <option value="">{{ __('web/messages.please_select_item') }}</option>
                                            @if(!empty($jobs))
                                                @foreach($jobs as $job)
                                                    <option value="{{ $job->id }}">{{ $job->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <div wire:loading wire:target="industryId">
                                            {{ __('web/messages.updating') }}
                                        </div>
                                    </div>

                                    @error('jobId')
                                    <div class="alert alert-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>


                        </div>

                        <div class="form-row">
                            <button type="submit" class="btn btn-primary round">
                                {{ __('web/messages.add_action') }}
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

@section('mystyle')
    <link rel="stylesheet" type="text/css" href="{{asset('vendors/css/pickers/pickadate/pickadate.css')}}">
@endsection

@section('myscript')
    <script src="{{asset('vendors/js/pickers/pickadate/picker.js')}}"></script>
    <script src="{{asset('vendors/js/pickers/pickadate/picker.date.js')}}"></script>
    <script src="{{asset('vendors/js/pickers/pickadate/picker.time.js')}}"></script>

    <script>
        <?php     
        $dob_min = \Carbon\Carbon::now()->subYears(120);
        $dob_max = \Carbon\Carbon::now();

        $ped_min = \Carbon\Carbon::now()->addMonths(1);
        $ped_max = \Carbon\Carbon::now()->addYears(20);
        ?>

        $(document).ready(function () {
            $('#country_id').select2();
            $('#country_id').on('change', function (e) {
                @this.set('country_id', e.target.value);
            });

            @if($showNric)
                $("input[name='nric']").inputmask("999999-99-9999").on('change', function (e) {
                    @this.set('nric', e.target.value);
                });
            @endif
        });

        window.addEventListener('inputmask:nric', e => {
            $("input[name='nric']").inputmask("999999-99-9999").on('change', function (e) {
                @this.set('nric', e.target.value);
            });
        });

        window.addEventListener('inputmask:passport', e => {
            $("input[name='nric']").inputmask('*{1,20}').on('change', function (e) {
                @this.set('nric', e.target.value);
            });
        });

        window.addEventListener('swal:modal', e => {
            swal({
                type: e.detail.type,
                title: e.detail.title,
                text: e.detail.text,
                icon: e.detail.icon,
            });
        });

        window.addEventListener('dob', e => {
            $('.dob').pickadate({
                selectYears: true,
                selectMonths: true,
                format: 'yyyy-mm-dd',
                formatSubmit: 'yyyy-mm-dd',
                hiddenPrefix: 'prefix__',
                hiddenSuffix: '__suffix',
                selectYears: 200,
                max: [{{$dob_max->format('Y')}},{{$dob_max->format('m') - 1}},{{$dob_max->format('d')}}],
                min: [{{$dob_min->format('Y')}},{{$dob_min->format('m') - 1}},{{$dob_min->format('d')}}]
            }).on('change', function (e) {
                //@this.set('dob', $("input[name='prefix__dob__suffix']").val());
            });
        });

        $('.dob').pickadate({
            selectYears: true,
            selectMonths: true,
            format: 'yyyy-mm-dd',
            formatSubmit: 'yyyy-mm-dd',
            hiddenPrefix: 'prefix__',
            hiddenSuffix: '__suffix',
            selectYears: 200,
            max: [{{$dob_max->format('Y')}},{{$dob_max->format('m') - 1}},{{$dob_max->format('d')}}],
            min: [{{$dob_min->format('Y')}},{{$dob_min->format('m') - 1}},{{$dob_min->format('d')}}]
        }).on('change', function (e) {
            //@this.set('dob', $("input[name='prefix__dob__suffix']").val());
        });

        $('#addDobBtn').click(function(){
            @this.set('dob', $("input[name='prefix__dob__suffix']").val());
        });

        window.addEventListener('ped', e => {
            $('.ped').pickadate({
                selectYears: true,
                selectMonths: true,
                format: 'dd/mm/yy',
                formatSubmit: 'yyyy/mm/dd',
                hiddenPrefix: 'prefix__',
                hiddenSuffix: '__suffix',
                selectYears: 100,
                max: [{{$ped_max->format('Y')}},{{$ped_max->format('m') - 1}},{{$ped_max->format('d')}}],
                min: [{{$ped_min->format('Y')}},{{$ped_min->format('m') - 1}},{{$ped_min->format('d')}}]
            }).on('change', function (e) {
                //@this.set('passport_expiry_date', $("input[name='prefix__passport_expiry_date__suffix']").val());
            });
        });

        $('.ped').pickadate({
            selectYears: true,
            selectMonths: true,
            format: 'dd/mm/yy',
            formatSubmit: 'yyyy/mm/dd',
            hiddenPrefix: 'prefix__',
            hiddenSuffix: '__suffix',
            selectYears: 100,
            max: [{{$ped_max->format('Y')}},{{$ped_max->format('m') - 1}},{{$ped_max->format('d')}}],
            min: [{{$ped_min->format('Y')}},{{$ped_min->format('m') - 1}},{{$ped_min->format('d')}}]
        }).on('change', function (e) {
            //@this.set('passport_expiry_date', $("input[name='prefix__passport_expiry_date__suffix']").val());
        });

        $('#changePedBtn').click(function(){
            @this.set('passport_expiry_date', $("input[name='prefix__passport_expiry_date__suffix']").val());
        });
    </script>
@endsection
