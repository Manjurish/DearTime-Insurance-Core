<div>
    <section>
        <div class="card">
            <div class="card-body">
                <!-- Claim Data Import -->
                <div class="card-dashboard">
                    <h2>{{__('web/messages.claim_data_import')}}</h2>
                    <form wire:submit.prevent="import" method="POST" enctype="multipart/form-data">

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <input class="form-control" type="file" wire:model="file">

                                @error('file')
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

            </div>
        </div>
    </section>
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
