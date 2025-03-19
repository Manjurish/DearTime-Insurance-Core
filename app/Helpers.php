<?php      
 // ALL RIGHTS RESERVED ® DEARTIME BERHAD 
 // Last Updated: 24/09/2021 


namespace App;

use App\Helpers\Enum;
use App\Helpers\NextPage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Image;
use Illuminate\Support\Str;
use Config;
use File;

class Helpers
{
    public static function getLocale()
    {
        $locale = 'en';

        if (app()->getLocale() != 'en') {
            $locale = app()->getLocale();
        } else {
            $locale = auth()->check() ? auth()->user()->locale : (auth('api')->check() ? auth('api')->user()->locale : 'en');
        }
        return $locale;
    }

    public static function route($name, $params = [])
    {
        switch ($name) {
            case 'thanksgiving_page':
                $page = ('userpanel.Thanksgiving.index');
                break;
            case 'product_page':
                $page = ('userpanel.product.index');
                break;
            case 'login_page':
                $page = ('login');
                break;
            case 'register_page':
                $page = ('register');
                break;
            case 'profile_page':
                $page = ('userpanel.dashboard.profile');
                break;
            case 'corporate_profile_page':
                $page = ('userpanel.dashboard.profile');
                break;
            case 'group_packages_page':
                $page = ('userpanel.dashboard.profile');
                break;
            case 'dashboard_page':
                $page = ('userpanel.dashboard.main');
                break;
            case 'claim_page':
                $page = ('userpanel.claim.create');
                break;
            case 'nominee_page':
                $page = ('userpanel.Beneficiary.index');
                break;
            case 'payment_details_page':
                $page = ('userpanel.bank_card.index');
                break;
            case 'payment_details_account_page':
                $page = ('userpanel.bank_account.index');
                break;
            case 'policies_page':
                $page = ('userpanel.policies.index');
                break;
            case 'order_review_page':
                $page = ('userpanel.order.index');
                break;
            case 'corporate_order_review_page':
                $page = ('userpanel.order.index');
                break;
            case 'order_receipt_page':
                $page = ('userpanel.dashboard.main');
                break;
            case 'underwriting_page':
                $page = ('userpanel.MedicalSurvey.index');
                break;
            case 'claims_page':
                $page = ('userpanel.claim.index');
                break;
            case 'promoter_page':
                $page = ('userpanel.promote.index');
                break;
            case 'account_page':
                $page = ('userpanel.dashboard.main');
                break;
            case 'verification_page':
                $page = ('userpanel.Verification.index');
                break;
            case 'foreign_page':
                $page = ('userpanel.foreign.index');
                break;
            case 'pay_for_other_confirm':
                $page = ('userpanel.order.other');
                break;
        }
        if (empty($page))
            return route('admin.dashboard.main');

        return route($page, $params);
    }

    public static function getColor()
    {
        $hash = md5('color' . rand(1000, 9000));
        return '#' . hexdec(substr($hash, 0, 2)) . hexdec(substr($hash, 2, 2)) . hexdec(substr($hash, 4, 2));
    }

    public static function getMenuContents()
    {
        if (auth('internal_users')->check() && request()->is("ops*"))
            $menuJson = file_get_contents(base_path('resources/json/menu.json'));
        elseif (auth('partner')->check())
            $menuJson = file_get_contents(base_path('resources/json/menuPartner.json'));
        elseif (auth()->check() && auth()->user()->isIndividual())
            $menuJson = file_get_contents(base_path('resources/json/menuUser.json'));
        elseif (auth()->check())
            $menuJson = file_get_contents(base_path('resources/json/menuCorporate.json'));
        else
            $menuJson = file_get_contents(base_path('resources/json/menuGuest.json'));


        $menuData = json_decode($menuJson);
        return $menuData;
    }

    public static function getDeductibleFromCoverage($value)
    {
        if ($value == 0)
            return '0';
        if ($value == 1)
            return '500';
        if ($value == 2)
            return '1000';
        if ($value == 3)
            return '2000';
        if ($value == 4)
            return '5000';
        if ($value == 5)
            return '10000';
    }

    public static function prepPriceFor($premium, $annually)
    {
        return number_format($annually ? self::round_up($premium, 2) : self::round_up($premium * 0.085, 2), 2);
    }


    // public static function round_up( $value, $precision ) {
    //     $value = (float)$value;
    //     $precision = (int)$precision;
    //     if ($precision < 0) {
    //         $precision = 0;
    //     }
    //     $decPointPosition = strpos($value, '.');
    //     if ($decPointPosition === false) {
    //         return $value;
    //     }
    //     $floorValue = (float)substr(round($value,2), 0, $decPointPosition + $precision + 2);
    //     $followingDecimals = (int)substr(round($value,2), $decPointPosition + $precision + 2);
    //     if ($followingDecimals > 0) {
    //         $ceilValue = $floorValue + pow(10, -$precision);
    //     }
    //     else {
    //         $ceilValue = $floorValue;
    //     }
    //     return round($ceilValue, 2);
    // }

    public static function round_up( $value, $precision ) {
        $value = (float)$value;
        $numStr = (string)$value;
        $decimalPos = strrpos($numStr, '.');
        $precisions = strlen($numStr) - $decimalPos - 1;
       $precision = (int)2;
       if ($precision < 0) {
           $precision = 0;
       }
       $decPointPosition = strpos($value, '.');
       if ($decPointPosition === false) {
           return $value;
       }
       $fractionalPart = round($value - floor($value),3);
       $multiplier = pow(10, 4 - 1); // To extract the 2nd digit, use 10^1
       $digit = floor($fractionalPart * $multiplier) % 10;  
       if ($precisions>2){
       if ( $digit == 0  || $digit < 5){
       $floorValue = (float)substr(round($value,2), 0, $decPointPosition + $precision + 2) + pow(10, -$precision);
   }
   elseif ($digit > 4){
       $floorValue = $value ;
   }
   }
   else {
    $floorValue = $value ;
    }
    
        $ceilValue = $floorValue;
        return round($ceilValue, 2);
    }

    public static function flushExpiredQR()
    {
        QR::where('expiry', '<', Carbon::now())->delete();
    }

    public static function generateTemporaryQR($object,$expireSecond=60)
    {
        $qr = new QR();
        $qr->action_type = get_class($object);
        $qr->action_uuid = $object->uuid;
        $qr->expiry = Carbon::now()->addSeconds($expireSecond);
        $qr->save();
        return $qr;
    }

    public static function sendNotificationToAndroid($title, $body, $data, $token)
    {
        try {
            $url = 'https://fcm.googleapis.com/fcm/send';
            $msg = [
                'body' => $body,
                'title' => $title,
                'icon' => '',
                'sound' => '',
            ];
            $fields = array(
                'registration_ids' => (is_array($token) ? $token : [$token]),
                'notification' => $msg,
                'data' => [
                    'body'=>$body,
                    'title'=>$title,
                    'keys'=>$data
                ],
                "priority" => "high",
            );
            $fields = json_encode($fields);

            $headers = array(
//                'Authorization: key=' . "AAAAHJ2xYaw:APA91bFy3lAca6G9OY1AyvCUwCgYz8zuT5qYGSD-d5YlFSLe4Lsqv6GcE42XwmeQVbCAxhWD67CSx-XbdrLPd3O7Iryru_zA4oej1m20a7sn8f8LOt-bySWrhZ9qmFA8Tp5qJHeT4X9Q",
                //'Authorization: key=' . "AAAAfCc0i9U:APA91bFC0WZFGehUqR9A2t-2zMk7lpU0sLQQlD0dh9Y5L2C2C_EX6k5Z_AJASLhOTTOhkb0_OreK9bEccqc6bEzmYzm54p8Ejhrzn-oxgE-I9gDSXYhyjac1o6vXDUmF7TdAQl9_gfcx",
                'Authorization: key=' . "AAAA_3zic5s:APA91bFX9Y1_Rt9PTWvQdmFSxB4jxkBA34jVvjYeAeBKR_p2ePq5erAHc1pmngpUiWdghWCQ5-CJFXu5MhNS-CJPaUWOI-8lnjeywW1amuJTEEbXrZ3gzoerA9MvdoK3y77xPhvmZb23",
                'Content-Type: application/json'
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

            $result = curl_exec($ch);
            curl_close($ch);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function sendNotificationToIOS($title, $body, $data, $token)
    {
        //https://stackoverflow.com/questions/41628335/send-ios-push-notification-in-php-with-p8-file#:~:text=php%20file%20you%20need%20to,you%20should%20receive%20push%20notification.
        try {
            // Path to the .p8 file downloaded from apple
            // see: https://developer.apple.com/documentation/usernotifications/setting_up_a_remote_notification_server/establishing_a_token-based_connection_to_apns#2943371
            $keyfile = resource_path('certs/AuthKey_79L495WLBV.p8');

            // Team ID (From the Membership section of the ios developer website)
            // see: https://developer.apple.com/account/
            $teamid = '5QBCAC7SXM';

            // The "Key ID" that is provided when you generate a new key
            $keyid = '79L495WLBV';

            // The Bundle ID of the app you want to send the notification to
            $bundleid = env('IOS_APN_BUNDLE_ID');

            // Device token (registration id)
            //$token = 'e32dc01cfcf710ce06446359f061f98747d131977db6c4bd31aff2ca5185bd77';

            $url = 'https://api.push.apple.com';

            //$message = '{"aps":{"alert":"Hi Kishore How r u!","sound":"default"}}';

            $bodies['aps'] = array(
                'alert' => array(
                    'title' => $title,
                    'body' => $body,
                    'extra' => $data,
                ),
                'badge' => 0,
                'sound' => 'default',
            );
            $message = json_encode($bodies); // Encode the payload as JSON

            $key = openssl_pkey_get_private('file://'.$keyfile);

            $header = ['alg'=>'ES256','kid'=>$keyid];
            $claims = ['iss'=>$teamid,'iat'=>time()];

            $header_encoded = self::base64($header);
            $claims_encoded = self::base64($claims);

            $signature = '';
            openssl_sign($header_encoded . '.' . $claims_encoded, $signature, $key, 'sha256');
            $jwt = $header_encoded . '.' . $claims_encoded . '.' . base64_encode($signature);

            // only needed for PHP prior to 5.5.24
            if (!defined('CURL_HTTP_VERSION_2_0')) {
                define('CURL_HTTP_VERSION_2_0', 3);
            }

            $http2ch = curl_init();
            curl_setopt_array($http2ch, array(
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
                CURLOPT_URL => "$url/3/device/$token",
                CURLOPT_PORT => 443,
                CURLOPT_HTTPHEADER => array(
                "apns-topic: {$bundleid}",
                "authorization: bearer $jwt"
                ),
                CURLOPT_POST => TRUE,
                CURLOPT_POSTFIELDS => $message,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HEADER => 1
            ));
            $result = curl_exec($http2ch);
            return true;
        } catch (\Exception $e) {
            dd($e);
            return false;
        }
       /*try {
            $deviceToken = $token;
            $passphrase = 'p';
            $pemfilename = resource_path('certs/pushcert.pem');

            $bodies['aps'] = array(
                'alert' => array(
                    'title' => $title,
                    'body' => $body,
                    'extra' => $data,
                ),
                'badge' => 0,
                'sound' => 'default',
            );
            $ctx = stream_context_create();
            stream_context_set_option($ctx, 'ssl', 'local_cert', $pemfilename);
            stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
            $fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx); // Open a connection to the APNS server
            if (!$fp)
                return false;
            $payload = json_encode($bodies); // Encode the payload as JSON
            $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload . chr(5) . pack('n', 1) . chr(10);; // Build the binary notification
            $result = fwrite($fp, $msg, strlen($msg)); // Send it to the server
            fclose($fp); // Close the connection to the server
            if (!$result)
                return false;
            else
                return true;
        } catch (\Exception $e) {
            dd($e);
            return false;
        }*/
    }

    public static function crateDocumentFromUploadedFile($file, $model, $type = null, $temp = false)
    {

        if ($type == null)
            $type = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);


//        $path = $file->store('documents/' . Carbon::now()->format('Y-m-d'));
        $path = Storage::disk('s3')->put('documents/' . Carbon::now()->format('Y-m-d'), ($file));
        $storePath = $path;

//        create Thumbnails
        $thumb_path = '';
        if (substr($file->getMimeType(), 0, 5) == 'image' && !$temp) {
            $thumb_path = storage_path('app/' . 'thumb.' . $file->getClientOriginalExtension());

            $img = Image::make($file)->resize(500, null, function ($constraint) {
                $constraint->aspectRatio();
            })->save($thumb_path);


            $th_p = Storage::disk('s3')->put('thumb_documents/' . Carbon::now()->format('Y-m-d'), (new UploadedFile($thumb_path, $file->getClientOriginalName(), $file->getClientOriginalExtension())));
            \Illuminate\Support\Facades\File::delete($thumb_path);
            $thumb_path = $th_p;

        }
        $doc = new Document();

//        $doc->documentable_id = $model->id;
//        $doc->documentable_type = get_class($model);
        $doc->type = $type;
        $doc->path = $storePath;
        $doc->thumb_path = $thumb_path;
        $doc->name = $file->getClientOriginalName();
        $doc->ext = $file->getClientOriginalExtension();
        $doc->url = (string)Str::uuid();
        $doc->size = $file->getSize();
        $doc->created_by = auth()->id() ?? auth('api')->id() ?? auth('internal_users')->id();

        if (!$temp)
            $model->documents()->save($doc);
        else {
            $doc->documentable_id = 0;
            $doc->documentable_type = $temp;
            $doc->save();
        }
        return $doc;
    }

    public static function createDocumentFromFile($file, $model, $type = null, $temp = false,$mime='pdf'){
        $address = 'documents/' . Carbon::now()->format('Y-m-d').'/'.Str::random(36).'.'.$mime;
        Storage::disk('s3')->put($address, (string)$file);
        $thumb_path = '';
        $doc = new Document();
        $doc->type = $type;
        $doc->path = $address;
        $doc->thumb_path = $thumb_path;
        $doc->name = $type.'.'.$mime;
        $doc->ext = $mime;
        $doc->url = (string)Str::uuid();
        $doc->size = Storage::disk('s3')->size($address);
        $doc->created_by = auth()->id() ?? auth('api')->id() ?? auth('internal_users')->id();

        if (!$temp)
            $model->documents()->save($doc);
        else {
            $doc->documentable_id = 0;
            $doc->documentable_type = $temp;
            $doc->save();
        }
        return $doc;
    }

    public static function getAccessControlMethod()
    {
        return
            function ($action, $row) {
                return self::hasPermission($action);
            };
    }

    public static function hasPermission($route)
    {

        if ($route == 'login' || $route == 'register')
            return true;
        if (Str::contains($route, 'userpanel.groupPackage')) {

            $user = auth()->user();
            if ($user->isCorporate()) {
                $user = $user->profile;
                if ($user->isClinic() || $user->isHospital())
                    return false;
            }
        }
        if (Str::contains($route, 'userpanel.clinic')) {
            $user = auth()->user();
            if ($user->isCorporate()) {
                $user = $user->profile;
                if ($user->isClinic())
                    return true;
            }
            return false;
        }
        if (Str::contains($route, 'userpanel.hospital')) {
            $user = auth()->user();
            if ($user->isCorporate()) {
                $user = $user->profile;
                if ($user->isHospital())
                    return true;
            }
            return false;
        }

        if (in_array($route, config('static.allowed_routes')) || Str::contains($route, 'userpanel') || Str::contains($route, 'partner'))
            return true;

        if (\Auth::guard('internal_users')->check()) {
            $user = \Auth::guard('internal_users')->user();

            try {
                if (empty($route) || $user->hasRole('SuperAdmin') || $user->can($route)) {
                    return true;
                }
            } catch (\Exception $e) {
                if (config('app.debug'))
                    throw new \Exception($e->getMessage());
                else
                    abort(500);
            }
        }
        return false;
    }

    public static function applClasses()
    {
        $data = config('custom.custom');

        $layoutClasses = [
            'theme' => $data['theme'],
            'sidebarCollapsed' => $data['sidebarCollapsed'],
            'navbarColor' => $data['navbarColor'],
            'menuType' => $data['menuType'],
            'navbarType' => $data['navbarType'],
            'footerType' => $data['footerType'],
            'sidebarClass' => 'menu-expanded',
            'bodyClass' => $data['bodyClass'],
            'pageHeader' => $data['pageHeader'],
            'blankPage' => $data['blankPage'],
            'blankPageClass' => '',
            'contentLayout' => $data['contentLayout'],
            'sidebarPositionClass' => '',
            'contentsidebarClass' => '',
            'mainLayoutType' => $data['mainLayoutType'],
        ];


        //Theme
        if ($layoutClasses['theme'] == 'dark')
            $layoutClasses['theme'] = "dark-layout";
        elseif ($layoutClasses['theme'] == 'semi-dark')
            $layoutClasses['theme'] = "semi-dark-layout";
        else
            $layoutClasses['theme'] = "light";

        //menu Type
        switch ($layoutClasses['menuType']) {
            case "static":
                $layoutClasses['menuType'] = "menu-static";
                break;
            default:
                $layoutClasses['menuType'] = "menu-fixed";
        }


        //navbar
        switch ($layoutClasses['navbarType']) {
            case "static":
                $layoutClasses['navbarType'] = "navbar-static";
                $layoutClasses['navbarClass'] = "navbar-static-top";
                break;
            case "sticky":
                $layoutClasses['navbarType'] = "navbar-sticky";
                $layoutClasses['navbarClass'] = "fixed-top";
                break;
            case "hidden":
                $layoutClasses['navbarType'] = "navbar-hidden";
                break;
            default:
                $layoutClasses['navbarType'] = "navbar-floating";
                $layoutClasses['navbarClass'] = "floating-nav";
        }

        // sidebar Collapsed
        if ($layoutClasses['sidebarCollapsed'] == 'true')
            $layoutClasses['sidebarClass'] = "menu-collapsed";

        // sidebar Collapsed
        if ($layoutClasses['blankPage'] == 'true')
            $layoutClasses['blankPageClass'] = "blank-page";

        //footer
        switch ($layoutClasses['footerType']) {
            case "sticky":
                $layoutClasses['footerType'] = "fixed-footer";
                break;
            case "hidden":
                $layoutClasses['footerType'] = "footer-hidden";
                break;
            default:
                $layoutClasses['footerType'] = "footer-static";
        }

        //Cotntent Sidebar
        switch ($layoutClasses['contentLayout']) {
            case "content-left-sidebar":
                $layoutClasses['sidebarPositionClass'] = "sidebar-left";
                $layoutClasses['contentsidebarClass'] = "content-right";
                break;
            case "content-right-sidebar":
                $layoutClasses['sidebarPositionClass'] = "sidebar-right";
                $layoutClasses['contentsidebarClass'] = "content-left";
                break;
            case "content-detached-left-sidebar":
                $layoutClasses['sidebarPositionClass'] = "sidebar-detached sidebar-left";
                $layoutClasses['contentsidebarClass'] = "content-detached content-right";
                break;
            case "content-detached-right-sidebar":
                $layoutClasses['sidebarPositionClass'] = "sidebar-detached sidebar-right";
                $layoutClasses['contentsidebarClass'] = "content-detached content-left";
                break;
            default:
                $layoutClasses['sidebarPositionClass'] = "";
                $layoutClasses['contentsidebarClass'] = "";
        }

        return $layoutClasses;
    }

    public static function updatePageConfig($pageConfigs)
    {
        $demo = 'custom';
        if (isset($pageConfigs)) {
            if (count($pageConfigs) > 0) {
                foreach ($pageConfigs as $config => $val) {
                    Config::set('custom.' . $demo . '.' . $config, $val);
                }
            }
        }
    }

    public static function isFromApp()
    {
        return request()->hasHeader('DT-App-Version');
    }

    public static function curlPost($url, $data = NULL, $headers = NULL)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        // TODO two lines below should be deleted on the server
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);

        if (curl_error($ch)) {
            trigger_error('Curl Error:' . curl_error($ch));
        }

        curl_close($ch);
        return $response;
    }

    public static function paginate($items, $perPage = 5, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);
    }

    public static function setModal($message, $buttons)
    {
        return ['messages' => $message, 'buttons' => $buttons];
    }

    public static function calcThanksgivingDiscount($thanksgiving, $total)
    
    {
        $discount = 0;
        if (!empty($thanksgiving)) {
            $selfThanksgiving = $thanksgiving->where('type', Enum::THANKSGIVING_TYPE_SELF)->first();
            $discountPercent = ($selfThanksgiving->percentage ?? 0) / config('static.thanksgiving_percent');
            //Dev-523 Rounding-Issue
            if($discountPercent > 0){
            $dis = ($total * $discountPercent * 100) / 100;

            // return $dis;
            $discount = $total - $dis;

        // $discount = round($disco,2);

            }else{
                $discount = 0;
            }
            //Number format fix
            //  $discount = number_format(($total * $discountPercent * 100) / 100, 2);
        }
        return $discount;
    }

    public static function response($status, $actionType, $modal = null, $next_page = null, $toast = null,$data=null)
    {
        $res = [
            "status" => $status,
            "action_type" => $actionType,
            "modal" => $modal,
            "next_page" => $next_page,
            "toast" => $toast,
            "data"=>[
                "user"=>$data['user'] ?? '',
                "is_foreign"=>$data['is_foreign'] ?? '',
                "charity_eligible"=>$data['charity_eligible'] ?? '',
            ],
            "config"=>$data['config'] ?? '',
        ];
        return json_encode($res);
    }

    public static function proRate($price,$days,$restDays): float
    {
        return round($price * ($restDays/$days),2);
    }

    public static function getDocs($type){

    	if($type == Enum::PRODUCT_NAME_DEATH || $type == Enum::PRODUCT_NAME_ACCIDENT){
        
        
			$deathAllFiles_2  = array_diff(scandir(resource_path('documents/claims/common')),array("..","."));
            natsort($deathAllFiles_2);
            $death_docs = [];

            foreach ($deathAllFiles_2 as $filename) {
			array_push($death_docs,
					   [
					   	'name' => $filename,
						'link' =>  encrypt('documents/claims/common/' . $filename)
						//'link' => null
					   ]
			);
        }
        
        
			$deathAllFiles  = array_diff(scandir(resource_path('documents/claims/death')),array("..","."));
			natsort($deathAllFiles);            
            

			foreach ($deathAllFiles as $filename) {
				array_push($death_docs,
						   [
							   'name' => $filename,
							   'link' => encrypt('documents/claims/death/'. $filename)
						   ]);
			}

           array_push($death_docs,
            [
                'name' => __('mobile.supporting_documents'),
                //'link' => resource_path('documents/claims/ci/' . $filename)
                'link' => NULL
            ]);
			return $death_docs;
		}

        if($type == Enum::PRODUCT_NAME_MEDICAL){


			$medAllFiles_2  = array_diff(scandir(resource_path('documents/claims/common')),array("..","."));
            natsort($medAllFiles_2);
            $med_docs = [];

            foreach ($medAllFiles_2 as $filename) {
			array_push($med_docs,
					   [
					   	'name' => $filename,
						'link' =>  encrypt('documents/claims/common/' . $filename)
						//'link' => null
					   ]
			);
            }
        
        

			$medAllFiles  = array_diff(scandir(resource_path('documents/claims/medical')),array("..","."));
			natsort($medAllFiles);

			foreach ($medAllFiles as $filename) {
				array_push($med_docs,
						   [
							   'name' => $filename,
							   //'link' => resource_path('documents/claims/ci/' . $filename)
							   'link' => encrypt('documents/claims/medical/' . $filename)
						   ]);
			}

            array_push($med_docs,
            [
                'name' => __('mobile.supporting_documents'),
                //'link' => resource_path('documents/claims/ci/' . $filename)
                'link' => NULL
            ]);

			return $med_docs;
		}

		if($type == Enum::PRODUCT_NAME_DISABILITY){
        
        	$disabilityAllFiles_2  = array_diff(scandir(resource_path('documents/claims/common')),array("..","."));
            natsort($disabilityAllFiles_2);
            $disability_docs = [];

            foreach ($disabilityAllFiles_2 as $filename) {
			array_push($disability_docs,
					   [
					   	'name' => $filename,
						'link' =>  encrypt('documents/claims/common/' . $filename)
						//'link' => null
					   ]
			);
        }
        
        
			$disabilityAllFiles  = array_diff(scandir(resource_path('documents/claims/disability')),array("..","."));
			natsort($disabilityAllFiles);

			foreach ($disabilityAllFiles as $filename) {
				array_push($disability_docs,
						   [
							   'name' => $filename,
							   'link' => encrypt('documents/claims/disability/'. $filename)
						   ]);
			}

            array_push($disability_docs,
            [
                'name' => __('mobile.supporting_documents'),
                //'link' => resource_path('documents/claims/ci/' . $filename)
                'link' => NULL
            ]);
            
			return $disability_docs;
		}

		if($type == Enum::PRODUCT_NAME_CRITICAL_ILLNESS){
        
                
        	$ciAllFiles_2  = array_diff(scandir(resource_path('documents/claims/common')),array("..","."));
            natsort($ciAllFiles_2);
            $ci_docs = [];

            foreach ($ciAllFiles_2 as $filename) {
			array_push($ci_docs,
					   [
					   	'name' => $filename,
						'link' =>  encrypt('documents/claims/common/' . $filename)
						//'link' => null
					   ]
			);
        }
        
        
        
			$ciAllFiles  = array_diff(scandir(resource_path('documents/claims/ci')),array("..","."));
			natsort($ciAllFiles);

			foreach ($ciAllFiles as $filename) {
				array_push($ci_docs,
						   [
							   'name' => $filename,
							   //'link' => resource_path('documents/claims/ci/' . $filename)
							   'link' => encrypt('documents/claims/ci/' . $filename)
						   ]);
			}

             array_push($ci_docs,
            [
                'name' => __('mobile.supporting_documents'),
                //'link' => resource_path('documents/claims/ci/' . $filename)
                'link' => NULL
            ]);

			return $ci_docs;
		}

	}

/***************************** Dashboard FAQ Documents  ********************/

    public static function getDocs_faqs(){   
        $user = Auth::user();     
            
        $faqsAllFiles  = array_diff(scandir(resource_path('documents/dashboard_faqs')),array("..","."));
        natsort($faqsAllFiles);
        $faq_docs = [];

        foreach ($faqsAllFiles as $filename) {
        array_push($faq_docs,
                [
                    'title' => $filename,
                    'link' => route('doc.view',['app_view' => Helpers::isFromApp() ? '1' : '2','coverage' => $options['coverage'] ?? 2000,'term' => 'annually', 'type' => 'dash_faq', 'title' => $filename,'uuid' => encrypt($user->uuid)]),
                    'type' => "pdf"
                ]
        );
    }

        return $faq_docs;

    }

/***************************** Dashboard FAQ Documents  ********************/

	public static function removeJavaScript($value)
	{
		return preg_replace('#<script(.*?)>(.*?)</script>#is', '', $value);
	}

    public static function updatePremiumOnOccupation($profile) {
        if(!$profile->coverages_owner())    return false;
        
        $coverages_owner    =   $profile->coverages_owner()->whereIn('status',['unpaid','increase-unpaid','grace-unpaid','grace-increase-unpaid','decrease-unpaid'])->get() ?? [];
        
        foreach ($coverages_owner as $coverage) {
            if($coverage->payer->corporate_type == 'payorcorporate')
                continue;


            $occ_loading=null;
            
            $product        =   $coverage->product;
            $price          =   $product->getPrice($profile, $coverage->coverage,$occ_loading,null,$coverage->deductible)[0];
            $without_loading_price =  $product->getPrice($profile, $coverage->coverage,$occ_loading,null,$coverage->deductible)[3];
            $without_loading = Helpers::round_up($without_loading_price, 2);
            $annually       =   Helpers::round_up($price, 2);
            $monthly        =   Helpers::round_up($price * 0.085, 2);
            $coverage->payment_monthly  =    $monthly;  
            $coverage->payment_annually =    $annually;
            $coverage->payment_without_loading = $coverage->payment_term =='monthly'?(Helpers::round_up($without_loading* 0.085, 2)): $without_loading;
            $coverage->full_premium = $coverage->payment_term =='monthly'?(Helpers::round_up($annually* 0.085, 2)): $annually;

            $coverage->save();             
        }
    }

    public static function base64($data) {
        return rtrim(strtr(base64_encode(json_encode($data)), '+/', '-_'), '=');
    }

}


