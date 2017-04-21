<?php

namespace App;
use DB;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use JWTAuth;
use JWTFactory;
use Edujugon\PushNotification\PushNotification;
use Tymon\JWTAuth\Exceptions\JWTException;
use Mail;
class User extends Authenticatable
{
    use Notifiable;

    /**
//    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'a_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['u_first_name', 'u_email', 'u_password'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['u_password', 'remember_token','u_real_password'];
    
    public $timestamps = false;
    
    protected $primaryKey = 'u_id';
    
    public static function checkAdminSession() {  //dd( Auth::user());
        if (Auth::check()) {
            return true;
        }
        return true;
    }

    public function getAuthIdentifier() {
        return $this->getKey();
    }

    public function getAuthPassword() {
        return $this->u_password;
    }
    
    public static function requestLogData($userId,$api_function,$logPostArr=[],$logGetArr=[],$logFileArr=[],$logHeaderArr=[]){
        if(env('LOG_REQUEST_ON','false') == "true"){
                $arr = array(
                                'req_api_name'		=> $api_function,
                                'req_u_id'		=> (is_numeric($userId) && $userId > 0) ? $userId : 0,
                                'req_post'		=> json_encode($logPostArr),
                                'req_get'			=> json_encode($logGetArr),
                                'req_files'		=> json_encode($logFileArr),
                                'req_header'		=> json_encode($logHeaderArr),
                                'req_response'          => json_encode([]),
                                'req_time'		=> time(),
                                'req_formate_time'	=> date('Y-m-d H:i:s',time()),
                        );
                $insertId = DB::table('a_z_request_logs')->insertGetId($arr);
                if($insertId){
                        return $insertId;
                }
        }
        return false;
    }
    
    public static function responseLogData($responseId,$response = [],$forceFullyLog = 9){
    	if((env('LOG_RESPONSE_ON','false') == "true" || $forceFullyLog < 9 ) && $responseId > 0){
                    $arr = array(
                                    'req_response'		=> json_encode($response),
                                    'req_resp_formate_time'	=> date('Y-m-d H:i:s',time()),
                            );
                    $isTracked = DB::table('a_z_request_logs')->where('req_id',$responseId)->update($arr);
                    if($isTracked){
                            return true;
                    }
    	}
        return false;
    }
    
    public static function getAllConfig(){ 
        try {
            $data = DB::table('a_app_configs')
          //  ->where('cfg_status', 1)
            ->get()->toArray();  // dd($data);
            // this is commented because new version of mongo lib. create issue in convert object to array but without that works
          //  $data = parseResponse((array) $data);
          //  if(checkArr($data)){
                $allConfig = [];
                $data = parseResponse($data);
                foreach ($data as $key => $value){ 
                    $cfgKey     = $value['cfg_key'];
                    $cfgValue   = $value['cfg_value'];
                    
                    $allConfig[$cfgKey] = $cfgValue;
                }
                return $allConfig;
            //}
            return [];
        } catch(\Exception $e){ echo $e->getMessage(); dd($e);
            return [];
        } 
        return [];
    }
    
     public static function checkAccessToken($token,$isParseArr = true,$isActivated = true){ 
        try {
            JWTAuth::setToken($token); 
            $token =JWTAuth::getToken();  
            $user =JWTAuth::decode($token);
            if(isset($user['sub']) && (string) $user['sub'] != ''){
                $checkUserData = DB::table('a_user_device_tokens')
                         ->join('a_users', function ($join) {
                                $join->on('udt_u_id', '=', 'u_id');
                            })
                ->where('udt_status','1')
                ->where('udt_u_id', $user['sub'])
                ->where('udt_security_token', $user['userData']['securityToken']);
                if($isActivated){
                    $checkUserData = $checkUserData->where('u_status', '1');
                }else{
                     $checkUserData = $checkUserData->whereNotIn('u_status', [ '2', '9']);
                }  
                //dd($checkUserData);
                $checkUserData = (array) $checkUserData->first();
               // dd($checkUserData);
                if(checkArr($checkUserData)){
                    if($isParseArr){
                        $checkUserData['securityToken'] = $user['userData']['securityToken'];
                        return parseResponse($checkUserData);
                    }else{
                        return ($checkUserData);
                    }
                }
            }
            return [];
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return [];
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return [];
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            return [];
        } catch(\Exception $e){ //echo $e->getMessage();
            return [];
        } 
        return [];
    }
    
    
    
    public static function getUserDeviceTokenList($userIdList){
            $qry = "SELECT
                              *
                       FROM
                              a_user_device_tokens 
                       JOIN
                              a_users ON u_id = udt_u_id AND u_status = '1' AND u_push_notification_status = 'on'
                       WHERE
                             udt_u_id IN (".$userIdList.") AND
                             udt_push_token != '' AND
                             udt_push_token IS NOT NULL AND
                             udt_status = 1
                ";
            $results = DB::select($qry);
            $userList = parseResponse($results);

            $data = [
                            'iosList'		=> [],
                            'androidList'	=> [],
                            'allList'	=> [],
                    ];
            if(checkArr($userList)){
                foreach($userList as $key => $value){
                    if(strtolower($value['udt_device_type']) == 'ios'){
                            $data['iosList'][] = $value['udt_push_token']; 
                            $data['allList'][] = $value['udt_push_token']; 
                    }else if(strtolower($value['udt_device_type']) == 'android'){
                            $data['androidList'][] = $value['udt_push_token']; 
                            $data['allList'][] = $value['udt_push_token']; 
                    }
                }
            }
            return $data;
    }
    
    public static function sendPushNotification($deviceList,$payLoad,$type = 'all'){
            
            $return['iosFeedback']   = [];
            $return['androidFeedback']   = [];
            if(isset($deviceList['iosList']) && count($deviceList['iosList']) > 0 && ($type == 'ios')){
                $push = new PushNotification('apn');

                $feedback = $push->setMessage($payLoad)
                    ->setDevicesToken($deviceList['iosList'])
                    ->send()
                    ->getFeedback();
                $return['iosFeedback']   = $feedback;
            }else if(isset($deviceList['androidList']) && count($deviceList['androidList']) > 0 && ($type == 'fcm')){
                $push = new PushNotification('fcm');
                
                 $feedback = $push->setMessage($payLoad)
                                ->setApiKey(env('FCM_SERVER_API_KEY'))
                                ->setDevicesToken($deviceList['androidList'])
                                ->send()
                                ->getFeedback();
                
                $return['androidFeedback']   = $feedback;
            }else if(isset($deviceList['allList']) && count($deviceList['allList']) > 0 && ($type == 'all')){
                $push = new PushNotification('fcm');
                
                 $feedback = $push->setMessage($payLoad)
                                ->setApiKey(env('FCM_SERVER_API_KEY'))
                                ->setDevicesToken($deviceList['allList'])
                                ->send()
                                ->getFeedback();
                
                $return['allFeedback']   = $feedback;
            }
            
            return $return;
    }
    
    public static function sendVerificationEmail($userId,$email) {
        
        $emailToken = "";
        $toeknExist = array();
        do {
            $emailToken = str_random(32);
            $toeknExist = (array) DB::table('a_verify_login_contents')
               //     ->where('vr_lgn_type', "email")
                    ->where('vr_lgn_token', $emailToken)
                    ->first();
        } while (checkArr($toeknExist));
        
        $checkUserData = (array) DB::table('a_users')
                    ->where('u_id', $userId)
                    ->first();
        $expiredDate  = time() - (24*3600);
        
        $deleteOldToken = DB::table('a_verify_login_contents')
                ->where('vr_lgn_u_id', $userId)
                ->orWhere('vr_lgn_created_date', "<",$expiredDate)
                ->delete();
        
        $insertData = [
                'vr_lgn_u_id'           => $userId,
                'vr_lgn_token'          => $emailToken,
                'vr_lgn_data'           => $email,
                'vr_lgn_type'           =>  'email',
                'vr_lgn_created_date'   => time(),
            ];
        $tokenId = DB::table('a_verify_login_contents')->insertGetId($insertData);
       
        
        $verificationEmailLink = route('sendEmailVerificationTokenLink',['verificationToken'=>$emailToken,'email' => urlencode($email)]);
        
        $emailData = ['verificationEmailLink' => $verificationEmailLink];
        try{
            Mail::send('emails.verificationEmail', $emailData, function ($m) use($checkUserData,$email) {
                $m->from('kp@messapps.com', 'GTE Support Team');
                $m->to($email, $checkUserData["u_name"])->subject('Verify your GTE email');
            });
            $updateData = [
                    'u_temp_email'      => $email,
                    'u_modified_date'   => time(),
                ];
            $isUserUpdated = DB::table('a_users')
                    ->where('u_id', $userId)
                    ->update($updateData); 
        }catch(\Exception $e){
            $jsonResponse = array(
                    "statusCode" => "2",
                    "errors"     => [$e->getMessage()],
                    "message"    => (env('APP_SHOW_EXCEPTION_MSG',false) == true) ? $e->getMessage() : "There is a problem delivering the email.",
                );
            return $jsonResponse;
        }
        
        $jsonResponse = array(
            "statusCode" => "1",
            "errors" => array(),
            "message" => "Success.",
        );
        return $jsonResponse;
    }
    
    public static function sendVerificationPhone($userId,$phone,$countryCode='1') {
        
        $expiredDate  = time() - (24*3600);
        
        $deleteOldToken = DB::table('a_verify_login_contents')
                ->where('vr_lgn_u_id', $userId)
                ->orWhere('vr_lgn_created_date', "<",$expiredDate)
                ->delete();
        
        $phoneToken = "";
        $toeknExist = array();
        do {
            $phoneToken = mt_rand(1000, 9999); //str_random(4);
            $toeknExist = (array) DB::table('a_verify_login_contents')
             //       ->where('vr_lgn_type', "phone")
                    ->where('vr_lgn_token', $phoneToken)
                    ->first();
        } while (checkArr($toeknExist));
        
        $checkUserData = (array) DB::table('a_users')
                    ->where('u_id', $userId)
                    ->first();
        
        
        $insertData = [
                'vr_lgn_u_id'           => $userId,
                'vr_lgn_token'          => $phoneToken,
                'vr_lgn_data'           => $phone,
                'vr_lgn_cc'             => $countryCode,
                'vr_lgn_type'           => 'phone',
                'vr_lgn_created_date'   => time(),
            ];
        $tokenId = DB::table('a_verify_login_contents')->insertGetId($insertData);
       
        $combinePhone =  ltrim($countryCode.$phone, '+');
        try{
            $twilioClient = new \Twilio\Rest\Client(env('TWILIO_SMS_ACCOUNT_SID'), env('TWILIO_SMS_AUTH_TOKEN'));
          //    $twilioClient = new \Twilio\Rest\Client('SKc539822201fa14f9bfd44d23c19e80e5', 's3tyjbxOcuZUt0hZ6zVGZmxe77ZAk2aW','AC755022c5141a75a2b68dfff6e6903b09');
            $twilioClient->messages->create(
                "+".$combinePhone, 
                array(
                    'from' => env('TWILLO_FROM_NUMBER'),
                    'body' => "GTE: Your verification code is ".$phoneToken,
                )
            );
            $updateData = [
                    'u_temp_phone'                  => $phone,
                    'u_temp_phone_country_code'     => $countryCode,
                    'u_modified_date'               => time(),
                ];
            $isUserUpdated = DB::table('a_users')
                    ->where('u_id', $userId)
                    ->where('u_status', "<>",9)
                    ->update($updateData);
        }catch(\Exception $e){
            $jsonResponse = array(
                    "statusCode" => "2",
                    "errors" => array($e->getMessage()),
                    "message" => "There is a problem delivering the verify sms.",
                );
            return $jsonResponse;
        }
        
        $jsonResponse = array(
            "statusCode" => "1",
            "errors" => array(),
            "message" => "Success.",
        );
        return $jsonResponse;
    }
    
    //
}
