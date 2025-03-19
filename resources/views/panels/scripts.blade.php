{{-- Vendor Scripts --}}
<div class="modal fade" id="pageModal" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalScrollableTitle">Information</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>


<script>
    var mId = '{{\App\Country::where("nationality","Malaysian")->first()->uuid ?? 0 }}';
</script>

<script src="{{asset('vendors/js/vendors.min.js')}}"></script>
<script src="{{asset('vendors/js/jquery.inputmask.min.js')}}"></script>
<script src="{{asset('vendors/js/select2.min.js')}}"></script>
<script src="{{asset('vendors/js/lottie.min.js')}}"></script>
<script src="{{asset('vendors/js/sweetalert2.all.min.js')}}"></script>
<script src="{{asset('js/scripts.js')}}" type="text/javascript"></script>
<script src="{{asset('js/app.js')}}" type="text/javascript"></script>
<script>
    var loading = bodymovin.loadAnimation({container: document.getElementById('loading'), path: '{{asset('images/loading.json')}}', renderer: 'svg', loop: true, autoplay: true});
</script>
@include('panels.zendesk')
