<div>
    @foreach($nominee->documents as $document)
        <div class="row">
            <div class="col-8">
                {{ $document->name }}
            </div>
            <div class="col-2">
                <a href="{{ $document->Link }}">
                    <i class="feather icon-download"></i>
                </a>
            </div>
            <div class="col-2">
                <a wire:click="deleteDoc('{{ $document }}')">
                    <i class="feather icon-trash"></i>
                </a>
            </div>
        </div>
        <hr>
    @endforeach
</div>

