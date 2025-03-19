<div class="modal fade" id="reasonforcancel-{{ $item['product-id'] }}{{ $item['payer-id'] }}" tabindex="-1" role="dialog" data-backdrop="false"  aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="ModalTitle">Reason for deactivation</h5>
    
        </div>
        <div class="modal-body">
            <select wire:model.defer="status" class="form-control" name="status" onchange="showfield(this.options[this.selectedIndex].value)">
                <option value="Request by Coverage Owner">Request by Coverage Owner</option>
                <option value="Incomplete document requested by Underwriting Dept">Incomplete document requested by Underwriting Dept</option>
                <option value="Non Disclosure by Underwrting Dept">Non Disclosure by Underwrting Dept</option>
                <option value="Non Disclosure by Claim Dept">Non Disclosure by Claim Dept</option> 
                <option value="AMLA - PEP">AMLA - PEP</option>
                <option value="AMLA - Sanction Person">AMLA - Sanction Person</option>
                <option value="Fraud">Fraud</option>
                <option value="Turn off auto billing">Turn off auto billing</option>
                <option value="Other">Others</option>
            </select>
          
        </div>
        <div class="col-12" style="display: none">
          <div class="col-md-4">
            <span>Reason  </span>
        </div>
        <div class="col-md-8">
            <textarea wire:model.defer="status" class="form-control" name="reason"></textarea>
        </div>
      </div>
        <div class="modal-footer">
          <button id="close" type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button id="savechanges"  type="button" class="btn btn-primary"  data-dismiss="modal"
          wire:click="adddeactivateAction('{{ $item['product-name'] }}','{{ $item['active-coverage'] }}','{{ $item['payer-id'] }}')">Save changes</button> 

        </div>
      </div>
    </div>
  </div>

  @section('myscript')
<script>
    
    $("select[name=status]").on("change",function (e) {
           
        let value = $(this).val();
            if(value =="Other"){
            $("[name=reason]").parents('.col-12').show();
            }else{
              $("[name=reason]").parents('.col-12').hide();
            }
           
           
      
        
});
 
</script>
@endsection
