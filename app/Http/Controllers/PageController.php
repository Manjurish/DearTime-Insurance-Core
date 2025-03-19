<?php     

namespace App\Http\Controllers;

use App\Notification;
use Illuminate\Http\Request;

class PageController extends Controller
{

    public function index($page,Request $request)
    {
        $pages = [
            'importantNotice' =>    [
                'name'      =>  __('mobile.important_notice'),
                'view'      =>  'pages.importantNotice',
                'view_bm'   =>  'pages.importantNotice',
                'view_ch'   =>  'pages.importantNotice'
            ],
            'declaration' =>        [
                'name'      =>  __('mobile.declaration'),
                'view'      =>  'pages.declaration',
                'view_bm'   =>  'pages.declaration',
                'view_ch'   =>  'pages.declaration'
            ],
            'termsOfUse' =>         [
                'name'      =>  __('mobile.term_of_use'),
                'view'      =>  'pages.termsOfUse',
                'view_bm'   =>  'pages.termsOfUse',
                'view_ch'   =>  'pages.termsOfUse'
            ],
            'privacyStatement' =>   [
                'name'      =>  __('mobile.privacy_statement'),
                'view'      =>  'pages.privacyStatement',
                'view_bm'   =>  'pages.privacyStatement',
                'view_ch'   =>  'pages.privacyStatement'
            ],
            'CreditCardTerms' =>    [
                'name'      =>  __('mobile.terms_conditions_credit'),
                'view'      =>  'pages.creditCardTerms',
                'view_bm'   =>  'pages.creditCardTerms',
                'view_ch'   =>  'pages.creditCardTerms'
            ],
            'order_declaration' =>  [
                'name'      =>  __('mobile.declaration'),
                'view'      =>  'pages.order_declaration',
                'view_bm'   =>  'pages.order_declaration',
                'view_ch'   =>  'pages.order_declaration'
            ],
            'order_pdpa' =>         [
                'name'      =>  __('mobile.pdpa'),
                'view'      =>  'pages.order_pdpa',
                'view_bm'   =>  'pages.order_pdpa',
                'view_ch'   =>  'pages.order_pdpa'
            ],
            'PersonalDataConsent' =>[
                'name'      =>  __('mobile.personal_data_consent'),
                'view'      =>  'pages.PersonalDataConsent',
                'view_bm'   =>  'pages.PersonalDataConsent',
                'view_ch'   =>  'pages.PersonalDataConsent'
            ],
            'privacy-policy' =>[
                'name'      =>  __('mobile.personal_data_consent'),
                'view'      =>  'pages.privacyPolicy',
                'view_bm'   =>  'pages.privacyPolicy',
                'view_ch'   =>  'pages.privacyPolicy'
            ],
            'personal-data-protection' =>[
                'name'      =>  __('mobile.personal_data_consent'),
                'view'      =>  'pages.personalDataProtection',
                'view_bm'   =>  'pages.personalDataProtection',
                'view_ch'   =>  'pages.personalDataProtection'
            ],
        ];
        if(empty($pages[$page])) {
            abort(404);
        }
        $page = $pages[$page];
        $index = 'view';
        $locale = app()->getLocale();

        if($locale != 'en') {
            $index = 'view_' . $locale;
        }

        //dd("TESTING ".$page[$index]);

        $view = view($page[$index])->render();

        if($request->input('mobile') == '1') {
            return view('mobile_page',
                [
                    'title'     => $page['name'],
                    'content'   => $view,
                ]);
        }else {
            return view('mobile_page',
                [
                    'title'     => $page['name'],
                    'content'   => $view,
                ]);
        }
    }

    public function showNotification($uuid)
    {
        $notification = Notification::whereUuid($uuid)->first();

        if(empty($notification)) {
            abort(404);
        }

        if($notification->auto_read == '1'){
            $notification->is_read = '1';
            $notification->save();
        }
        $from_web = \request()->has('wb');
        return view('notification',compact('notification','from_web'));

    }
}
