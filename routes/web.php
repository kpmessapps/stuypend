<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('apns/test/{deviceToken}', ['as' => 'pushApnsTest', 'uses' => 'webservices\CommonController@pushApnsTest']);


/// EMAIL VERIFICATION AND PASSWORD DEEP LINKING
Route::get('email/password/token/{email}/{resetToken}',['as' => 'resetEmailPasswordTokenLink',function ($email,$resetToken) {
    return view('mobileTokenRedirect')->with(['emailToken' => $resetToken,'email'=>$email,'tokenType'=>"resetPassword"]);
}]); 


Route::group(['prefix' => 'webservices','as' => 'webservices::'], function () {
    Route::any('app/request/log/{limit}/{pageno}', 'webservices\CommonController@tempRequestLogList')->name('tempRequestLogList');
    
    Route::post('verify/phone', 'webservices\CommonController@verifyPhoneToken')->name('verifyPhoneToken');
    
    Route::any('user/phone/register', 'webservices\UserController@registerPhoneNumber')->name('registerPhoneNumber');
    Route::any('user/phone/verify', 'webservices\CommonController@verifyPhoneToken')->name('verifyPhoneToken');
        
    Route::post('auth/login', 'webservices\CommonController@login')->name('login');
    Route::post('auth/logout', 'webservices\UserController@logout')->name('logout');
    Route::post('account/contractor/delete', 'webservices\UserController@deleteAccount')->name('deleteAccount');
    Route::get('auth/resetpwd', 'webservices\CommonController@forgotPassword')->name('forgotPassword');
    Route::post('auth/resetpwd', 'webservices\CommonController@resetPassword')->name('resetPassword');
    
    Route::post('signup', 'webservices\CommonController@signup')->name('signup');
    
    Route::get('profile', 'webservices\UserController@getUserProfile')->name('getUserProfile');
    Route::post('profile', 'webservices\UserController@updateUserProfile')->name('updateUserProfile');
    Route::post('change/pwd', 'webservices\UserController@changePassword')->name('changePassword');
    
    Route::get('app/config/data', 'webservices\UserController@appConfigData')->name('appConfigData');
    
    Route::post('{moderateBy}/moderate/contractor/{contractorId}/job/{jobId}', 'webservices\JobController@moderateJobRequest')->name('moderateJobRequest');
    Route::post('job/{jobId}/request', 'webservices\JobController@sendJobRequest')->name('sendJobRequest');
    Route::post('job/create', 'webservices\JobController@createJob')->name('createJob');
    Route::get('job/list', 'webservices\JobController@getJobList')->name('getJobList');
    Route::get('job/{jobId}/contractor/{statusType}/list', 'webservices\JobController@getJobContractorList')->name('getJobContractorList');
    
    
    Route::get('executive/client/list', 'webservices\JobController@getExecutiveClientList')->name('getExecutiveClientList');
    
    Route::get('game/code/verify', 'webservices\GameController@getFriendGameCodeDetail')->name('getFriendGameCodeDetail');
    Route::get('game', 'webservices\GameController@getNewGame')->name('getNewGame');
    Route::post('game', 'webservices\GameController@submitGame')->name('submitGame');
    
    
    Route::get('business/ads/detail', 'webservices\GameController@getBusinessAdsDetail')->name('getBusinessAdsDetail');
    
    Route::get('business/list', 'webservices\GameController@getBusinessList')->name('getBusinessList');
    Route::post('business/{businessId}/favorite/action', 'webservices\GameController@favoriteUnfavoriteBusiness')->name('favoriteUnfavoriteBusiness');
    
    
//    Route::post('gyms', 'webservices\GymController@addGym')->name('addGym');
//    Route::get('gyms', 'webservices\GymController@myGym')->name('myGym');
//    
//    Route::post('checkins/{gymId}/fill/{checkInId}', 'webservices\GymController@checkOutToGym')->name('checkOutToGym');
//    Route::post('checkins/{gymId}', 'webservices\GymController@checkInToGym')->name('checkInToGym');
//    
//    Route::post('activities/summary', 'webservices\GymController@addActivitiesSummeryToGym')->name('addActivitiesSummeryToGym');
//    Route::get('activities/summary', 'webservices\GymController@getMyActivitySummary')->name('getMyActivitySummary');
//    
//    Route::get('activities/search', 'webservices\GymController@searchActivity')->name('searchActivity');
//    
});

Route::group(['prefix' => 'admin', 'as' => 'admin::'], function () {
        /* no need to change here */
        Route::get('/', ['as' => 'base', 'uses' => 'admin\QuestionController@getIndex']);
        Route::get('login', ['as' => 'login', 'uses' => 'admin\LoginController@getIndex']);
        Route::post('login/auth', ['as' => 'loginAuth', 'uses' => 'admin\LoginController@doLogin']);

        // Dashboard and common
        Route::get('dashboard', ['as' => 'dashboard', 'uses' => 'admin\QuestionController@getIndex']);
        Route::any('ajax/dashboard/answer/report', ['as' => 'ajaxAnswerGraphReport', 'uses' => 'admin\DashboardController@ajaxAnswerGraphReport']);
        Route::any('ajax/dashboard/user/report', ['as' => 'ajaxUserGraphReport', 'uses' => 'admin\DashboardController@ajaxUserGraphReport']);
        Route::get('settings', ['as' => 'settings', 'uses' => 'admin\DashboardController@settings']);
        Route::post('settings', ['as' => 'doSettingsEdit', 'uses' => 'admin\DashboardController@doSettingsEdit']);
        Route::get('password/change', ['as' => 'changePassword', 'uses' => 'admin\DashboardController@changePassword']);
        Route::post('password/change', ['as' => 'doChangePasswordEdit', 'uses' => 'admin\DashboardController@doChangePasswordEdit']);
        Route::get('profile', ['as' => 'profile', 'uses' => 'admin\DashboardController@profile']);
        Route::get('logout', ['as' => 'logout', 'uses' => 'admin\DashboardController@doLogout']);


        // User Master
        Route::get('user/list', ['as' => 'userList', 'uses' => 'admin\UserAdminController@getIndex']);
        Route::get('user/add', ['as' => 'userAdd', 'uses' => 'admin\UserAdminController@add']);
        Route::post('user/doAdd', ['as' => 'doUserAdd', 'uses' => 'admin\UserAdminController@doAdd']);
        Route::get('user/edit/{id}', ['as' => 'userEdit', 'uses' => 'admin\UserAdminController@edit'])->where(['id' => '[0-9]+']);
        Route::post('user/edit/{id}', ['as' => 'doUserEdit', 'uses' => 'admin\UserAdminController@doEdit'])->where(['id' => '[0-9]+']);
        Route::get('user/loadListData', ['as' => 'userLoadListData', 'uses' => 'admin\UserAdminController@loadListData']);
        // Route::get('user/userMemoryLoadListData', ['as' => 'userMemoryLoadListData', 'uses' => 'admin\UserAdminController@userMemoryLoadListData']);
        Route::post('user/gridDataAction', ['as' => 'userGridDataAction', 'uses' => 'admin\UserAdminController@gridAction']);
        //Route::get('user/detail/{id}', ['as' => 'userDetail', 'uses' => 'admin\UserAdminController@detail'])->where(['id' => '[0-9]+']);
        /* no need to change up to here */


       
        Route::get('question/list', ['as' => 'questionList', 'uses' => 'admin\QuestionController@getIndex']);
        Route::get('question/import', ['as' => 'questionImport', 'uses' => 'admin\QuestionController@import']);
        Route::post('question/import', ['as' => 'questionImport', 'uses' => 'admin\QuestionController@doImport']);
        Route::get('question/add', ['as' => 'questionAdd', 'uses' => 'admin\QuestionController@add']);
        Route::post('question/doAdd', ['as' => 'doQuestionAdd', 'uses' => 'admin\QuestionController@doAdd']);
        Route::get('question/edit/{id}', ['as' => 'questionEdit', 'uses' => 'admin\QuestionController@edit'])->where(['id' => '[0-9]+']);
        Route::post('question/edit/{id}', ['as' => 'doQuestionEdit', 'uses' => 'admin\QuestionController@doEdit'])->where(['id' => '[0-9]+']);
        Route::get('question/loadListData', ['as' => 'questionLoadListData', 'uses' => 'admin\QuestionController@loadListData']);
        Route::post('question/gridDataAction', ['as' => 'questionGridDataAction', 'uses' => 'admin\QuestionController@gridAction']);
        Route::post('question/changeStatus', ['as' => 'questionStatusChange', 'uses' => 'admin\QuestionController@changeStatus']);

        // deal Master
        Route::get('answer/list/{type}/{dataId}', ['as' => 'filterAnswerList', 'uses' => 'admin\AnswerController@getIndex']);
        Route::get('answer/list', ['as' => 'answerList', 'uses' => 'admin\AnswerController@getIndex']);
        Route::get('answer/loadListData', ['as' => 'answerLoadListData', 'uses' => 'admin\AnswerController@loadListData']);
        Route::post('answer/gridDataAction', ['as' => 'answerGridDataAction', 'uses' => 'admin\AnswerController@gridAction']);
        Route::get('answer/delete/{id}', ['as' => 'answerDelete', 'uses' => 'admin\AnswerController@delete']);
        Route::get('answer/view/{id}', ['as' => 'answerView', 'uses' => 'admin\AnswerController@view']);
        
    });

Auth::routes();

Route::get('/home', 'DashboardController@index');
    