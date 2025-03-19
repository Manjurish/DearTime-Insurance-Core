<?php     

namespace App\Http\Controllers\Api;


use App\HCPanel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HCPanelController extends Controller
{
	public function getList(Request $request)
	{
		$request->validate([
							   'latitude'  => 'required|numeric',
							   'longitude' => 'required|numeric',
						   ]);

		$lat = $request->input('latitude');
		$lng = $request->input('longitude');

		$clinics = HCPanel::select(DB::raw("*, ( 6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(" . $lng . ") ) + sin( radians(" . $lat . ") ) * sin( radians( latitude ) ) ) ) AS distance, IF(latitude = '',0,1) AS has_lat"))->whereType('General Practitioner')->orderBy("has_lat","DESC")->orderBy("distance")->paginate(20);
		return ['status' => 'success','data' => $clinics];
	}

	public function search(Request $request)
	{
		$request->validate(
			[
				'search_term' => 'required',
				'latitude'    => 'required|numeric',
				'longitude'   => 'required|numeric',
				//'type' => 'required',
			]);

		//$relationship = $request->get('relationship');
		$searchTerm = $request->get('search_term');
		$lat        = $request->get('latitude');
		$lng        = $request->get('longitude');

		if($request->get('ct') == 'hospital'){
			$clinics = HCPanel::select(DB::raw("*, ( 6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(" . $lng . ") ) + sin( radians(" . $lat . ") ) * sin( radians( latitude ) ) ) ) AS distance, IF(latitude = '',0,1) AS has_lat"))
							->whereType('Hospital')
							->where('name','like',"%{$searchTerm}%")
							->orderBy("has_lat","DESC")
							->orderBy("distance")
							->paginate(20);

		}else{
			
			$clinics = HCPanel::select(DB::raw("*, ( 6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(" . $lng . ") ) + sin( radians(" . $lat . ") ) * sin( radians( latitude ) ) ) ) AS distance, IF(latitude = '',0,1) AS has_lat"))
							->whereType('General Practitioner')
							->where('name','like',"%{$searchTerm}%")
							->orderBy("has_lat","DESC")
							->orderBy("distance")
							->paginate(20);
			 

		}

		/*$HCPanel = HCPanel::query()
			//->where('relationship',$relationship)
						  ->where('name','like',"%{$searchTerm}%")
			//->without('documents')
						  ->get();*/

		if($clinics->isNotEmpty()){
			return response()->json(
				[
					'status' => 'success',
					'data'   => [
						'clinics' => $clinics
					],
				]);
		}else{
			$ctParam = ($request->get('ct') == 'hospital') ? 'hospital' : 'clinic';
			return response()->json(
				[
					'status' => 'error',
					'message' => "This {$ctParam} is not under our panel list."
				],404);
		}
	}
}
