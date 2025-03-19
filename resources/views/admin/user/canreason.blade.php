<div class="add-new-data-sidebar">
    <div class="modal fade text-left" id="claim-{{ $claim->id }}" role="dialog" data-backdrop="false"
         aria-labelledby="myModalLabel160" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary white">
                   
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.claim.cancel')}}" method="post" enctype="multipart/form-data"
                      style="overflow: hidden !important;">
                    @csrf
                    <input type="hidden" name="id" value="{{ $claim->id }}">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-6">
                                
                                <div class="col-12">
                                    <div class="form-group">
                                         <label>Reason for Cancellation</label>
                                        <textarea class="form-control" style="width: 300px;height: 50px;font: bold" name="description"></textarea>
                                    </div>
                                </div>
                            </div>
                           
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit"
                                class="btn btn-primary cancelclaim">@lang('web/beneficiary.submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="overlay-bg"></div>
</div>
