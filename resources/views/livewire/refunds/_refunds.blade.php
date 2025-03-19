<div>
    <livewire:tables.refund-table></livewire:tables.refund-table>

    <!-- Modal -->
    <div wire:ignore.self class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
         aria-hidden="true">

        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form>
                        @if(!empty($allowStatus))
                            <div class="form-group">
                                <label>Status</label>
                                <select class="form-control" wire:model="status">
                                    @foreach($allowStatus as $item)
                                        <option @if($refund->status === $item) selected @endif>{{ $item }}</option>
                                    @endforeach
                                </select>
                                @error('status') <span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        @endif

                        @if($status == App\Helpers\Enum::REFUND_STATUS_APPROVE)
                            <div class="form-group">
                                <label>Effective Date</label>

                                <input type="text" name="effective_date" id="datepicker"
                                       wire:model.defer="effective_date" class="form-control effective_date">

                                @error('effective_date')
                                <div class="alert alert-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Effective Time</label>

                                <input type="text" name="effective_time" id="timepicker"
                                       wire:model.defer="effective_time" class="form-control effective_time">
                                @error('effective_time')
                                <div class="alert alert-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        @if($status == App\Helpers\Enum::REFUND_STATUS_COMPLETED)
                            <div class="form-group">
                                <label>Pay Ref NO</label>
                                <input type="text" name="pay_ref_no" id="pay_ref_no"
                                       wire:model.defer="pay_ref_no" class="form-control pay_ref_no">
                                @error('pay_ref_no')
                                <div class="alert alert-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" {{--wire:click.prevent="cancel()"--}} class="btn btn-secondary"
                            data-dismiss="modal">
                        Cancel
                    </button>
                    {{--<button type="button" wire:click.prevent="update()" onclick="confirm('Are you sure you want to remove the user from this group?') || event.stopImmediatePropagation()" class="btn btn-primary" data-dismiss="modal">
                        Save changes
                    </button>--}}

                    @if(!empty($refund))
                        @if($confirming === $refund->uuid)
                            <button wire:click="update()" class="btn btn-warning">Sure?</button>
                        @else
                            <button wire:click="confirm('{{ $refund->uuid }}')" class="btn btn-primary">Save Change
                            </button>
                        @endif
                    @endif


                </div>

            </div>
        </div>
    </div>
</div>

@section('mystyle')
    <link rel="stylesheet" type="text/css" href="{{asset('vendors/css/pickers/pickadate/pickadate.css')}}">
@endsection

@section('myscript')
    <script src="{{asset('vendors/js/pickers/pickadate/picker.js')}}"></script>
    <script src="{{asset('vendors/js/pickers/pickadate/picker.date.js')}}"></script>
    <script src="{{asset('vendors/js/pickers/pickadate/picker.time.js')}}"></script>

    <script type="text/javascript">
        $(function () {
            $("body").delegate("#datepicker", "focusin", function () {
                $("#datepicker").pickadate({
                    selectYears: true,
                    selectMonths: true,

                    format: 'dd/mm/yy',
                    formatSubmit: 'yyyy/mm/dd',
                    hiddenPrefix: 'd__',
                    hiddenSuffix: '__d',
                    selectYears: 2,
                    min: [{{\Carbon\Carbon::now()->format('Y')}},{{\Carbon\Carbon::now()->format('m') - 1}},{{\Carbon\Carbon::now()->format('d')}}]
                }).on('change', function (e) {
                    @this.set('effective_date', $("input[name='d__effective_date__d']").val());
                });
            });

            $("body").delegate("#timepicker", "focusin", function () {
                $("#timepicker").pickatime({
                    hiddenPrefix: 'time__',
                    hiddenSuffix: '__time',
                }).on('change', function (e) {
                    @this.set('effective_time', $("input[name='time__effective_time__time']").val());
                });
            });
        });

        window.addEventListener('timepicker', e => {
            $(function () {
                $("body").delegate("#datepicker", "focusin", function () {
                    $("#datepicker").pickadate({
                        selectYears: true,
                        selectMonths: true,

                        format: 'dd/mm/yy',
                        formatSubmit: 'yyyy/mm/dd',
                        hiddenPrefix: 'd__',
                        hiddenSuffix: '__d',
                        selectYears: 2,
                        min: [{{\Carbon\Carbon::now()->format('Y')}},{{\Carbon\Carbon::now()->format('m') - 1}},{{\Carbon\Carbon::now()->format('d')}}]
                    }).on('change', function (e) {
                    //@this.set('effective_date', $("input[name='d__effective_date__d']").val());
                    });
                });

                $("body").delegate("#timepicker", "focusin", function () {
                    $("#timepicker").pickatime({
                        hiddenPrefix: 'time__',
                        hiddenSuffix: '__time',
                        formatSubmit: 'H:i',
                        editable: true
                    }).on('change', function (e) {
                    @this.set('effective_time', $("input[name='time__effective_time__time']").val());
                    });
                });
            });

        });

        window.addEventListener('editModalHide', event => {
            $('#editModal').modal('hide');
        });
    </script>
@endsection

