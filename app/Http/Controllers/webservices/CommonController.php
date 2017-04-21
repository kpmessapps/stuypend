<?php

namespace App\Http\Controllers\Webservices;

use Mail;
//use Request;
use Illuminate\Http\Request as Request;
use Validator;
use App\User as User;
use App\Http\Controllers\Controller;
use Auth;
use DB;
use Hash;
use Storage;
use Image;
use Edujugon\PushNotification\PushNotification;
use JWTAuth;
use JWTFactory;
use Tymon\JWTAuth\Exceptions\JWTException;

class CommonController extends Controller {
    
    protected  $time;
    public function __construct()
    {
        $this->time = time();
    }
    
    public function signup(Request $request) {
        $requestLogId = 0;
        try{
            $getArr = [];
            $requestLogId = User::requestLogData('0','common/signup',$_POST,$getArr,$_FILES,$request->header());
            
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
                    'name'          => 'required',
                    'email'         => 'required|email',
                    'password'      => 'required|min:3',
                    'profileImage'  => 'image|mimes:jpeg,png,jpg',
                ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $messages = $validator->errors()->all();
                $jsonResponse = array(
                    "statusCode" => "2",
                    "errors" => $messages,
                    "message" => isset($messages['0']) ? $messages['0'] :  "Please, Fill all the mendotary detail.",
                );
                User::responseLogData($requestLogId,$jsonResponse,4);
                return response()->json(parseResponse($jsonResponse), 400);
            }

            $name           = $request->input('name');
            $email          = $request->input('email');
            $password       = $request->input('password');
            $pushToken      = $request->input('pushToken','');
            $deviceType     = $request->input('deviceType','ios');
            
            $checkUserData = (array) DB::table('a_users')
                    ->where('u_email', $email)
                    ->where('u_status','<>', 9)->first(); 
            if (checkArr($checkUserData)) {
                $jsonResponse = array(
                    "statusCode" => "2",
                    "errors" => array(),
                    "message" => "Email address already exist.",
                );
                User::responseLogData($requestLogId,$jsonResponse,5);
                return response()->json(parseResponse($jsonResponse), 409);
            } 
            
            $userData = [
                    'u_name'              => $name,
                    'u_email'             => $email,
                    'u_password'          => Hash::make($password),
                    'u_image_data'        => serialize([]), 
                    'u_type'              => 4,
                    'u_phone_country_code'=> "",
                    'u_phone'             => "",
                    'u_temp_phone_country_code'=> "",
                    'u_temp_phone'        => "",
                    'u_alert_badge'             => 0,
                    'u_is_push_notification'   => "0",
                    'u_last_login_date'   => $this->time,
                    'u_created_date'      => $this->time,
                    'u_modified_date'     => $this->time,
                    'u_status'            => 0,
                ];

           $userId = DB::table('a_users')->insertGetId($userData);

            if ($userId) {
                
                if($request->hasFile('profileImage') && $request->file('profileImage')->isValid()){

                    $file = $request->file('profileImage');

                    $image      = Image::make($file->getRealPath())->encode('jpg', 75);
                    $thumbImage = Image::make($file->getRealPath())->resize(300, 300, function ($constraint) {
                                            $constraint->aspectRatio();
                                            $constraint->upsize();
                                        })->encode('jpg', 75);

                    $file_path       = '/users/'.$userId.'/';
                    $image_file_name = $userId."_".  str_random(16)."_".time().'.jpg'; 

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
                        
                        $updateUserData = [];
                        $updateUserData['u_image_data']  = serialize($imageData);
                        
                        DB::table('a_users')->where('u_id',$userId)->update($updateUserData);

                    }                
                }

                $deleteOldToken = DB::table('a_user_device_tokens')->where('udt_u_id', $userId)->orWhere('udt_device_id', $xDeviceId)->delete();
                $deleteOldToken = DB::table('a_user_device_tokens')->where('udt_push_token', $pushToken)->where('udt_device_type', $deviceType)->where('udt_push_token','<>','')->delete();
            
                $securityExist = array();
                do {
                    $securityToken = str_random(64);
                    $securityExist = (array) DB::table('a_user_device_tokens')
                            ->where('udt_security_token', $securityToken)
                            ->where('udt_status', '<>', 9)
                            ->first();
                } while (checkArr($securityExist));

                $tokenData = [
                    'udt_u_id'           => $userId,
                    'udt_device_id'      => $xDeviceId,
                    'udt_security_token' => $securityToken,
                    'udt_push_token'     => $pushToken,
                    'udt_device_type'    => $deviceType,
                    'udt_created_date'   => time(),
                    'udt_modified_date'  => time(),
                    'udt_status'         => 1,
                ];
                $tokenId = DB::table('a_user_device_tokens')->insertGetId($tokenData);

                $accessToken = "";
                try {
                    // add a custom claim with a key of `foo` and a value of ['bar' => 'baz']
                   // $payload = JWTFactory::sub((string)$userId)->setTTL(3*365*24*60)->userData(['name' => $name,'deviceType' => $pushToken,'pushToken' => $deviceType])->make();
                    $payload = JWTFactory::sub((string)$userId)->setTTL(3*365*24*60)->userData(['securityToken' => $securityToken])->make();
                    $accessToken = JWTAuth::encode($payload);
                } catch (JWTException $e) {
                    $jsonResponse = [
                        "statusCode" => "2",
                        "errors" => [$e->getMessage()],
                        "message" => $e->getMessage(),
                    ];
                    User::responseLogData($requestLogId,$jsonResponse,1);
                    return response()->json(parseResponse($jsonResponse), 500);
                }catch (\Exception $e) {
                    // something went wrong whilst attempting to encode the token
                    $jsonResponse = [
                        "statusCode" => "2",
                        "errors" => [$e->getMessage()],
                        "message" => $e->getMessage(),
                    ];
                    User::responseLogData($requestLogId,$jsonResponse,1);
                    return response()->json(parseResponse($jsonResponse), 500);
                }

                $userData = (array) DB::table('a_users')
                    ->where('u_id', $userId)
                    ->whereIn('u_status', [0,1])->first(); 
                $userData = getUserResp(parseResponse($userData));
                
                
                $jsonResponse = [
                        "statusCode"        => "1",
                        "errors"            => [],
                        "message"           => "Success.",
                    ];
                $jsonResponse["data"] = [
                        "profile"  => $userData,
                        "session"  => (string) $accessToken,
                    ];
                User::responseLogData($requestLogId,$jsonResponse);
                return response()->json(parseResponse($jsonResponse), 200);
            } else {
                $jsonResponse = [
                    "statusCode" => "2",
                    "errors" => [],
                    "message" => "There are something problem with registering detail. Please, try again.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,1);
                return response()->json(parseResponse($jsonResponse), 400);
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
    
    public function login(Request $request) {
        $requestLogId = 0;
        try{
            $getArr = [];
            $requestLogId = User::requestLogData('0','common/login',$_POST,$getArr,$_FILES,$request->header());
            
            $xDeviceId      = $request->header('X-Device-ID');
            $xApiKey        = $request->header('X-API-Key');
            
            if($xApiKey != env('USER_X_API_KEY')){
                $jsonResponse = [
                    "statusCode" => "8",
                    "errors" => array(),
                    "message" => "Please update your application.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,4);
                return response()->json(parseResponse($jsonResponse), 400);
            }
            
            // validate the info, create rules for the inputs
            $rules = [
                'email'         => 'required|email',
                'password'      => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $messages = $validator->errors()->all();
                $jsonResponse = [
                    "statusCode" => "2",
                    "errors" => $messages,
                    "message" => isset($messages['0']) ? $messages['0'] :  "Please, Fill all the mendotary detail.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,4);
                return response()->json(parseResponse($jsonResponse), 400);
            }

            $username       = $request->input('email');
            $password       = $request->input('password');
            $pushToken      = $request->input('pushToken','');
            $deviceType     = $request->input('deviceType','ios');
            
            
            $checkUserData = (array) DB::table('a_users')
                        ->where('u_email', $username)
                        ->whereIn('u_status', [0,1])
                        ->first();    

            if (checkArr($checkUserData) && Hash::check($password, $checkUserData['u_password'])) {
                $userId = $checkUserData['u_id'];
                $updateData = [
                    'u_last_login_date'    => time(),
                    'u_modified_date'      => time(),
                ];
                $isUserUpdated = DB::table('a_users')->where('u_id',$userId)->where('u_status',1)->update($updateData);

                $deleteOldToken = DB::table('a_user_device_tokens')->where('udt_u_id', $userId)->orWhere('udt_device_id', $xDeviceId)->delete();
                $deleteOldToken = DB::table('a_user_device_tokens')->where('udt_push_token', $pushToken)->where('udt_device_type', $deviceType)->where('udt_push_token','<>','')->delete();

                $securityExist = array();
                do {
                    $securityToken = str_random(64);
                    $securityExist = (array) DB::table('a_user_device_tokens')
                            ->where('udt_security_token', $securityToken)
                            ->where('udt_status', '<>', 9)
                            ->first();
                } while (checkArr($securityExist));

                $tokenData = array(
                    'udt_u_id'              => $userId,
                    'udt_device_id'      => $xDeviceId,
                    'udt_security_token'    => $securityToken,
                    'udt_push_token'      => $pushToken,
                    'udt_device_type'       => $deviceType,
                    'udt_created_date'      => time(),
                    'udt_modified_date'     => time(),
                    'udt_status'            => 1,
                );
                $tokenId = DB::table('a_user_device_tokens')->insertGetId($tokenData);

                $accessToken = "";
                try {
                    // add a custom claim with a key of `foo` and a value of ['bar' => 'baz']
                   // $payload = JWTFactory::sub((string)$userId)->setTTL(3*365*24*60)->userData(['name' => $name,'deviceType' => $pushToken,'pushToken' => $deviceType])->make();
                    $payload = JWTFactory::sub((string)$userId)->setTTL(3*365*24*60)->userData(['securityToken' => $securityToken])->make();
                    $accessToken = JWTAuth::encode($payload);
                } catch (JWTException $e) {
                    // something went wrong whilst attempting to encode the token
                    $jsonResponse = [
                            "statusCode" => "2",
                            "errors"     => [$e->getMessage()],
                            "message"    => (env('APP_SHOW_EXCEPTION_MSG',false) == true) ? $e->getMessage() : "There are problem with creating your session. Please, try again.",
                    ];
                    User::responseLogData($requestLogId,$jsonResponse,1);
                    return response()->json(parseResponse($jsonResponse), 500);
                }catch (\Exception $e) {
                    // something went wrong whilst attempting to encode the token
                    $jsonResponse = [
                            "statusCode" => "2",
                            "errors"     => [$e->getMessage()],
                            "message"    => (env('APP_SHOW_EXCEPTION_MSG',false) == true) ? $e->getMessage() : "There are problem with creating your session. Please, try again.",
                    ];
                    User::responseLogData($requestLogId,$jsonResponse,1);
                    return response()->json(parseResponse($jsonResponse), 500);
                }

                $userData = (array) DB::table('a_users')
                    ->where('u_id', $userId)
                    ->whereIn('u_status', [0,1])->first();  

                $userData = getUserResp(parseResponse($userData));
                
                $jsonResponse = [
                    "statusCode"        => "1",
                    "errors"            => [],
                    "message"           => "Success.",
                ];
                $jsonResponse["data"] = [
                        "profile"  => $userData,
                        "session"  => (string) $accessToken,
                    ];
                User::responseLogData($requestLogId,$jsonResponse);
                return response()->json(parseResponse($jsonResponse), 200);
            }
            $jsonResponse = [
                "statusCode" => "2",
                "errors" => [],
                "message" => "The entered email and password don't match. Please try again.",
            ];
            User::responseLogData($requestLogId,$jsonResponse,9);
            return response()->json(parseResponse($jsonResponse), 401);

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
    
    public function forgotPassword(Request $request) {
        $requestLogId = 0;
        try{
            $getArr = [];
            $requestLogId = User::requestLogData('0','common/forgotPassword',$_POST,$getArr,$_FILES,$request->header());
            
            $xDeviceId      = $request->header('X-Device-ID');
            $xApiKey        = $request->header('X-API-Key');

            if($xApiKey != env('USER_X_API_KEY')){
                $jsonResponse = [
                    "statusCode" => "8",
                    "errors" => [],
                    "message" => "Please update your application.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,4);
                return response()->json(parseResponse($jsonResponse), 400);
            }
        
            // validate the info, create rules for the inputs
            $rules = [
                'email'         => 'required|email',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $messages = $validator->errors()->all();
                $jsonResponse = [
                    "statusCode" => "2",
                    "errors" => $messages,
                    "message" => isset($messages['0']) ? $messages['0'] :  "Please, Fill all the mendotary detail.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,4);
                return response()->json(parseResponse($jsonResponse), 400);
            }

            $email    = $request->input('email');
        
            $checkUserData = (array) DB::table('a_users')
                    ->where('u_email', $email)
                   ->where('u_status', '1')
                    ->first();
            if (!checkArr($checkUserData)) {
                $jsonResponse = [
                    "statusCode" => "2",
                    "errors" => array(),
                    "message" => "We cannot find an account associated with this email.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,8);
                return response()->json(parseResponse($jsonResponse), 200);
            }

            $emailToken = "";
            $toeknExist = [];
            do {
                $emailToken = str_random(32);
                $toeknExist = (array) DB::table('a_reset_tokens')
                        ->where('reset_pass_token', $emailToken)
                        ->first();
            } while (checkArr($toeknExist));

            $userId = $checkUserData['u_id'];
            $expiredDate  = time() - (24*3600);

            $deleteOldToken = DB::table('a_reset_tokens')
                    ->where('reset_pass_created_date', "<",$expiredDate)
                    ->delete();

            $deleteOldToken = DB::table('a_reset_tokens')
                    ->where('reset_pass_u_id', $userId)
                    ->where('reset_pass_type', '1')
                    ->delete();

            $insertData = [
                    'reset_pass_u_id'           => $userId,
                    'reset_pass_token'          => $emailToken,
                    'reset_pass_type'          => '1',
                    'reset_pass_created_date'   => time(),
                ];
            $tokenId = DB::table('a_reset_tokens')->insertGetId($insertData);

            $resetPasswordEmailLink = route('resetEmailPasswordTokenLink',['resetToken'=>$emailToken,'email' => urlencode($email)]);

            $emailData = ['resetPasswordEmailLink' => $resetPasswordEmailLink];
            Mail::send('emails.forgotpassword', $emailData, function ($m) use($checkUserData,$email) {
                $m->from('kp@messapps.com', 'GTE Support Team');
                $m->to($email, $checkUserData["u_name"])->subject('Reset your GTE password');
            });
            
            $jsonResponse = array(
                "statusCode" => 1,
                "errors" => [],
                "message" => "Success.",
            );
            User::responseLogData($requestLogId,$jsonResponse);
            return response()->json(parseResponse($jsonResponse), 200);

        }catch(\Exception $e){
            $jsonResponse = array(
                    "statusCode" => "2",
                    "errors"     => [$e->getMessage(),"Line number : ".$e->getLine(),"Whole error : ".$e->getTraceAsString()],
                    "message"    => (env('APP_SHOW_EXCEPTION_MSG',false) == true) ? $e->getMessage() : "There is a problem delivering the email.",
                );
            User::responseLogData($requestLogId,$jsonResponse,1);
            return response()->json(parseResponse($jsonResponse), 500);
        }

    }
    
    
    public function resetPassword(Request $request) {
        $requestLogId = 0;
        try{    
            $getArr = [];
            $requestLogId = User::requestLogData('0','common/resetPassword',$_POST,$getArr,$_FILES,$request->header());
            
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
                'token'         => 'required',
                'newPassword'   => 'required|min:3'
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $messages = $validator->errors()->all();
                $jsonResponse = [
                    "statusCode" => "2",
                    "errors" => $messages,
                    "message" => isset($messages['0']) ? $messages['0'] :  "Please, Fill all the mendotary detail.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,4);
                return response()->json(parseResponse($jsonResponse), 400);
            }
            
            $email        = $request->input('email');
            $resetToken   = $request->input('token');
            $password     = $request->input('newPassword');

            $expireDate = time()-(24*3600);

            $deleteExpireToken = (array) DB::table('a_reset_tokens')
                    ->where('reset_pass_created_date',"<", $expireDate)
                    ->delete();

            $checkToken = (array) DB::table('a_reset_tokens')
                    ->where('reset_pass_token', $resetToken)
                    ->where('reset_pass_created_date',">", $expireDate)
                    ->first();
            if (!checkArr($checkToken)) {
                $jsonResponse = array(
                    "statusCode" => "2",
                    "errors" => array(),
                    "message" => "Your reset link has been  expired, please re-try.",
                );
                User::responseLogData($requestLogId,$jsonResponse,8);
                return response(parseResponse($jsonResponse), 200)->header('Content-Type', "json");
            }

            $userData = [
                    'u_password'         => Hash::make($password),
                    'u_modified_date'    => time(),  
                ]; 
            $isUserUpdated = DB::table('a_users')->where('u_id',$checkToken['reset_pass_u_id'])->where('u_status',1)->update($userData);
            if($isUserUpdated){

                $deleteToken = DB::table('a_reset_tokens')
                    ->where('reset_pass_token', $resetToken)
                    ->where('reset_pass_created_date',">", $expireDate)
                    ->delete();

                $jsonResponse = [
                        "statusCode" => "1",
                        "errors" => [],
                        "message" => "Success.",
                    ];
                User::responseLogData($requestLogId,$jsonResponse);
                return response(parseResponse($jsonResponse), 200)->header('Content-Type', "json");
            }


            $jsonResponse = array(
                    "statusCode" => "2",
                    "errors" => [],
                    "message" => "There are something problem with reset password. Please, re-try.",
                );
            User::responseLogData($requestLogId,$jsonResponse,8);
            return response(parseResponse($jsonResponse), 200)->header('Content-Type', "json");
        }catch(\Exception $e){
            $jsonResponse = array(
                    "statusCode" => "2",
                    "errors"     => [$e->getMessage(),"Line number : ".$e->getLine(),"Whole error : ".$e->getTraceAsString()],
                    "message"    => (env('APP_SHOW_EXCEPTION_MSG',false) == true) ? $e->getMessage() : "There is a problem with passing your request.",
                );
            User::responseLogData($requestLogId,$jsonResponse,1);
            return response()->json(parseResponse($jsonResponse), 500);
        }
    }
    
    public function verifyPhoneToken(Request $request) {
        $requestLogId = 0;
        try{   
            $getArr = [];
            $requestLogId = User::requestLogData('0','common/verifyPhoneToken',$_POST,$getArr,$_FILES,$request->header());
            
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
                'phoneToken'     => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $messages = $validator->errors()->all();
                $jsonResponse = [
                    "statusCode" => "2",
                    "errors" => $messages,
                    "message" => isset($messages['0']) ? $messages['0'] :  "Please, Fill all the mendotary detail.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,4);
                return response()->json(parseResponse($jsonResponse), 400);
            }
            
            $phoneToken = $request->input('phoneToken');

            $expireDate = time()-(24*3600);
            $deleteToken = DB::table('a_verify_login_contents')
                    ->where('vr_lgn_created_date',"<", $expireDate)
                    ->delete();
            $checkToken = (array) DB::table('a_verify_login_contents')
                    ->where('vr_lgn_token', $phoneToken)
                    ->where('vr_lgn_type', 'phone')
                    ->where('vr_lgn_created_date',">", $expireDate)
                    ->first();
            if(!checkArr($checkToken)) {
                $jsonResponse = array(
                    "statusCode" => "2",
                    "errors" => array(),
                    "message" => "Your verification link has been expired, please re-try.",
                );
                User::responseLogData($requestLogId,$jsonResponse,8);
                return response(parseResponse($jsonResponse), 200)->header('Content-Type', "json");
            }

            $userData = (array) DB::table('a_users')
                    ->where('u_id', $checkToken['vr_lgn_u_id'])
                    ->whereIn('u_status', ['0','1'])
                    ->first();
            if(!checkArr($userData)) {
                $jsonResponse = array(
                    "statusCode" => "2",
                    "errors" => array(),
                    "message" => "Oops, your account not longer available with GTE.",
                );
                User::responseLogData($requestLogId,$jsonResponse,8);
                return response(parseResponse($jsonResponse), 200)->header('Content-Type', "json");
            }
            $updateData = [
                    'u_phone'           => $checkToken['vr_lgn_data'],
                    'u_phone_country_code'           => $checkToken['vr_lgn_cc'],
                    'u_temp_phone'           => "",
                    'u_temp_phone_country_code'           => "",
                    'u_modified_date'   => time(),  
                ]; 
            if($userData['u_status'] == '0'){
                $updateData['u_status'] = 1;
            }

            $isUserUpdated = DB::table('a_users')->where('u_id',$checkToken['vr_lgn_u_id'])->whereIn('u_status', ['0','1'])->update($updateData);
            if($isUserUpdated){

                $deleteToken = DB::table('a_verify_login_contents')
                    ->where('vr_lgn_u_id', $checkToken['vr_lgn_u_id'])
                    ->where('vr_lgn_type', 'phone')
                    ->delete();

                $jsonResponse = array(
                        "statusCode" => "1",
                        "errors" => array(),
                        "message" => "Success.",
                    );
                User::responseLogData($requestLogId,$jsonResponse);
                return response(parseResponse($jsonResponse), 200)->header('Content-Type', "json");
            }


            $jsonResponse = array(
                    "statusCode" => "2",
                    "errors" => array(),
                    "message" => "There are something problem with sending SMS. Please, re-try.",
                );
            User::responseLogData($requestLogId,$jsonResponse,8);
            return response(parseResponse($jsonResponse), 200)->header('Content-Type', "json");
        }catch(\Exception $e){
            $jsonResponse = array(
                    "statusCode" => "2",
                    "errors"     => [$e->getMessage(),"Line number : ".$e->getLine(),"Whole error : ".$e->getTraceAsString()],
                    "message"    => (env('APP_SHOW_EXCEPTION_MSG',false) == true) ? $e->getMessage() : "There is a problem with passing your request.",
                );
            User::responseLogData($requestLogId,$jsonResponse,1);
            return response()->json(parseResponse($jsonResponse), 500);
        }
    }
    
    public function tempRequestLogList($limit=20,$pageno=1) {
        
        $limit = (int) $limit;
        $data = DB::table('a_z_request_logs')->orderBy('req_id','desc')->skip($limit*($pageno-1))->take($limit)->get()->toArray();
        $data =parseResponse($data);
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }
    
    
    
    public function pushApnsTest($pushToken) {
        
        $driverUserId = 6;
        
        $tokenList       = User::getUserDeviceTokenList($driverUserId);
                    $payLoad  = [ //p19
                            'notification' => [
                                    'title' => 'Test has canceled booking.',
                                    'body'  => "Unfortunately one of your passenger has changed his mind and canceled the booking.",
                                    'sound' => 'default',
                                    'content_available' => true,
                                    'type'   => 'cancelReservation',
                                    'tripId' => 49,
                                    'userId' => 6,
                                    'bookingId' => 36,
                                ],
                            'data' => [
                                    'title' => 'Test has canceled booking.',
                                    'body'  => "Unfortunately one of your passenger has changed his mind and canceled the booking.",
                                    'sound' => 'default',
                                    'content_available' => true,
                                    'type'   => 'cancelReservation',
                                    'tripId' => 49,
                                    'userId' => 6,
                                    'bookingId' => 36,
                                ]
                            ];
//                    $payLoad  = [
//                            'notification' => [
//                                    'title' =>'TEST ',
//                                    'body'  => "Test data.",
//                                    'sound' => 'default',
//                                    'content_available' => true
//                                ],
//                            'data' => [
//                                    'type'   => 'testType',
//                                    'tripId' => '1'
//                                ]
//                            ];
                    $feedback = User::sendPushNotification($tokenList,$payLoad,'all');
                    
     /*   $payLoad  = [
                            'notification' => [
                                    'title' =>'TEST ',
                                    'body'  => "Test data.",
                                    'sound' => 'default',
                                    'content_available' => true
                                ],
                            'data' => [
                                    'type'   => 'testType',
                                    'tripId' => '1'
                                ]
                            ];
        
            $push = new PushNotification('fcm');
       */         
//                 $feedback = $push->setMessage($payLoad)
//                    /*     ->setConfig([
//                                'priority' => 'high',
//                                'dry_run' => false,
//                            ])   */
//                                ->setApiKey(env('FCM_SERVER_API_KEY'))
//                                ->setDevicesToken($pushToken)
//                                ->send()
//                                ->getFeedback();
     /*           
        $feedback = $push->setMessage($payLoad)
        ->setConfig([
    'priority' => 'high',
    'dry_run' => false,
]) 
                        ->setApiKey('AIzaSyBKL1FnDKhYkfUCpXFnZQQvm7j008NayxQ')
                         ->setDevicesToken($pushToken)
                         ->send()
                         ->getFeedback();
       */ 
        $jsonResponse = array(
                "statusCode"        => "1",
                "errors"            => array(),
                "message"           => "Success.",
                "feedback"          => $feedback,
            );
        return response(parseResponse($jsonResponse), 200)->header('Content-Type', "json");
    }

}