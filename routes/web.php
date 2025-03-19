<?php     

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use function foo\func;

Route::get('/', function () {
    //return redirect()->route('userpanel.dashboard.main');
return redirect()->route('partner.login');
});


//Route::get('/test','TPADataSyncController@upload')->name('page.index');
//Route::get('/test','Api\ToolsController@test');
Route::get('/send-test-notification','Api\ToolsController@sendTestNotification');


Route::get('/page/{page}','PageController@index')->name('page.index');
Route::get('/notification/{uuid}','PageController@showNotification')->name('page.showNotification');

Route::get('/payment/authenticate/{id}/{platform}', 'User\PaymentGatewayController@getToken');
#Route::get('/partner/login','PageController@redirect_funtion')->name('partner.login');

Auth::routes();

Route::get('/payment/callback','User\PaymentGatewayController@callback');
Route::post('/payment/callback','User\PaymentGatewayController@callback');
Route::get('/payment/pay/{order_id}','User\PaymentGatewayController@pay');

//Route::get('/payment/return','User\PaymentGatewayController@return_');
Route::get('/payment/return','User\PaymentGatewayController@return_post');
Route::post('/payment/return','User\PaymentGatewayController@return_post');

Route::get('/partner/login','Auth\LoginController@checkLogin')->name('partner.login');
Route::get('/partner','Auth\LoginController@checkpartner')->name('partners.login');
Route::get('/partner/password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('partner.password.reset');

Route::get('/partner/login','Auth\LoginController@checkLogin')->name('partner.login');
Route::get('/partner','Auth\LoginController@checkpartner')->name('partners.login');
Route::get('/logout','Auth\LoginController@logout')->name('logout');
Route::get('/oauth/{provider}/redirect','Auth\OAuthController@redirect')->name('oauth.redirect');
Route::get('/oauth/{provider}/callback','Auth\OAuthController@callback')->name('oauth.callback');


Route::get('/documents/show/{path}.{ext}','Admin\DashboardController@showDocument')->name('admin.dashboard.document');
Route::get('/documents/show/{type}/{path}.{ext}','Admin\DashboardController@showDocumentResize')->name('admin.dashboard.documentResize');

Route::get('/doc','User\DocumentController@docViewIndex')->name('doc.view');
Route::get('/doc/generate','User\DocumentController@generateDoc')->name('doc.generate');
Route::get('/download/resource/{path}','User\DocumentController@downloadResource')->name('download.resource');
Route::get('/con','PremiumCalculatorController@getPrice_campaign')->name('con.view');



Route::middleware(['IsPartner'])->prefix('partner_old')->namespace('Partner')->name('partner.')->group(function(){

    Route::get('/login', 'Auth\LoginController@showLoginForm')->name('auth.login');
    Route::post('/login', 'Auth\LoginController@login')->name('auth.login');
    Route::get('/logout', 'Auth\LoginController@logout')->name('auth.logout');

    Route::get('/register', 'Auth\RegisterController@showRegistrationForm')->name('auth.register');
    Route::post('/register', 'Auth\RegisterController@register')->name('auth.register');

    Route::get('/','DashboardController@main')->name('dashboard.main');

    Route::resource('User','UserController');
    Route::resource('Role','RoleController');



});

Route::prefix('ops')->middleware(['IsAdmin','hasPermission','DisableRouteCache','XssSanitizer'])->namespace('Admin')->name('admin.')->group(function(){
	Route::post('/refresh-csrf',function (){
		return csrf_token();
	})->name('refresh.csrf');

    Route::get('/login','Auth\LoginController@showLoginForm')->name('auth.login');
    Route::post('/login','Auth\LoginController@login')->name('auth.login');
    Route::get('/logout','Auth\LoginController@logout')->name('auth.logout');

    Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
    Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');

    Route::get('/','DashboardController@main')->name('dashboard.main');

    Route::resource('SanctionScreen','SanctionScreenController');
    Route::get('UserScreen/{id}/edit','SanctionScreenController@edit')->name('userScreen.edit');
    Route::post('UserScreen/{id}/edit','SanctionScreenController@update')->name('userScreen.update');
    Route::get('UserScreen/{id}/details','SanctionScreenController@details')->name('userScreen.details');

    Route::get('notification/send','NotificationController@create')->name('Notification.create');
    Route::post('notification','NotificationController@store')->name('Notification.store');
    Route::get('notification/list','NotificationController@index')->name('Notification.index');
    Route::get('notification/{uuid}','NotificationController@show')->name('Notification.show');
    Route::get('spo/dashboard','SpoInsuranceController@sopsummary')->name('Spo.dashboard');

    Route::get('/insertvou','AddVoucherController@insertvoucher')->name('insertvou.view');
    Route::get('/addvou','UserController@addVoucher')->name('addvoucher.view');
    
    Route::resource('InternalUser','InternalUserController');
    Route::resource('Coverage','CoverageController');
    Route::resource('Payment','PaymentController');
    Route::resource('Underwriting','UnderwritingController');
    Route::resource('Beneficiary','BeneficiaryController');

    Route::resource('spo','SpoInsuranceController');

    Route::resource('referral','ReferralController');
    
    //Dev-495 - Error in "Edit Profile" for a User
    //Route::resource('Profile','ProfileController');
    Route::get('ac/editprofile','ProfileAccountInformationController@editProfile')->name('ac.editprofile');
    Route::post('ac/editprofile','ProfileAccountInformationController@editProfileStore')->name('ac.editprofile.store');

    Route::resource('Role','RoleController');
    Route::resource('Industry','IndustryController');
    Route::resource('Job','JobController');
    Route::resource('User','UserController');
    Route::get('corporates','UserController@corporatesIndex')->name('corporates.index');
    Route::resource('Verification','VerificationController');
    Route::resource('CoverageOrder','CoverageOrderController');
    Route::get('Verification/{id}/verify','VerificationController@verify')->name('Verification.verify');
    Route::post('Verification/{id}/verify','VerificationController@submitVerify')->name('Verification.submitVerify');
    
    Route::get('/claim/{uid}/export-answers','ClaimController@exportAnswers')->name('claim.export.answers');
    Route::resource('claim','ClaimController');
    Route::resource('Config','ConfigController');
    Route::post('User/{id}/resetPassword','UserController@resetPassword')->name('User.resetPassword');
    Route::post('User/{id}/verification','UserController@verification')->name('User.verification');
    Route::get('User/{id}/details','UserController@details')->name('User.details');
    Route::get('User/{id}/audit','UserController@audit')->name('User.audit');
    Route::get('User/{id}/verification','UserController@verification')->name('User.verification');
    Route::resource('CharityApplicant','CharityApplicantController');
    Route::get('CharityApplicant/{uid}/details','CharityApplicantController@details')->name('CharityApplicant.details');
    Route::get('spo/{uuid}/details','SpoInsuranceController@details')->name('Spo.details');
    Route::post('spo/updatestatus','SpoInsuranceController@updatestatus')->name('Spo.statusupdate');

    Route::get('referral/{uuid}/details','ReferralController@details')->name('referral.details');
    Route::post('referral/{uuid}/updatestatus','ReferralController@updatestatus')->name('referral.statusupdate');


    // reporting
    Route::get('reports','ReportController@index')->name('reports.index');
    Route::post('reports/insurance-penetration','ReportController@insurancePenetration')->name('reports.insurance.penetration');
    Route::post('reports/underwriting-rejection','ReportController@underwritingRejection')->name('reports.underwriting.rejection');
    Route::post('reports/far-classification','ReportController@farreport')->name('reports.far.classification');
    Route::post('reports/tat','ReportController@tatreport')->name('reports.tat.classification');
    Route::post('reports/uw-data','ReportController@exportMemebersUwData')->name('reports.uw.data');
    Route::post('reports/pud','ReportController@prodUserDetails')->name('reports.pud.classification');
    Route::post('reports/ipr','ReportController@insurancepenetrationReport')->name('reports.ipr.classification');
    Route::post('reports/cudr','ReportController@customerDetails')->name('reports.cudr.classification');
    Route::post('reports/produserdetailsnew','ReportController@produserdetails_new')->name('reports.prod.userdetails.new');


    Route::post('reports/underwriting-rejection-analysis','ReportController@underwritingRejectionAnalysis')->name('reports.underwriting.rejection.analysis');
    Route::get('reports/underwriting-rejection-analysis/{uuid}','ReportController@underwritingRejectionAnalysisByUser')->name('reports.underwriting.rejection.analysis.by.user');

	Route::get('reports/export-csv','ReportController@exportCsv')->name('reports.export.csv');
	Route::get('claims/import','ClaimController@import')->name('claims.import');
	Route::post('claims/import/csv','ClaimController@importCsv')->name('claims.import.csv');
    Route::post('/claim/cancel','ClaimController@cancel')->name('claim.cancel');

    Route::get('vouchercampaign/imp','ArekaController@imp')->name('vouchercampaign.imp');
    Route::post('areka/import/csv','ArekaController@importCsv')->name('areka.import.csv');
    Route::post('sarawak/import/csv','SarawakController@importCsv')->name('sarawak.import.csv');
    Route::get('/precal','UserController@Premiunratechecking')->name('precal.view');

    
	// coverage moderation
    Route::post('coverage-moderation-action/index','CoverageModerationActionController@index')->name('coverage.moderation.action.index');
    Route::post('coverage-moderation-action/store','CoverageModerationActionController@store')->name('coverage.moderation.action.store');

    // paricular changes
    Route::get('User/{uuid}/change-basic-info','UserController@changeBasicInfo')->name('user.change.basic.info');
    Route::get('User/{uuid}/change-payment-term','UserController@changePaymentTerm')->name('user.change.payment.term');
    Route::get('User/{uuid}/cancell-coverage','UserController@cancellCoverage')->name('user.cancell.coverage');

    // credit
    Route::get('User/{uuid}/credit','CreditController@getUserCreditsLog')->name('user.credit.show');
    Route::get('credits','CreditController@credits')->name('credits.index');

    // refund
    Route::get('refunds','RefundController@index')->name('refunds.index');

    // Beneficiary
    Route::post('/beneficiary/update','UserController@updateBeneficiary')->name('beneficiary.update');
    Route::post('reports/successful-transaction','ReportController@successfulTransaction')->name('reports.transaction.successful');
    Route::post('reports/customer-list','ReportController@customerlist')->name('reports.customer.list');
    Route::post('reports/referral-list','ReportController@referrallist')->name('reports.referral.list');
    Route::post('reports/member-data','ReportController@exportMemberData')->name('reports.export.member.data');
    
});
Route::middleware(['auth','ProfileDone','XssSanitizer'])->prefix('partner')->namespace('User')->name('userpanel.')->group(function(){

    
    Route::get('/time-tube','TimeTubeController@index')->name('time-tube.index');
    Route::get('/notification-area','NotificationController@index')->name('notification.index');


    Route::get('/go/{route}','DashboardController@go')->name('go');
    Route::get('/Dashboard','DashboardController@main')->name('dashboard.main');

    Route::get('/profile','ProfileController@index')->name('dashboard.profile');
    Route::post('/profile','ProfileController@store')->name('dashboard.profile.save');

    Route::post('/profile/doc','ProfileController@storeDocument')->name('dashboard.profile.doc');
    Route::get('/profile/doc/remove','ProfileController@destroyDocument')->name('dashboard.profile.doc.remove');

    Route::get('/foreign','ForeignController@index')->name('foreign.index');
    Route::post('/foreign','ForeignController@store')->name('foreign.store');

    Route::get('/setting','SettingController@index')->name('setting.index');
    Route::get('/setting/language','SettingController@language')->name('setting.language');

    Route::get('/account/change-password','AccountInformationController@changePassword')->name('account.change-password');
    Route::post('/account/change-password','AccountInformationController@changePasswordStore')->name('account.change-password.store');

    Route::get('/account/change-email','AccountInformationController@changeEmail')->name('account.change-email');
    Route::post('/account/change-email','AccountInformationController@changeEmailStore')->name('account.change-email.store');

    Route::get('/account/change-mobile','AccountInformationController@changeMobile')->name('account.change-mobile');
    Route::post('/account/change-mobile','AccountInformationController@changeMobileStore')->name('account.change-mobile.store');

    Route::get('/register-user','AccountInformationController@showRegistrationForm')->name('account.registration-form');
    Route::post('/register-user','AccountInformationController@storeUserData')->name('account.store-user');

    Route::middleware(['individual'])->group(function (){
        Route::get('/charity','CharityController@index')->name('charity.index');
        Route::post('/charity','CharityController@store')->name('charity.save');
        Route::post('/charity/Upload/{uuid}/Selfie','CharityController@uploadSelfie')->name('charity.upload.selfie');
        Route::post('/charity/Upload/{uuid}/Doc','CharityController@uploadDoc')->name('charity.upload.doc');
        Route::get('/charity/Upload/{uuid}/Remove','CharityController@removeDoc')->name('charity.upload.remove');

        Route::get('/medical-survey','MedicalSurveyController@index')->name('MedicalSurvey.index');

        Route::get('/beneficiary','BeneficiaryController@index')->name('Beneficiary.index');
        Route::post('/beneficiary','BeneficiaryController@store')->name('Beneficiary.store');

        Route::get('/thanksgiving','ThanksgivingController@index')->name('Thanksgiving.index');
        Route::post('/thanksgiving','ThanksgivingController@store')->name('Thanksgiving.store');

        Route::get('/verification','VerificationController@index')->name('Verification.index');
        Route::post('/verification','VerificationController@store')->name('Verification.store');

        Route::get('/product','ProductController@index')->name('product.index');

        Route::get('/claim','ClaimController@index')->name('claim.index');
        Route::get('/claim/create','ClaimController@create')->name('claim.create');
        Route::get('/claim/{uid}/edit','ClaimController@edit')->name('claim.edit');
        Route::post('/claim','ClaimController@store')->name('claim.store');
        Route::get('/claim/{uid}/destroy','ClaimController@destroy')->name('claim.destroy');
        Route::post('/claim/{uid}/upload/add','ClaimController@upload')->name('claim.upload.add');
        Route::get('/claim/{uid}/upload/remove','ClaimController@removeupload')->name('claim.upload.remove');

        Route::get('/sponsor','SponsorController@index')->name('sponsor.index');
        Route::post('/sponsor/getData','SponsorController@getData')->name('sponsor.getData');

        Route::get('/referral','ReferralController@index')->name('referral.index');

        Route::get('/order','OrderController@index')->name('order.index');
        Route::get('order/{uid}/','OrderController@other')->name('order.other');

        Route::get('/payment-history','PolicyController@history')->name('history.index');

        Route::get('/bank-account-detail','BankAccountController@indexAccount')->name('bank_account.index');
        Route::get('/bank-card','BankAccountController@index')->name('bank_card.index');
        Route::post('/bank-account-detail','BankAccountController@store')->name('bank_account.store');
        Route::post('/set-fund-source','BankAccountController@setFundSource')->name('bank_account.setFundSource');

        Route::get('/bank-account-card/{uid}/destroy','BankAccountController@destroy')->name('bank_account.destroy');

        Route::post('/bank-card','BankAccountController@storeCard')->name('bank_card.store');

        Route::get('/policies','PolicyController@index')->name('policies.index');
        Route::get('/policies/{uid}/product','PolicyController@product')->name('policies.product');

        Route::get('/child','ChildController@index')->name('child.index');

        Route::get('/promote','PromoteController@index')->name('promote.index');
        Route::get('/promote/my-promoted','PromoteController@myPromoted')->name('promote.myPromoted');
        Route::get('/promote/my-promoted/{uid}/medical-survey','PromoteController@medicalSurvey')->name('promote.medicalSurvey');
        Route::get('/promote/my-promoted/{uid}/product','PromoteController@product')->name('promote.product');

        Route::get('/pay-for-others/{uid}/medical-survey','PayForOtherController@medicalSurvey')->name('pay_for_others.medicalSurvey');
        Route::get('/pay-for-others/{uid}/product','PayForOtherController@product')->name('pay_for_others.product');

    });

    Route::middleware(['corporate'])->group(function (){

        Route::get('/group-package','GroupPackageController@index')->name('groupPackage.index');
        Route::get('/group-package/create','GroupPackageController@newPackage')->name('groupPackage.newPackage');
        Route::post('/group-package/store','GroupPackageController@savePackage')->name('groupPackage.savePackage');
        Route::get('/group-package/{uid}/edit','GroupPackageController@editPackage')->name('groupPackage.editPackage');
        Route::get('/group-package/{uid}/destroy','GroupPackageController@destroyPackage')->name('groupPackage.destroyPackage');
        Route::get('/group-package/{uid}/members','GroupPackageController@packageMembers')->name('groupPackage.packageMembers');
        Route::post('/group-package/{uid}/members','GroupPackageController@savePackageMembers')->name('groupPackage.savePackageMembers');

        Route::middleware(['clinic'])->group(function () {

            Route::get('clinic/review', 'ClinicController@review')->name('clinic.review');
            Route::post('clinic/review', 'ClinicController@create')->name('clinic.create');
            Route::get('clinic/{uid}/fill', 'ClinicController@fill')->name('clinic.fill');
            Route::post('clinic/{uid}/fill', 'ClinicController@store')->name('clinic.store');

        });
        Route::middleware(['hospital'])->group(function () {
            
//            Route::get('hospital/review', 'HospitalController@review')->name('hospital.review');
//            Route::post('hospital/review', 'HospitalController@create')->name('hospital.create');

            Route::get('hospital/claim','HospitalController@claim')->name('hospital.claim');
            Route::get('hospital/claim/scan','HospitalController@scan')->name('hospital.scan');
            Route::get('hospital/claim/create','HospitalController@create')->name('hospital.create');
            Route::post('hospital/claim/create','HospitalController@parse')->name('hospital.create');

            Route::get('hospital/claim/{uid}/coverage','HospitalController@coverage')->name('hospital.coverage');
            Route::get('hospital/claim/{uid}/details','HospitalController@details')->name('hospital.details');

            Route::post('hospital/checkSelfie', 'HospitalController@checkSelfie')->name('hospital.checkSelfie');
            Route::post('/hospital/review/{uuid}/Doc','HospitalController@uploadDoc')->name('hospital.upload.doc');
            Route::get('/hospital/hospital/{uuid}/Remove','HospitalController@removeDoc')->name('hospital.upload.remove');
            Route::get('/hospital/hospital/{id}/dl','HospitalController@dlDoc')->name('hospital.upload.dl');
			//Route::get('hospital/claim/{cuuid}/{puuid}/import','HospitalController@claimDeatil')->name('hospital.claim.detail');
			Route::get('hospital/claim/{uuid}/import','HospitalController@claimDeatil')->name('hospital.claim.detail');


		});

    });


});

Route::middleware(['auth:internal_users,web', 'XssSanitizer'])->prefix('web-api')->name('wb-api.')->group(function() {


    Route::get('order', 'Api\PolicyController@orderReview')->name('order-review');
    Route::post('order', 'Api\PolicyController@orderProcess')->name('order-process');

    Route::post('getProducts', 'Api\ProductController@index')->name('getproducts');
    Route::any('underwritings', 'Api\UnderwritingController@get')->name('getunderwritings');
    Route::get('getIndustryJobsList', 'Api\IndustryJobsController@getList')->name('getIndustryJobsList');
    Route::post('generateQR', 'Api\QRController@generate')->name('generateQR');
    Route::post('thanksgiving', 'Api\ThanksgivingController@set')->name('setThanksgiving');
    Route::post('promote', 'Api\PromoterController@add')->name('addPromoter');
    Route::post('/check-user', 'Api\UserController@checkUser')->name('check-user');
    Route::post('buy-for-others', 'Api\ProductController@buyForOther')->name('buyForOther');
    Route::post('can-pay-for-others', 'Api\ProductController@canPayForOthers')->name('can-pay-for-others');
    Route::post('foreign', 'Api\ForeignController@store')->name('foreign');
    Route::post('msg-action', 'Api\UserController@msgAction')->name('msgAction');
    Route::post('save-child', 'Api\ChildController@set')->name('save-child');
    Route::post('remove-child', 'Api\ChildController@delete')->name('remove-child');

    Route::get('/tools/initPostRegisterIndividual', 'Api\ToolsController@initPostRegisterIndividual')->name('initPostRegisterIndividual');
});
