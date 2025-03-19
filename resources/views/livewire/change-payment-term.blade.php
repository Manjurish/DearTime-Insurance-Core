<div>
    <section>
        <div class="card">
            <div class="card-body">
                <!-- Payment Term -->
                <div class="card-dashboard">
                    <h2>{{__('web/product.payment_term')}}</h2>
                    <form wire:submit.prevent="addPaymentTermAction">
                        @csrf

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <select name="paymentTerm" class="form-control" wire:model="paymentTerm">
                                    <option value="">{{ __('web/messages.please_select_item') }}</option>
                                    <option value="monthly">{{__('web/product.monthly')}}</option>
                                    <option value="annually">{{__('web/product.annually')}}</option>
                                </select>

                                @error('paymentTerm')
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
