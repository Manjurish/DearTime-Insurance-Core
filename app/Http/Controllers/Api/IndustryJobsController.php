<?php     

namespace App\Http\Controllers\Api;

use App\Helpers\Enum;
use App\Http\Controllers\Controller;
use App\Industry;
use App\IndustryJob;
use Illuminate\Http\Request;


class IndustryJobsController extends Controller
{

    /**
     * @api {post} api/getIndustryJobsList Industry Jobs List
     * @apiVersion 1.0.0
     * @apiName tIndustryJobsList
     * @apiGroup IndustryJobs
     *
     * @apiDescription It gets all industry job by gender and country that country default is Malaysian
     * @apiUse AuthHeaderToken
     *
     * @apiParam (Request) {String} gender male/female
     * @apiParam (Request) {String} nationality country uuid
     *
     * @apiSuccess (Response (200) ) {String} status success
     * @apiSuccess (Response (200) ) {Object} data industry jobs
     *
     */
	public function getList(Request $request)
	{
		if($request->initial){
			return Industry::all();
		}

		if(!$request->industry){
			$noIncomeIds = Industry::query()->whereIn('name',['Student','Housewife','Househusband','Retiree / Pensioner'])->pluck('id');
			return ['status' => 'success','data' =>
				[
					'industry'    => Industry::all(),
					'no_income_ids' => $noIncomeIds
				]
			];
		}

		$gender = $request->input('gender');
		if(!in_array($gender,[Enum::INDIVIDUAL_GENDER_MALE,Enum::INDIVIDUAL_GENDER_FEMALE])){
			if(auth()->check()){
				$user   = auth()->user();
				$gender = $user->isIndividual() ? ($user->profile->gender ?? NULL) : NULL;
			}elseif(auth('api')->check()){
				$user   = auth('api')->user();
				$gender = $user->isIndividual() ? ($user->profile->gender ?? NULL) : NULL;
			}
		}

		/*if(empty($gender)){
			$gender = Enum::INDIVIDUAL_GENDER_MALE;
		}*/

		if(is_numeric($request->industry)){
			$industry = $request->industry;
		}else{
			$industry = Industry::whereUuid($request->industry)->first()->id ?? 0;
		}

		$data = IndustryJob::where('industry_id',$industry);

		$data = $data->where(function ($q) use ($gender) {
			if(!empty($gender)){
				return $q->where('gender',$gender)
						 ->orWhereNull('gender')
						 ->orWhere('gender','');
			}else{
				return $q->whereIn('gender',[Enum::INDIVIDUAL_GENDER_MALE,Enum::INDIVIDUAL_GENDER_FEMALE,'']);
			}
		});

		//check for nationalities not Malaysia
		/*$country = Country::whereUuid($request->input('nationality'))->first();
		if(!empty($country)){
			if($country->is_allowed != '1'){
				$data = $data->whereNull('id');
			}elseif($country->nationality != 'Malaysian'){
				$data = $data->where("death","0")->where("Accident","1")->where("Medical","1")->where("TPD","1");
			}
		}*/

		$data = $data->get();

		return ['status' => 'success','data' => $data];

		// return $ij->load('occupations');

	}
}
