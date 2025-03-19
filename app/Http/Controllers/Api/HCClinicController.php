<?php     

namespace App\Http\Controllers\Api;

use App\HCPanel;
use App\Industry;
use App\IndustryJob;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;


class HCClinicController extends Controller
{
    public function getList(Request $request){
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'ct' => 'required|in:clinic,hospital'
        ]);
        $type = $request->ct == 'clinic' ? 'General Practitioner' : 'Hospital'; // Specialist Clinic TBD
        $lat = $request->input('latitude');
        $lng = $request->input('longitude');

        $clinics = HCPanel::select(DB::raw("*, ( 6371 * acos( cos( radians(".$lat.") ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(".$lng.") ) + sin( radians(".$lat.") ) * sin( radians( latitude ) ) ) ) AS distance, IF(latitude = '',0,1) AS has_lat"))->whereType($type)->orderBy("has_lat","DESC")->orderBy("distance")->paginate(20);

        return ['status' => 'success', 'data' => $clinics];
    }


}
