@if($column['hidden'])
@else
<div class="relative table-cell h-1 overflow-hidden align-top">
    <button wire:click.prefetch="sort('{{ $index }}')" class="w-full h-full px-1 py-2 border-b border-gray-200 bg-gray-200 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider flex items-center focus:outline-none @if(isset($column['align']) && $column['align'] === 'right') flex justify-end @elseif(isset($column['align']) && $column['align'] === 'center') flex justify-center @endif">
        <span class="inline ">{{ str_replace('_', ' ', $column['label']) }}</span>
        <span class="inline text-xs text-blue-400">
            @if($sort === $index)
            @if($direction)
            <x-icons.chevron-up wire:loading.remove class="h-6 w-6 text-green-600 stroke-current" />
            @else
            <x-icons.chevron-down wire:loading.remove class="h-6 w-6 text-green-600 stroke-current" />
            @endif
            @endif
        </span>
    </button>
</div>
@endif
