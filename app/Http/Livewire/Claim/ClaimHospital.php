<?php     

namespace App\Http\Livewire\Claim;

use App\Claim;
use App\Coverage;
use App\Helpers\Enum;
use App\Helpers\Modal;
use App\Individual;
use Livewire\Component;

class ClaimHospital extends Component
{
	//use WithFileUploads;

	public $nric;
	public $mobile;
	public $ref_no;
	public $myCoverages;
	public $beneficiaryCoverages;
	public $docs;
	public $coverage;
	public $profile;
	public $claim;
	public $file;

	public function render()
	{
		return view('livewire.claim.hospital');
	}

	public function authorize($type = 'nric')
	{

		
		if($type == 'nric'){
			$this->validate([
								'nric'   => 'required|numeric|exists:individuals',
								'mobile' => 'required|numeric|exists:individuals',
							]);

			$this->profile = Individual::where('nric',$this->nric)->where('mobile',$this->mobile)->first();

			$this->myCoverages = collect($this->profile->activeCoverages(Enum::COVERAGE_OWNER_TYPE_MYSELF))->whereNotIn('product_name',[Enum::PRODUCT_NAME_MEDICAL]);

			$this->beneficiaryCoverages = $this->profile->coverages_beneficiary()->whereNotIn('product_name',[Enum::PRODUCT_NAME_MEDICAL])->active()->get();
		}else{
			$this->validate([
								'ref_no' => 'required|exists:claims',
							]);

			$claim = Claim::where('ref_no',$this->ref_no)->first();
			// print_r(auth()->id());
			// var_dump($claim->coverage->uuid) ;
			// var_dump($claim->profile->uuid) ;
			// dd($claim->coverage);
			//
			// select * from claims where ref_no = '342042';
			// SELECT * FROM `Coverages` where id = 32 ORDER BY `id`  DESC;
				
			if(!empty($claim->panel_id) && $claim->panel_id != auth()->id()){
				Modal::error($this,__('web/messages.claim_take_by_another_hospital'));
				$this->skipRender();
				return;
			}else{
				$claim->update(['panel_id' => auth()->id()]);
				//return redirect()->route('userpanel.hospital.claim.detail',['cuuid' => $claim->coverage->uuid,'puuid' => $claim->profile->uuid]);
				//Hospital Panel -> Search by Claim reference number is broken
				return redirect()->route('userpanel.hospital.claim.detail',['uuid' => $claim->uuid]);
			}
		}
	}

	public function gotoDetail($uuid)
	{
		$coverage = Coverage::where('uuid',$uuid)->firstOrFail();

		$claim = Claim::query()->where('coverage_id',$coverage->id)->where('individual_id',$this->profile->id)->first();

		if(empty($claim)){
			$claim = Claim::create([
							  'individual_id' => $this->profile->id,
							  'coverage_id'   => $coverage->id,
							  'owner_id'      => $coverage->owner_id,
							  'status'        => Enum::CLAIM_STATUS_DRAFT,
							  'created_by'    => auth()->id(),
							  'panel_id'      => auth()->id(),
						  ]);
		}else{
			if(empty($claim->panel_id) && $claim->panel_id != auth()->id()){
				Modal::error($this,__('web/messages.claim_take_by_another_hospital'));
				$this->skipRender();
				return;
			}else{
				$claim->update(['panel_id' => auth()->id()]);
			}
		}

		return redirect()->route('userpanel.hospital.claim.detail',['uuid' => $claim->uuid]);
	}
}