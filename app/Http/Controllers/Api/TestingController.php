<?php     

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Encryption\Encrypter;
use App\Cronjobs;
class TestingController extends Controller
{

public function index()
{
/*
    $appKey = 'base64:Muk2HrX3OahhiGkpT9WFIpB/DqEeSKAk2aQbJ9gCpYo=';
    $decodedKey = base64_decode(substr($appKey, 7));
    $encrypter = new Encrypter($decodedKey, config('app.cipher'));
  //  $encryptedValue = "eyJpdiI6Ik1oTkV6Vy84eVZiTFd0ZVg2U3Z0eXc9PSIsInZhbHVlIjoiRzdNVHFXNy9nMEpvbmkyRXo4akxudW93cWsweE0wZFdTUGp4c29oSkVaQUZDWnAxYkkvaXBORmk5NzhoYW1uMTNkRFNCWDV2RW14NTM0OWRmb3dZSHlFU2JndUVCN3FXNlpUSzRJM01hSjFuYnE5TXJFdkx6OE1uMjNCZHV2T1c4VnhPSWxCQXFPa2tsQ3ZnaUl1MWJWaWdXblN0U0tFbml5K3A1OXdWMnNQVDdrdjhha2hKV0ZKbnp0SS9sMWtyQWx5MXdYUzFQTG5HcnlLMkpMQzU2UT09IiwibWFjIjoiMjVkODliY2ZjZTgyNmI4NjViMTRlYjBiMWUyZmQ1Zjc2ZDI4YzE3YTdmZjUxMGE3NTM3ZDdiNzc5ODQ1Mzk2ZCIsInRhZyI6IiJ9";
  //  return ['status' => 'success', 'data' =>$encrypter->decrypt($encryptedValue) ];

  $value='{"weight":70,"height":175,"smoke":0,"answers":[34,21,84,23,104,25,111,39,52,57,59,61]}';
  $encryptedValue = $encrypter->encrypt($value);
  return ['status' => 'success', 'data' =>$encrypter->encrypt($encryptedValue) ];
*/
$data= json_encode( ['job_name'=>'Testing Job','job_status'=>'success']);

return ['status' => 'success', 'data' => Cronjobs::insert(['job_name'=>'Testing Job','job_status'=>'success','job_data'=>$data])];

}



}
