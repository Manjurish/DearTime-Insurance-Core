<?php     

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//

Route::namespace('Api')->middleware(['setLang'])->group(function () {

    Route::group(['middleware' => ['auth:api']], function () {

        Route::post('set-notification-token', 'AuthController@setNotificationToken');

//        Route::post('hospital/checkSelfie1', '\App\Http\Controllers\User\HospitalController@checkSelfie')->name('hospital.checkSelfie');

        //Dev-494 - Error message after key in 6 digit
        Route::post('pin-code', 'AuthController@postPin');
        
        Route::get('pin-code', 'AuthController@getPin');

        Route::post('profile', 'AuthController@profile');
        Route::get('profile', 'AuthController@profile');

        Route::post('change-avatar', 'AuthController@changeAvatar');
        Route::post('change-email', 'AuthController@changeEmail');
        Route::post('change-password', 'AuthController@changePassword');
        Route::post('change-mobile', 'AuthController@changeMobile');

        Route::post('getIndustryJobsList', 'IndustryJobsController@getList');

        Route::post('promote', 'PromoterController@add');
        Route::get('promote', 'PromoterController@list');
        Route::get('payments-received', 'PromoterController@paymentsReceived');

        Route::post('getProducts', 'ProductController@index');
        Route::get('terminationProducts', 'ProductController@send_email');
        
        Route::post('remove-coverage', 'ProductController@removeCoverage');
        Route::post('buy-for-others', 'ProductController@buyForOther');
        Route::post('can-pay-for-others', 'ProductController@canPayForOthers');

        Route::post('updateProfile', 'UserController@updateProfile');
        Route::post('indvupdateProfile','IndividualController@updateProfile');


        Route::get('initialBankCard', 'BankAccountController@banksList');
        Route::post('bankAccounts', 'BankAccountController@add');

        Route::get('paymentHistory', 'OrderController@getHistory');
        Route::post('scanClaim', 'ClaimController@scan');

        Route::get('get-time-tube', 'UserController@getTimeTube');
        
        Route::post('set-chat', 'UserController@setChat');Route::get('sopsummary','SpoInsuranceController@sopsummary');
        Route::post('applyspo','SpoInsuranceController@apply');

        Route::get('/tools/initPostRegisterIndividual', 'ToolsController@initPostRegisterIndividual');
        Route::get('/user', 'AuthController@getUser')->name('api.getUser');

        Route::get('/tools/initPostRegisterIndividual', 'ToolsController@initPostRegisterIndividual');
        Route::get('/user', 'AuthController@getUser')->name('api.getUser');

        Route::group(['middleware' => ['individual']], function () {


            Route::post('underwritings', 'UnderwritingController@get');
            // Sio
            Route::post('underwritingssio', 'UnderwritingSioController@test');
//            Route::post('update', 'IndividualController@update');
            Route::post('applyCharity', 'CharityApplicantController@apply');
            Route::get('kycVerification', 'CustomerVerificationController@get');
            Route::post('kycVerification', 'CustomerVerificationController@set');

            //dev-499 - unable to register claim , stuck in selfi verify 
            Route::post('verify', 'CustomerVerificationController@verify');


            Route::get('beneficiary', 'BeneficiaryController@get');
            Route::post('beneficiary', 'BeneficiaryController@set');
			Route::post('beneficiary/add','BeneficiaryController@add')->name('Beneficiary.add');

            Route::get('updatethanksgiving','ThanksgivingController@update');

            Route::post('generateQR', 'QRController@generate')->name('api.generateQR');
            
            Route::post('generateCode','ReferralController@generateCode');
            Route::get('promoters', 'ReferralController@promoters');
            Route::get('countref','ReferralController@countref');
            Route::post('monthly', 'ReferralController@monthly');
            Route::post('yearly', 'ReferralController@yearly');
            Route::get('termscondition','ReferralController@termscondition');

            Route::get('yearlypdf', 'ReferralController@yearlypdf');
            Route::get('monthlypdf', 'ReferralController@monthlypdf');

            Route::get('paymentterm', 'PolicyController@paymentterm');
            
            Route::post('paymentterm_payer', 'PolicyController@paymentterm_payer');
            
            Route::post('payor_reject_owner', 'PolicyController@payor_reject_owner');

            Route::get('householdmember','SpoInsuranceController@getmember');
            Route::post('householdmemberadd','SpoInsuranceController@addmember');
            Route::post('memberdocupload','SpoInsuranceController@memberdocupload');
            Route::post('check','SpoInsuranceController@check');
            Route::get('deletespo','SpoInsuranceController@deletespo');
            Route::post('deletemember','SpoInsuranceController@deletemember');
            Route::post('/checkuserspo', 'UserController@checkuserspo')->name('api.checkuserspo');
            Route::get('deletepayoroffer','SpoInsuranceController@deletepayoroffer');




			Route::get('child', 'ChildController@get');
            Route::post('child', 'ChildController@set');
            Route::delete('child', 'ChildController@delete');

            Route::get('message-history', 'UserController@getMessageHistory');
            Route::post('msg-action', 'UserController@msgAction');

            Route::get('thanksgiving', 'ThanksgivingController@get');
            Route::post('thanksgiving', 'ThanksgivingController@set');

            Route::get('claims', 'ClaimController@getList');
            Route::post('getClaim', 'ClaimController@getClaim');
            Route::post('claims', 'ClaimController@add');
            Route::post('deleteClaim', 'ClaimController@delete');

            //dev-499 - Unable to register claim
            Route::post('claims/create','ClaimController@store');

            Route::get('policies', 'PolicyController@getList');
            Route::post('policies', 'PolicyController@getList');
            Route::get('orderReview', 'PolicyController@orderReview');
            Route::post('orderReview', 'PolicyController@orderProcess');
            Route::get('payment-history', 'PolicyController@paymentHistory');
            Route::get('corpcoverage','PolicyController@corporate_coverageupdate');
            Route::get('checkpdscorp','PolicyController@checkPds');

            Route::get('charityQueue', 'CharityApplicantController@queueList');

            Route::get('bankAccounts', 'BankAccountController@getList');
            Route::post('deleteAccount', 'BankAccountController@delete');

            Route::post('bankCard', 'BankCardController@add');
            Route::post('deleteCard', 'BankCardController@delete');

            Route::get('foreign','ForeignController@getQuestions');
            Route::post('foreign','ForeignController@store');
            
            // claim
			Route::get('/hospitals', 'CompanyController@hospitals');
			Route::post('/search/companies', 'CompanyController@search');
			Route::post('/search/hcpanels', 'HCPanelController@search');

			Route::post('/claims/store', 'ClaimController@store')->name('claims.store');
			Route::post('/claims/assign-hospital', 'ClaimController@assignHospital')->name('claims.assign.hospital');
			Route::get('/claims/detail', 'ClaimController@detail')->name('claims.assign.hospital');
			Route::get('/documents/show/{type}/{path}.{ext}','ClaimController@showDocumentResize')->name('admin.dashboard.documentResize');
			Route::get('/download/resource/{path}','ClaimController@downloadResource')->name('download.resource');
			Route::post('claims/upload/doc','ClaimController@uploadDoc')->name('upload.doc');

            Route::get('/claims/detail_faq', 'ClaimController@detail_faq');

            Route::post('update-notification', 'UserController@updateNotification');
            Route::post('faceLockVerification', 'FaceLockVerificationController@verify');
            Route::post('reject-pay-other', 'UserController@rejectPayOther');
            Route::get('/sendMail', 'UserController@sendMail');
            Route::post('/corporate-coverage-status', 'CorporateController@updateCoverageStatus');

		});


        Route::group(['middleware' => ['corporate']], function () {
            Route::get('packages', 'GroupPackageController@getPackages');
            Route::post('packages', 'GroupPackageController@createPackage');
            Route::post('getPackageMembers', 'GroupPackageController@getMembers');
            Route::post('packageMembers', 'GroupPackageController@createMember');
            Route::post('deletePackage', 'GroupPackageController@deletePackage');
            Route::post('deletePackageMember', 'GroupPackageController@deleteMember');

            Route::get('corporate-order-review', 'PolicyController@corporateOrderReview');
            Route::post('corporate-order-review', 'PolicyController@corporateOrderProcess');
        });
            
            Route::get('logout', 'AuthController@logout');
            
            Route::post('/userSendVerification', 'MobileVerifyController@userSendVerification')->name('api.user.verification.send');
            Route::post('/userValidateVerification', 'MobileVerifyController@userValidateVerification')->name('api.user.verification.validate');
            Route::post('getOwnerInfo', 'UserController@getOwnerInfo');            
        });


//    Route::group([
//        'middleware' => 'auth:api'
//    ], function () {
//        Route::get('logout', 'AuthController@logout');

//        Route::get('user', 'AuthController@user');
//    });

 //   Route::get('getStatus', 'UserController@getStatus');

    Route::get('getStatus', 'UserController@getStatus');
    Route::get('get-terms', 'UserController@getTerms');
    Route::get('doctype','SpoInsuranceController@doctype');

    Route::post('/login', 'AuthController@login')->name('api.login');
    Route::post('/check-user', 'UserController@checkUser')->name('api.check-user');
    Route::post('/register', 'AuthController@signup')->name('api.register');

    Route::post('/social-auth', 'AuthController@socialAuth');
    Route::post('/forgotPassword', 'AuthController@resetPassword')->name('api.resetPassword');
    Route::post('/forgotPassword/confirm', 'AuthController@confirmPassword')->name('api.confirmPassword');

    Route::post('/sendVerification', 'MobileVerifyController@sendVerification')->name('api.verification.send');
    Route::post('/validateVerification', 'MobileVerifyController@validateVerification')->name('api.verification.validate');

    Route::post('/auth/activate', 'AuthController@activateEmail');
    Route::get('/tools/getStateInfo', 'AddressController@stateList')->name('stateList');
    Route::post('/setLocale', 'UserController@setLocale');
    Route::get('getIndustryJobsList', 'IndustryJobsController@getList');
    Route::get('getClinicList', 'HCClinicController@getList')->name('api.clinic.list');
    
    Route::post('/refreshLoginToken', 'AuthController@refreshToken');
    Route::post('/register-v2', 'AuthController@signupv2');


});