<div class="add-new-data-sidebar">
    <div class="modal fade text-left" id="nominee-{{ $nominee->id }}" role="dialog" data-backdrop="false"
         aria-labelledby="myModalLabel160" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary white">
                    <h5 class="modal-title" id="myModalLabel160">{{$nominee->name}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.beneficiary.update') }}" method="post" enctype="multipart/form-data"
                      style="overflow: hidden !important;">
                    @csrf
                    <input type="hidden" name="id" value="{{ $nominee->id }}">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="file-upload">
                                            {{__('web/beneficiary.assignment_file')}}
                                        </label>
                                        <input id="file-upload" type="file" class="form-control required"
                                               name="assignment-file"
                                               placeholder="{{__('web/beneficiary.assignment_file')}}">

                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>{{__('web/beneficiary.status')}}</label>
                                        <select class="form-control" name="status" value="{{ $nominee->status }}">
                                            <option value="approve" @if($nominee->status == 'approve') selected @endif >Approve</option>
                                            <option value="reject" @if($nominee->status == 'reject') selected @endif>Reject</option>
                                            <option value="pending" @if($nominee->status == 'pending') selected @endif>Pending</option>
                                            <option value="sent-email" @if($nominee->status == 'sent-email') selected @endif>Sent Email</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <h5>Download files</h5>
                                <hr>
                                <livewire:beneficiary.documents :nominee="$nominee"></livewire:beneficiary.documents>

                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit"
                                class="btn btn-primary addNominee">@lang('web/beneficiary.submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="overlay-bg"></div>
</div>
