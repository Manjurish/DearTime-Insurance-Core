<div class="flex space-x-1 justify-center">

    @if($status == App\Helpers\Enum::REFUND_STATUS_REJECT)
        <button wire:click="$emitTo('refunds.refunds','revert', '{{ $uuid }}')" class="btn btn-primary">Revert</button>
    @elseif($status != App\Helpers\Enum::REFUND_STATUS_COMPLETED)
        <button data-toggle="modal" data-target="#editModal" wire:click="$emitTo('refunds.refunds','edit', '{{ $uuid }}')" class="btn btn-primary">Edit</button>
    @else
        -
    @endif


    {{--<a href="{{ route('admin.User.audit', $uuid) }}" class="hover:bg-gray-200 rounded cursor-pointer px-1">
        <span>
           <i class="feather icon-activity"></i>
        </span>
    </a>

    <a href="{{ route('admin.User.show', $uuid) }}" class="hover:bg-gray-200 rounded cursor-pointer px-1">
        <span>
            <i class="feather icon-eye"></i>
        </span>
    </a>

    <a href="{{ route('admin.User.verification', $uuid) }}" class="hover:bg-gray-200 rounded cursor-pointer px-1">
        <span>
            <i class="feather icon-shield"></i>
        </span>
    </a>--}}
</div>
