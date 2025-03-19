<div class="flex space-x-1 justify-around">
    @if($status == App\Helpers\Enum::ACTION_STATUS_PENDING_REVIEW)
            @if($type == App\Helpers\Enum::ACTION_TABLE_TYPE_BASIC_INFO)
                @if($event == App\Helpers\Enum::ACTION_EVENT_CHANGE_NAME || $event == App\Helpers\Enum::ACTION_EVENT_CHANGE_NATIONALITY || $event == App\Helpers\Enum::ACTION_EVENT_CHANGE_ADDRESS)
                    <button wire:click="$emitTo('change-basic-info','executeAction', '{{ $uuid }}')" class="btn btn-primary round waves-effect waves-light">
                        {{ __('web/messages.execute') }}
                        <button wire:click="$emitTo('change-basic-info','cancelAction', '{{ $uuid }}')" class="btn btn-primary round waves-effect waves-light">
                        {{ __('web/messages.reject') }}
                        @else
                    <button wire:click="$emitTo('change-basic-info','recalculateAction', '{{ $uuid }}')" class="btn btn-primary round waves-effect waves-light">
                        {{ __('web/messages.recalculate') }}
                    </button>
                    <button wire:click="$emitTo('change-basic-info','cancelAction', '{{ $uuid }}')" class="btn btn-primary round waves-effect waves-light">
                        {{ __('web/messages.reject') }}
                    <button wire:click="$emitTo('change-basic-info','executeAction', '{{ $uuid }}')" class="btn btn-primary round waves-effect waves-light">
                        {{ __('web/messages.execute') }}
                    </button>
                @endif
            @elseif($type == App\Helpers\Enum::ACTION_TABLE_TYPE_PAYMENT_TERM)
                <button wire:click="$emitTo('change-payment-term','recalculateAction', '{{ $uuid }}')" class="btn btn-primary round waves-effect waves-light">
                    {{ __('web/messages.recalculate') }}
                </button>              
                <button wire:click="$emitTo('change-payment-term','executeAction', '{{ $uuid }}')" class="btn btn-primary round waves-effect waves-light">
                    {{ __('web/messages.execute') }}
                </button>

            @elseif($type == App\Helpers\Enum::ACTION_TABLE_TYPE_CANCELL_COVERAGE)
            <button wire:click="$emitTo('coverage.cancell','recalculateAction', '{{ $uuid }}')" class="btn btn-primary round waves-effect waves-light">
                {{ __('web/messages.recalculate') }}
            </button>
            <button wire:click="$emitTo('coverage.cancell','executeAction', '{{ $uuid }}')" class="btn btn-primary round waves-effect waves-light">
                {{ __('web/messages.execute') }}
            </button>
            <button wire:click="$emitTo('coverage.cancell','cancelAction', '{{ $uuid }}')" class="btn btn-primary round waves-effect waves-light">
                {{ ('Cancel Request') }}
            @endif
    @elseif($status == App\Helpers\Enum::ACTION_STATUS_EXECUTED)
        <h4>
            <span class="badge rounded-pill bg-light text-dark">
                {{ App\Helpers\Enum::ACTION_STATUS_EXECUTED }}
            </span>
        </h4>
        @elseif($status == App\Helpers\Enum::ACTION_STATUS_REJECTED)
        <h4>
            <span class="badge rounded-pill bg-light text-dark">
                {{ App\Helpers\Enum::ACTION_STATUS_REJECTED }}
            </span>
        </h4>
        @elseif($status == App\Helpers\Enum::ACTION_STATUS_CANCEL)
        <h4>
            <span class="badge rounded-pill bg-light text-dark">
                {{ App\Helpers\Enum::ACTION_STATUS_CANCEL}}
            </span>
        </h4>
    @endif
</div>
