<?php     

namespace App\Http\Controllers\Api;


use App\Claim;
use App\Coverage;
use App\Helpers;
use App\QR;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PHPUnit\TextUI\Help;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Http\Controllers\Controller;


class QRController extends Controller
{

    /**
     * @api {post} api/generateQR Generate QR
     * @apiVersion 1.0.0
     * @apiName Generate
     * @apiGroup QR
     *
     * @apiUse AuthHeaderToken
     *
     * @apiParam (Request) {String} type claim/test
     * @apiParam (Request) {String} uuid
     * @apiParam (Request) {String} from
     *
     * @apiSuccess (Response (200) ) {String} status success
     * @apiSuccess (Response (200) ) {Object} data QR
     *
     */

    public function generate(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:claim,test,referrer',
             'uuid' => 'string|required',
             'link' => 'string|required',
        ]);

        Helpers::flushExpiredQR();

        if ($request->type == 'claim') {
            $current = QR::where('action_uuid', $request->uuid)->first();
            if ($current)
                return ['status' => 'success', 'data' => $current];

            if($request->input('from') == 'claim'){
                $obj = Claim::whereUuid($request->uuid)->first();
                $obj = Coverage::whereUuid($obj->coverage_id)->first();

            }else
                $obj = Coverage::whereUuid($request->uuid)->first();
            return ['status' => 'success', 'data' => Helpers::generateTemporaryQR($obj)];

        }

        elseif($request->type == 'referrer'){
            $data = QrCode::size(345)
            ->style('dot')
            ->eye('square')
            ->margin(1)
            ->backgroundColor(247,247,247)
            ->format('png')
            ->merge('https://dt-insurance-dtdevtest-bucket.s3.ap-southeast-1.amazonaws.com/image/Image20230519143430.png', .5,true)
            ->errorCorrection('H')
            ->encoding('UTF-8')
            ->generate($request->link);
           
              $data1 = base64_encode($data);

            return response()->json([
                'status' => 'success',
                'data' => $data1,
            ]);
        }
    }
}
