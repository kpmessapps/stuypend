<?php

namespace App\Http\Controllers\Webservices;

use Mail;
use Input;
use Illuminate\Http\Request as Request;
use Validator;
use App\User as User;
use App\Http\Controllers\Controller;
use DB;
use Hash;
use File;
use Image;
use Storage;
use JWTAuth;
use JWTFactory;
use Tymon\JWTAuth\Exceptions\JWTException;
class UserController extends Controller {
    
    protected  $time;
    public function __construct()
    {
        $this->time = time();
    }
    
    public function appConfigData(Request $request) {
        $requestLogId = 0;
        try{
            $xSessionID      = $request->header('X-Session-ID','');
            $userData        = User::checkAccessToken($xSessionID);
            $getArr = [];
            if(!checkArr($userData)){
                $requestLogId = User::requestLogData('0','user/appConfig',$_POST,array_merge($getArr,$_GET),$_FILES,$request->header());
                $jsonResponse = array(
                        "statusCode" => 9,
                        "errors"     => array(),
                        "message"    => "Access denied.",
                    );
                return response()->json(parseResponse($jsonResponse), 401);
            }
            $userId         = $userData['u_id'];
            $securityToken  = $userData['securityToken'];
            
            $requestLogId = User::requestLogData($userId,'user/appConfig',$_POST,array_merge($getArr,$_GET),$_FILES,$request->header());
            
            $xDeviceId      = $request->header('X-Device-ID');
            $xApiKey        = $request->header('X-API-Key');

            if($xApiKey != env('USER_X_API_KEY')){
                $jsonResponse = [
                    "statusCode" => 8,
                    "errors" => array(),
                    "message" => "Please update your application.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,4);
                return response()->json(parseResponse($jsonResponse), 400);
            }
            
            // validate the info, create rules for the inputs
            $rules = [];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $messages = $validator->errors()->all();
                $jsonResponse = [
                    "statusCode" => "2",
                    "errors" => $messages,
                    "message" => isset($messages['0']) ? $messages['0'] :  "Please, Fill all the mendotary detail.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,5);
                return response()->json(parseResponse($jsonResponse), 400);
            }
            
            $tempList = DB::table('a_skills')->where('skill_status', 1)->get()->toArray();
            $tempList = parseResponse($tempList);
            
            $skillList = [];
            foreach($tempList as $key => $value){
                $skillList[] = getSkillResp($value);
            }
            
            $jsonResponse = [
                    "statusCode"        => "1",
                    "errors"            => [],
                    "message"           => "Success.",
                ];
            $jsonResponse["data"] = [
                    "skillList"  => $skillList,
                ];
            User::responseLogData($requestLogId,$jsonResponse);
            return response(parseResponse($jsonResponse), 200)->header('Content-Type', "json");
        }catch (\Exception $e) {
            $jsonResponse = array(
                    "statusCode" => "2",
                    "errors"     => [$e->getMessage(),"Line number : ".$e->getLine(),"Whole error : ".$e->getTraceAsString()],
                    "message"    => (env('APP_SHOW_EXCEPTION_MSG',false) == true) ? $e->getMessage() : "There are problem with passing your request. Please, try again.",
            );
            User::responseLogData($requestLogId,$jsonResponse,1);
            return response()->json(parseResponse($jsonResponse), 500);
        }
    }
    
    public function logout(Request $request) {
        $requestLogId = 0;
        try{
            $xSessionID      = $request->header('X-Session-ID','');
            $userData        = User::checkAccessToken($xSessionID);
            $getArr = [];
            if(!checkArr($userData)){
                $requestLogId = User::requestLogData('0','user/logout',$_POST,array_merge($getArr,$_GET),$_FILES,$request->header());
                $jsonResponse = array(
                        "statusCode" => 9,
                        "errors"     => array(),
                        "message"    => "Access denied.",
                    );
                return response()->json(parseResponse($jsonResponse), 401);
            }
            $userId         = $userData['u_id'];
            $securityToken  = $userData['securityToken'];
            
            $requestLogId = User::requestLogData($userId,'user/logout',$_POST,array_merge($getArr,$_GET),$_FILES,$request->header());
            
            $xDeviceId      = $request->header('X-Device-ID');
            $xApiKey        = $request->header('X-API-Key');

            if($xApiKey != env('USER_X_API_KEY')){
                $jsonResponse = [
                    "statusCode" => 8,
                    "errors"     => array(),
                    "message"    => "Please update your application.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,4);
                return response()->json(parseResponse($jsonResponse), 400);
            }
            
            // validate the info, create rules for the inputs
            $rules = [
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $messages = $validator->errors()->all();
                $jsonResponse = [
                    "statusCode" => 2,
                    "errors"     => $messages,
                    "message"    => isset($messages['0']) ? $messages['0'] :  "Please, Fill all the mendotary detail.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,5);
                return response()->json(parseResponse($jsonResponse), 400);
            }
            
            $deleteOldToken = DB::table('a_user_device_tokens')->where('udt_u_id', $userId)->delete();
            
            $jsonResponse = [
                    "statusCode" => "1",
                    "errors"     => array(),
                    "message"    => "Success.",
                ];
            User::responseLogData($requestLogId,$jsonResponse);
            return response(parseResponse($jsonResponse), 200)->header('Content-Type', "json");
        
        }catch (\Exception $e) {
            $jsonResponse = array(
                    "statusCode" => "2",
                    "errors"     => [$e->getMessage(),"Line number : ".$e->getLine(),"Whole error : ".$e->getTraceAsString()],
                    "message"    => (env('APP_SHOW_EXCEPTION_MSG',false) == true) ? $e->getMessage() : "There are problem with passing your request. Please, try again.",
            );
            User::responseLogData($requestLogId,$jsonResponse,1);
            return response()->json(parseResponse($jsonResponse), 500);
        }
    }
    
    public function deleteAccount(Request $request,$userType) {
        $requestLogId = 0;
        try{
            $xSessionID      = $request->header('X-Session-ID','');
            $userData        = User::checkAccessToken($xSessionID);
            $getArr = [];
            if(!checkArr($userData)){
                $requestLogId = User::requestLogData('0','user/deleteAccount',$_POST,array_merge($getArr,$_GET),$_FILES,$request->header());
                $jsonResponse = array(
                        "statusCode" => 9,
                        "errors"     => array(),
                        "message"    => "Access denied.",
                    );
                return response()->json(parseResponse($jsonResponse), 401);
            }
            $userId         = $userData['u_id'];
            $securityToken  = $userData['securityToken'];
            
            $requestLogId = User::requestLogData($userId,'user/deleteAccount',$_POST,array_merge($getArr,$_GET),$_FILES,$request->header());
            
            $xDeviceId      = $request->header('X-Device-ID');
            $xApiKey        = $request->header('X-API-Key');

            if($xApiKey != env('USER_X_API_KEY')){
                $jsonResponse = [
                    "statusCode" => 8,
                    "errors"     => array(),
                    "message"    => "Please update your application.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,4);
                return response()->json(parseResponse($jsonResponse), 400);
            }
            
            // validate the info, create rules for the inputs
            $rules = [
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $messages = $validator->errors()->all();
                $jsonResponse = [
                    "statusCode" => 2,
                    "errors"     => $messages,
                    "message"    => isset($messages['0']) ? $messages['0'] :  "Please, Fill all the mendotary detail.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,5);
                return response()->json(parseResponse($jsonResponse), 400);
            }
            
            if($userData['u_type'] != 4){
                $jsonResponse = [
                    "statusCode" => 2,
                    "errors"     => [],
                    "message"    => "Sorry, you can't delete your account.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,5);
                return response()->json(parseResponse($jsonResponse), 400);
            }
            
            $updateData = [
                    'u_modified_date' => $this->time,
                    'u_status'  => '9'
            ];
            $isUserUpdated = DB::table('a_users')->where('u_id', $userId)->where('u_status', '<>',9)->update($updateData);
            if($isUserUpdated){
                $deleteOldToken = DB::table('a_user_device_tokens')->where('udt_u_id', $userId)->delete();
            
                $updateData = [
                        'job_con_modified_date' => $this->time,
                        'job_con_status'  => '9'
                ];
                $isUserUpdated = DB::table('a_job_contractors')
                        ->where('job_con_u_id', $userId)
                        ->where('job_con_status', '<>', 9)->update($updateData);

                $jsonResponse = [
                    "statusCode" => "1",
                    "errors"     => array(),
                    "message"    => "Success.",
                ];
                
            
                User::responseLogData($requestLogId,$jsonResponse);
                return response(parseResponse($jsonResponse), 200)->header('Content-Type', "json");
            }
            
            $jsonResponse = [
                "statusCode" => "2",
                "errors"     => array(),
                "message"    => "A problem has occurred during delete your account. Please, try again.",
            ];
            User::responseLogData($requestLogId,$jsonResponse);
            return response(parseResponse($jsonResponse), 200)->header('Content-Type', "json");

        }catch (\Exception $e) {
            $jsonResponse = array(
                    "statusCode" => "2",
                    "errors"     => [$e->getMessage(),"Line number : ".$e->getLine(),"Whole error : ".$e->getTraceAsString()],
                    "message"    => (env('APP_SHOW_EXCEPTION_MSG',false) == true) ? $e->getMessage() : "There are problem with passing your request. Please, try again.",
            );
            User::responseLogData($requestLogId,$jsonResponse,1);
            return response()->json(parseResponse($jsonResponse), 500);
        }
    }
    
    public function updateUserProfile(Request $request) {
        $requestLogId = 0;
        try{
            $xSessionID      = $request->header('X-Session-ID','');
            $userData        = User::checkAccessToken($xSessionID);
            $getArr = [];
            if(!checkArr($userData)){
                $requestLogId = User::requestLogData('0','user/updateUserProfile',$_POST,array_merge($getArr,$_GET),$_FILES,$request->header());
                $jsonResponse = array(
                        "statusCode" => 9,
                        "errors"     => array(),
                        "message"    => "Access denied.",
                    );
                return response()->json(parseResponse($jsonResponse), 401);
            }
            $userId         = $userData['u_id'];
            $securityToken  = $userData['securityToken'];
            
            $requestLogId = User::requestLogData($userId,'user/updateUserProfile',$_POST,array_merge($getArr,$_GET),$_FILES,$request->header());
            
            $xDeviceId      = $request->header('X-Device-ID');
            $xApiKey        = $request->header('X-API-Key');

            if($xApiKey != env('USER_X_API_KEY')){
                $jsonResponse = [
                    "statusCode" => 8,
                    "errors"     => array(),
                    "message"    => "Please update your application.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,4);
                return response()->json(parseResponse($jsonResponse), 400);
            }
            
            // validate the info, create rules for the inputs
            $rules = [
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $messages = $validator->errors()->all();
                $jsonResponse = [
                    "statusCode" => 2,
                    "errors"     => $messages,
                    "message"    => isset($messages['0']) ? $messages['0'] :  "Please, Fill all the mendotary detail.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,5);
                return response()->json(parseResponse($jsonResponse), 400);
            }
            
            $name           = $request->input('name');
            $desc        = $request->input('description');
            $isNotificationOn  = $request->input('isNotificationOn');
            
            $updateUserData = array(
                    'u_modified_date'   => $this->time,
                );
            if(!is_null($name)){
                $updateUserData['u_name'] = $name;
            }
           
            if(!is_null($desc)){
                $updateUserData['u_desc'] = $desc;
            }
            
            if(!is_null($isNotificationOn)){
                $updateUserData['u_is_push_notification'] = $isNotificationOn == 1 ? '1' : '0';
            }
                    
            if($request->hasFile('profileImage') && $request->file('profileImage')->isValid()){

                $file = $request->file('profileImage');

                $image      = Image::make($file->getRealPath())->encode('jpg', 75);
                $thumbImage = Image::make($file->getRealPath())->resize(300, 300, function ($constraint) {
                                        $constraint->aspectRatio();
                                        $constraint->upsize();
                                    })->encode('jpg', 75);

                $file_path       = '/users/'.$userId.'/';
                $image_file_name = $userId."_".  str_random(16)."_".$this->time.'.jpg'; 

                $s3 = Storage::disk('s3');
                $isImageUploaded = $s3->put($file_path.$image_file_name,(string) $image, 'public');
                $isImageThumbUploaded = $s3->put($file_path.'thumb_'.$image_file_name, (string) $thumbImage, 'public');
                
                $imageData = getUserImage($userData['u_image_data']);
                if($isImageUploaded && $isImageThumbUploaded){
                    $url = Storage::disk('s3')->url(trim($file_path.$image_file_name,'/'));
                    list($width, $height)           = getimagesize($url);
                    $imageData['main']['url']       = $url;
                    $imageData['main']['width']     = $width;
                    $imageData['main']['height']    = $height;
                    
                    $url = Storage::disk('s3')->url(trim($file_path.'thumb_'.$image_file_name,'/'));
                    list($width, $height)           = getimagesize($url);
                    $imageData['thumb']['url']       = $url;
                    $imageData['thumb']['width']     = $width;
                    $imageData['thumb']['height']    = $height;
                    
                    $updateUserData['u_image_data']  = serialize($imageData);
                }                
            }

            
            $isUserUpdated = DB::table('a_users')->where('u_id',$userId)->where('u_status',1)->update($updateUserData);
            if ($isUserUpdated) {
                
                $userData = (array) DB::table('a_users')
                    ->where('u_id', $userId)
                    ->where('u_status', '1')->first();  
                $userData = getUserResp(parseResponse($userData));
                
                $jsonResponse = [
                        "statusCode"        => "1",
                        "errors"            => [],
                        "message"           => "Success.",
                    ];
                $jsonResponse["data"] = [
                        "profile"  => $userData,
                    ];
                User::responseLogData($requestLogId,$jsonResponse);
                return response()->json(parseResponse($jsonResponse), 200);                
            } else {
                $jsonResponse = array(
                    "statusCode" => "2",
                    "errors"     => array(),
                    "message"    => "There are something problem with update your information. Please, try again.",
                );
                User::responseLogData($requestLogId,$jsonResponse,5);
                return response(parseResponse($jsonResponse), 200)->header('Content-Type', "json");
            }
        
        }catch (\Exception $e) {
            $jsonResponse = array(
                    "statusCode" => "2",
                    "errors"     => [$e->getMessage(),"Line number : ".$e->getLine(),"Whole error : ".$e->getTraceAsString()],
                    "message"    => (env('APP_SHOW_EXCEPTION_MSG',false) == true) ? $e->getMessage() : "There are problem with passing your request. Please, try again.",
            );
            User::responseLogData($requestLogId,$jsonResponse,1);
            return response()->json(parseResponse($jsonResponse), 500);
        }
    }
    
    public function getUserProfile(Request $request) {
        $requestLogId = 0;
        try{
            $xSessionID      = $request->header('X-Session-ID','');
            $userData        = User::checkAccessToken($xSessionID);
            $getArr = [];
            if(!checkArr($userData)){
                $requestLogId = User::requestLogData('0','user/getUserProfile',$_POST,array_merge($getArr,$_GET),$_FILES,$request->header());
                $jsonResponse = array(
                        "statusCode" => 9,
                        "errors"     => array(),
                        "message"    => "Access denied.",
                    );
                return response()->json(parseResponse($jsonResponse), 401);
            }
            $userId         = $userData['u_id'];
            $securityToken  = $userData['securityToken'];
            
            $requestLogId = User::requestLogData($userId,'user/getUserProfile',$_POST,array_merge($getArr,$_GET),$_FILES,$request->header());
            
            $xDeviceId      = $request->header('X-Device-ID');
            $xApiKey        = $request->header('X-API-Key');

            if($xApiKey != env('USER_X_API_KEY')){
                $jsonResponse = [
                    "statusCode" => 8,
                    "errors"     => array(),
                    "message"    => "Please update your application.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,4);
                return response()->json(parseResponse($jsonResponse), 400);
            }
            
            
            // validate the info, create rules for the inputs
            $rules = [];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $messages = $validator->errors()->all();
                $jsonResponse = [
                    "statusCode" => "2",
                    "errors" => $messages,
                    "message" => isset($messages['0']) ? $messages['0'] :  "Please, Fill all the mendotary detail.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,5);
                return response()->json(parseResponse($jsonResponse), 400);
            }
            
            $otherUserId                = $request->input('otherUserId',$userId);
            
            $userData = (array) DB::table('a_users')
                ->where('u_id', $otherUserId)
                ->where('u_status', 1)->first();  

            if (checkArr($userData)) {
                $userData = getUserResp(parseResponse($userData));
                $jsonResponse = [
                        "statusCode"        => "1",
                        "errors"            => [],
                        "message"           => "Success.",
                    ];
                $jsonResponse["data"] = [
                        "profile"  => $userData,
                    ];
                User::responseLogData($requestLogId,$jsonResponse);
                return response()->json(parseResponse($jsonResponse), 200);
            } else {
                $jsonResponse = array(
                    "statusCode" => "2",
                    "errors"     => array(),
                    "message"    => "No user profile found. Please, try again.",
                );
                User::responseLogData($requestLogId,$jsonResponse,5);
                return response(parseResponse($jsonResponse), 200)->header('Content-Type', "json");
            }
        }catch (\Exception $e) {
            $jsonResponse = array(
                    "statusCode" => "2",
                    "errors"     => [$e->getMessage(),"Line number : ".$e->getLine()],
                    "message"    => (env('APP_SHOW_EXCEPTION_MSG',false) == true) ? $e->getMessage() : "There are problem with passing your request. Please, try again.",
            );
            User::responseLogData($requestLogId,$jsonResponse,1);
            return response()->json(parseResponse($jsonResponse), 500);
        }
    }
    
    
    public function changePassword(Request $request) {
        $requestLogId = 0;
        try{
            $xSessionID      = $request->header('X-Session-ID','');
            $userData        = User::checkAccessToken($xSessionID);
            $getArr = [];
            if(!checkArr($userData)){
                $requestLogId = User::requestLogData('0','user/changePassword',$_POST,array_merge($getArr,$_GET),$_FILES,$request->header());
                $jsonResponse = array(
                        "statusCode" => 9,
                        "errors"     => array(),
                        "message"    => "Access denied.",
                    );
                return response()->json(parseResponse($jsonResponse), 401);
            }
            $userId         = $userData['u_id'];
            $securityToken  = $userData['securityToken'];
            
            $requestLogId = User::requestLogData($userId,'user/changePassword',$_POST,array_merge($getArr,$_GET),$_FILES,$request->header());
            
            $xDeviceId      = $request->header('X-Device-ID');
            $xApiKey        = $request->header('X-API-Key');

            if($xApiKey != env('USER_X_API_KEY')){
                $jsonResponse = [
                    "statusCode" => 8,
                    "errors" => array(),
                    "message" => "Please update your application.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,4);
                return response()->json(parseResponse($jsonResponse), 400);
            }
            
            // validate the info, create rules for the inputs
            $rules = [
                'oldPassword'   => 'required',
                'newPassword'   => 'required|min:3',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $messages = $validator->errors()->all();
                $jsonResponse = [
                    "statusCode" => "2",
                    "errors" => $messages,
                    "message" => isset($messages['0']) ? $messages['0'] :  "Please, Fill all the mendotary detail.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,5);
                return response()->json(parseResponse($jsonResponse), 400);
            }
            
            $oldpassword    = $request->input('oldPassword','');
            $newpassword    = $request->input('newPassword','');

            if (Hash::check($oldpassword, $userData['u_password'])) {

                $updateData = array(
                    'u_password' => Hash::make($newpassword),
                    'u_modified_date' => $this->time,
                );

                $userUpdated = DB::table('a_users')
                        ->where('u_id', $userId)
                        ->update($updateData);

                if($userUpdated){
                    $jsonResponse = array(
                        "statusCode" => "1",
                        "errors"     => array(),
                        "message" => "Your password has been updated successfully.",
                    );
                    User::responseLogData($requestLogId,$jsonResponse);
                    return response()->json(parseResponse($jsonResponse), 200);
                }   
            } else {
                $jsonResponse = array(
                    "statusCode" => "2",
                    "errors"     => array(),
                    "message" => "That password cannot be found. Please try again.",
                );
                User::responseLogData($requestLogId,$jsonResponse,5);
                return response($jsonResponse, 200)->header('Content-Type', "json");
            }

            $jsonResponse = array(
                "statusCode" => "2",
                "errors"     => array(),
                "message"    => "Somthing Wrong Here! Please try again.",
            );
            User::responseLogData($requestLogId,$jsonResponse,5);
            return response($jsonResponse, 200)->header('Content-Type', "json");
        }catch (\Exception $e) {
            $jsonResponse = array(
                    "statusCode" => "2",
                    "errors"     => [$e->getMessage(),"Line number : ".$e->getLine()],
                    "message"    => (env('APP_SHOW_EXCEPTION_MSG',false) == true) ? $e->getMessage() : "There are problem with passing your request. Please, try again.",
            );
            User::responseLogData($requestLogId,$jsonResponse,1);
            return response()->json(parseResponse($jsonResponse), 500);
        }
    }
    
    
    
    
    // converted
    public function sendSupportEmail(Request $request) {
        $token          = $request->input('token','');
        $userData        = User::checkAccessToken($token);
        if(!checkArr($userData)){
            $getArr = [];
            User::requestLogData('-1','user/sendSupportEmail',$_POST,$getArr,$_FILES);
            
            $jsonResponse = array(
                    "statusCode" => "9",
                    "errors"     => array(),
                    "message"    => "Access denied.",
                );
            return response()->json(parseResponse($jsonResponse), 401);
        }
        $userId = $userData['u_id'];
        
        $getArr = [];
        User::requestLogData($userId,'user/sendSupportEmail',$_POST,$getArr,$_FILES);
        
        // validate the info, create rules for the inputs
        $rules = array(
            'email'     => 'required|email',
            'subject'   => 'required',
            'body'      => 'required',
        );
        
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $jsonResponse = array(
                "statusCode" => "2",
                "errors" => $messages,
                "message" => isset($messages['0']) ? $messages['0'] :  "Please, Fill all the mendotary detail.",
            );
            return response()->json(parseResponse($jsonResponse), 200);
        }
        
        $latitude   = $request->input('latitude','0');
        $longitude  = $request->input('longitude','0');
        $email      = $request->input('email');
        $subject    = $request->input('subject');
        $body       = $request->input('body');
        
        $insertData = [
                'support_email_u_id'            => $userId,
                'support_email_email'           => $email,
                'support_email_subject'         => $subject,
                'support_email_body'            => $body,
                'support_email_latitude'        => $latitude,
                'support_email_longitude'       => $longitude,
                'support_email_created_date'    => $this->time,
                'support_email_status'          => '1',
            ];
        $supportId = DB::table('a_support_emails')->insertGetId($insertData);
        
        
        $emailData = ['email' => $email,'subject' => $subject,'body' => $body];
        try{
            Mail::send('emails.supportemail', $emailData, function ($m) use($userData,$email,$subject) {
                $m->from('kp@messapps.com', 'GTE Support Team');
                $m->replyTo($email, $userData['u_name']);
                $m->to('kp@messapps.com', "GTE Support Team")->subject('GTE support request: '.$subject);
            });
        }catch(\Exception $e){
            $jsonResponse = array(
                    "statusCode" => "2",
                    "errors"     => [$e->getMessage()],
                    "message"    => (env('APP_SHOW_EXCEPTION_MSG',false) == true) ? $e->getMessage() : "There is a problem delivering the email.",
                );
            return response()->json(parseResponse($jsonResponse), 500);
        }

        $jsonResponse = array(
            "statusCode" => "1",
            "errors" => array(),
            "message" => "Success.",
        );
        return response()->json(parseResponse($jsonResponse), 200);
    
    }
    
    
    
    function registerPhoneNumber(Request $request){
        
        $requestLogId = 0;
        try{
            $xSessionID      = $request->header('X-Session-ID','');
            $userData        = User::checkAccessToken($xSessionID,true,false);
            $getArr = [];
            if(!checkArr($userData)){
                $requestLogId = User::requestLogData('0','user/registerPhoneNumber',$_POST,array_merge($getArr,$_GET),$_FILES,$request->header());
                $jsonResponse = array(
                        "statusCode" => 9,
                        "errors"     => array(),
                        "message"    => "Access denied.",
                    );
                return response()->json(parseResponse($jsonResponse), 401);
            }
            $userId         = $userData['u_id'];
            $securityToken  = $userData['securityToken'];
            
            $requestLogId = User::requestLogData($userId,'user/registerPhoneNumber',$_POST,array_merge($getArr,$_GET),$_FILES,$request->header());
            
            $xDeviceId      = $request->header('X-Device-ID');
            $xApiKey        = $request->header('X-API-Key');

            if($xApiKey != env('USER_X_API_KEY')){
                $jsonResponse = array(
                    "statusCode" => "8",
                    "errors" => array(),
                    "message" => "Please, update your app.",
                );
                User::responseLogData($requestLogId,$jsonResponse,4);
                return response()->json(parseResponse($jsonResponse), 400);
            }
            
            // validate the info, create rules for the inputs
            $rules = [
                'phone'   => 'required',
                'countryCode'   => 'required',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $messages = $validator->errors()->all();
                $jsonResponse = [
                    "statusCode" => "2",
                    "errors" => $messages,
                    "message" => isset($messages['0']) ? $messages['0'] :  "Please, Fill all the mendotary detail.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,5);
                return response()->json(parseResponse($jsonResponse), 400);
            }
            
            $phone         = $request->input('phone','');
            $countryCode    = $request->input('countryCode','1');

            if($phone == "" || $countryCode == ""){
                $jsonResponse = [
                        "statusCode" => "2",
                        "errors" => [],
                        "message" => "Try again, Enter valid phone number with country code.",
                    ];
                return response(parseResponse($jsonResponse), 200)->header('Content-Type', "json");
            }

            if($userData['u_phone'] == $phone && $userData['u_phone_country_code'] == $countryCode){
                $jsonResponse = [
                        "statusCode" => "2",
                        "errors" => [],
                        "message" => "Your number is already verified.",
                    ];
                return response(parseResponse($jsonResponse), 200)->header('Content-Type', "json");
            }
            
            $numberExist = (array) DB::table('a_users as u')->where("u.u_id", "<>", $userId)->where("u.u_phone", $phone)->where("u.u_phone_country_code", $countryCode)->whereNotIn("u.u_status", ['0','9'])->first();
            
            if(is_array($numberExist) && count($numberExist) > 0){
                $jsonResponse = [
                        "statusCode" => "2",
                        "errors" => [],
                        "message" => "This number is already linked to another user, please use a different one.", 
                    ];
                return response(parseResponse($jsonResponse), 200)->header('Content-Type', "json");
            }
    
            $jsonResponse = User::sendVerificationPhone($userId, $phone, $countryCode);
            
            User::responseLogData($requestLogId,$jsonResponse,5);
            return response($jsonResponse, 200)->header('Content-Type', "json");
        }catch (\Exception $e) {
            $jsonResponse = array(
                    "statusCode" => "2",
                    "errors"     => [$e->getMessage(),"Line number : ".$e->getLine()],
                    "message"    => (env('APP_SHOW_EXCEPTION_MSG',false) == true) ? $e->getMessage() : "There are problem with passing your request. Please, try again.",
            );
            User::responseLogData($requestLogId,$jsonResponse,1);
            return response()->json(parseResponse($jsonResponse), 500);
        }
    }
    
    public function getUserNotificationHistoryList(Request $request) {
        $requestLogId = 0;
        try{
            $xSessionID      = $request->header('X-Session-ID','');
            $userData        = User::checkAccessToken($xSessionID);
            $getArr = [];
            if(!checkArr($userData)){
                $requestLogId = User::requestLogData('0','user/getUserProfile',$_POST,array_merge($getArr,$_GET),$_FILES,$request->header());
                $jsonResponse = array(
                        "statusCode" => 9,
                        "errors"     => array(),
                        "message"    => "Access denied.",
                    );
                return response()->json(parseResponse($jsonResponse), 401);
            }
            $userId         = $userData['u_id'];
            $securityToken  = $userData['securityToken'];
            
            $requestLogId = User::requestLogData($userId,'user/getUserProfile',$_POST,array_merge($getArr,$_GET),$_FILES,$request->header());
            
            $xDeviceId      = $request->header('X-Device-ID');
            $xApiKey        = $request->header('X-API-Key');

            if($xApiKey != env('USER_X_API_KEY')){
                $jsonResponse = [
                    "statusCode" => 8,
                    "errors"     => array(),
                    "message"    => "Please update your application.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,4);
                return response()->json(parseResponse($jsonResponse), 400);
            }
            
            
            // validate the info, create rules for the inputs
            $rules = [];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $messages = $validator->errors()->all();
                $jsonResponse = [
                    "statusCode" => "2",
                    "errors" => $messages,
                    "message" => isset($messages['0']) ? $messages['0'] :  "Please, Fill all the mendotary detail.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,5);
                return response()->json(parseResponse($jsonResponse), 400);
            }
            
            $otherUserId                = $request->input('otherUserId',$userId);
        
            
            $pageNo         = $request->input('pageNo');
            $maxId       = $request->input('maxId','');
            
            if (!is_numeric($pageNo) || $pageNo < 1){
                $pageNo = 1;
            }
            $limit              = 20;
            $pageStartLimit	= ($pageNo - 1) * $limit;
            
            if (!is_numeric($maxId) || $maxId < 1) {
                $maxId = DB::table('a_notification_histories')->where('nt_status', "<>",9)->max('nt_id');
                if (!is_numeric($maxId) || $maxId < 1) {
                    $maxId = 0;
                }
            }

            $qry = "SELECT
                        nt.*,u.*,jobCon.*,job.*,client.u_name as clientName
                    FROM
                        a_notification_histories as nt
                    JOIN
                        a_users as u ON nt.nt_gen_by_id = u.u_id and u.u_status = '1'
                    LEFT JOIN
                        a_job_contractors as jobCon ON job_con_id = nt_content_id AND job_con_status != 9
                    LEFT JOIN
                        a_jobs as job ON IF(nt_content_type = 'jobId', job_id = nt_content_id,job_id = job_con_job_id  ) AND job_status != 9 AND nt.nt_content_id = rvw_id and rvw_status = '1'
                    WHERE
                        (
                            (
                                nt_gen_for_type = 'userId' AND
                                nt_gen_for_id 	= '".$userId."'
                            )
                        ) AND
                        nt_status 	= '1'
                    ORDER BY
                        nt_id DESC
                    LIMIT
                        ".$pageStartLimit.", ".$limit."
                ";
            $dataList = DB::select($qry);
            $dataList = parseResponse($dataList);
            $newList = [];
            foreach ($dataList as $key => $value){
                $temp = [
                    'noteId' => $value['nt_id'],
                    'noteType' => $value['nt_type'],
                    'noteContentType'   => $value['nt_content_type'],
                    'noteContentId'     => $value['nt_content_id'],
                    'notecreatedDate'   => $value['nt_created_date'],
                    'jobData'           => getJobResp($value),
                ];
                if($value['nt_type'] == "jobRequestToClient"){
                    $temp['noteHeadText'] = "New application for a \"".$value['clientName']."\" position";
                    $temp['noteBodyText'] = $value['u_name']." just applied to job you've created.";
                }else if($value['nt_type'] == "jobRequestToExecutive"){
                    $temp['noteHeadText'] = "New application for a \"".$value['clientName']."\" position";
                    $temp['noteBodyText'] = $value['u_name']." just applied to job you've created.";
                }else if($value['nt_type'] == "jobRejectedByClient" || $value['nt_type'] == "jobRejectedByExecutive"){
                    $temp['noteHeadText'] = $value['clientName'];
                    $temp['noteBodyText'] = "Hi! Unfortunately your application for this job has been declined. Don't give up\n- explore \"All jobs\" tab to see more position!";
                }else if($value['nt_type'] == "jobApprovedByExecutive"){
                    $temp['noteHeadText'] = $value['clientName'];
                    $temp['noteBodyText'] = "Hi! You've been choosen for a product promoter job which you have applied for.\nYou can see the details in \"My jobs\" tab.\nCongrats!";
                }else{
                    continue;
                }
              //  $notificationData = getNoticationResp(parseResponse($value));
                $notificationData['tripData'] = [];
              //  $notificationData['requestTripData'] = [];
                if($value['nt_content_type'] == "requestTripId"){
                   // $notificationData['requestTripData'] = getRequestTripResp(parseResponse($value));

                    // i have changed requestTripData -> tripData because Chirage used tripData variable instad of requestTripData. and nolan get null -> null in list
                     $notificationData['tripData'] = getRequestTripResp(parseResponse($value));
                }

                if($value['nt_content_type'] == "tripId"  || $value['nt_content_type'] == "reviewId"){
                    $notificationData['tripData'] = getTripResp(parseResponse($value));
                }

    //            if($value['nt_content_type'] == "reviewId"){
    //                $tripId = $value['rvw_trp_id'];
    //                $tripData = (array) DB::table('a_trips')
    //                    ->where('trp_id', $tripId)
    //                    ->first();
    //                $notificationData['tripData'] = getTripResp(parseResponse($tripData));
    //            }
                $generateUserData = getUserResp(parseResponse($value),'2');
                $notificationData['generateUserData'] = $generateUserData;

                $newList[] = $notificationData;
            }
        //    dd($dataList);
            
            $jsonResponse = [
                    "statusCode"        => "1",
                    "errors"            => [],
                    "message"           => "Success.",
                ];
            $jsonResponse["data"] = [
                    'notificationList'       => $newList,
                    'maxId'=> $maxDataDate,
                ];
            User::responseLogData($requestLogId,$jsonResponse);

            $jsonResponse = array(
                "statusCode" => "1",
                "errors"     => array(),
                "message"    => "Success.",
                
            );
            User::responseLogData($requestLogId,$jsonResponse);
            return response(parseResponse($jsonResponse), 200)->header('Content-Type', "json");     
        }catch (\Exception $e) {
            $jsonResponse = array(
                    "statusCode" => "2",
                    "errors"     => [$e->getMessage(),"Line number : ".$e->getLine()],
                    "message"    => (env('APP_SHOW_EXCEPTION_MSG',false) == true) ? $e->getMessage() : "There are problem with passing your request. Please, try again.",
            );
            User::responseLogData($requestLogId,$jsonResponse,1);
            return response()->json(parseResponse($jsonResponse), 500);
        }
    }
    
    
// END OF USER CLASS
}
