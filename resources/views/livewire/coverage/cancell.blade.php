<div>
    <div>
        <div class="card overflow-hidden">
            <div class="card-header">
                <h4 class="card-title">
                    Active Coverage
                    {{-- <span class="badge-secondary rounded p-1 m-10">
                        Free Look is
                        @if($profile->freelook())
                            Active
                        @else
                            Deactive
                        @endif
                    </span> --}}
                </h4>

            </div>
            <div class="card-content">
                <div class="card-body">

                    <table id="table" class="display table table-data-width">
                        <tr>
                            <th>Product Name</th>
                            <th>Payer </th>
                            <th>Active Coverage</th>
                            <th>Payment Term</th>
                            <th>Payment Monthly</th>
                            <th>Payment Annually</th>
                            <th>Request Cancellation Date </th>
                            <th>Duration Coverage</th>
                        </tr>

                        @foreach($data as $item)
                            <tr>
                                <th>
                                    {{ $item['product-name'] }}
                                </th>
                                <th>
                                    {{ \App\Individual::where('user_id',$item['payer-id'])->first()->name }}
                                </th>
                                <td>
                                    @if(is_int($item['active-coverage']))
                                        {{ number_format($item['active-coverage'],2) }}
                                    @else
                                        {{ $item['active-coverage'] }}
                                    @endif
                                </td>
                                <td>
                                    {{ $item['payment-term'] }}
                                </td>
                                <td>
                                    {{ number_format($item['payment-monthly'],2) }}
                                </td>
                                <td>
                                    {{ number_format($item['payment-annually'],2) }}
                                </td>
                                <td>
                                    {{ $item['cancel_request_date'] }} 
                                </td>
                                <td>
                                    {{ $item['duration']==0 ?'': $item['duration'].' days' }}
                                </td>
                                <td>
                                    <button class="btn btn-info action"
                                            wire:click="addCancellAction('{{ $item['product-name'] }}','{{ $item['active-coverage'] }}','{{ $item['payer-id'] }}')" {{ ($item['cov_status']=='deactivating') ? 'disabled': ''}}>
                                        Cancel
                                    </button>
                                </td>
                               
                                <td>
                                        <button id="btn" class="btn btn-info action" data-toggle="modal" data-target="#reasonforcancel-{{ $item['product-id'] }}{{ $item['payer-id'] }}"
                                        {{ ($item['cov_status']=='deactivating') ? 'disabled': ''}}      {{-- wire:click="adddeactivateAction('{{ $item['product-name'] }}','{{ $item['active-coverage'] }}')"--}}>  
                                            Deactivate 
                                        </button>
                                     
                                           
                                        
                                </td>
                                
                              
                            </tr>
                            @include('admin.users.reasons-modal')
                            
                            
                            
                        @endforeach
                    </table>
                    
                </div>
            </div>
        </div>
    </div>

    <section class="mb-2">
        <h2>{{ __('web/messages.actions') }}</h2>
        <livewire:tables.actions-table type="{{ App\Helpers\Enum::ACTION_TABLE_TYPE_CANCELL_COVERAGE }}"/>
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
