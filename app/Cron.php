<?php


namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Edujugon\PushNotification\PushNotification;

class Cron 
{
	
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
    protected $fillable = ['u_name', 'u_email', 'u_password'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['u_password'];
	 protected $dates = [];
    
	public $timestamps = false;
    
	protected $primaryKey = 'u_id';
	
   
    // p15-p16-p17 Done // pre notification to driver for trip start
    public static function sendPreNotifyToDriverForTripStart($notifyNumber){
        $arr = array(
                        'req_api_name'		=> 'cron/sendPreNotifyToDriverForTripStart',
                        'req_u_id'		=> 0,
                        'req_post'		=> json_encode([]),
                        'req_get'		=> json_encode(['notifyNumber' => $notifyNumber]),
                        'req_files'		=> "",
                        'req_time'		=> time(),
                        'req_formate_time'	=> date('Y-m-d H:i:s',time()),
                );
        $maxDataId = DB::table('a_trip_pre_notifications')->where('trp_pre_nt_status','1')->max('trp_pre_nt_id');
        if(!is_numeric($maxDataId) || $maxDataId < 1){
            $maxDataId = 0;
        }
        $where = "";
        $minutesBefore = 60;
        if($notifyNumber == "1"){ 
            $minutesBefore = 60;
            $where = " trp_pre_nt_1 > '9' AND trp_pre_nt_1 < '".time()."' AND";
        }else if($notifyNumber == "2"){
            $minutesBefore = 30;
            $where = " trp_pre_nt_2 > '9' AND trp_pre_nt_2 < '".time()."' AND";
        }else if($notifyNumber == "3"){
            $minutesBefore = 15;
            $where = " trp_pre_nt_3 > '9' AND trp_pre_nt_3 < '".time()."' AND";
        }else{
            return [];
        }
        
        $qry = "
                SELECT
                    *
                FROM
                    a_trip_pre_notifications as trpNt
                JOIN 
                    a_trips as trp on trpNt.trp_pre_nt_trp_id = trp.trp_id AND trp_status = 'pending'
                JOIN
                    a_users ON u_id = trp_u_id AND u_status = '1' AND u_push_notification_status = 'on'
                LEFT JOIN
                    a_user_device_tokens ON udt_u_id = u_id AND udt_device_token != '' AND udt_device_token IS NOT NULL AND udt_status = 1
                WHERE
                    ".$where."
                    trp_pre_nt_id < '".$maxDataId."' AND
                    trp_pre_nt_status = '1'
            ";
        $arrRes = array(
                        'req_api_name'		=> 'cron/sendPreNotifyToDriverForTripStart',
                        'req_u_id'		=> 0,
                        'req_post'		=> json_encode([]),
                        'req_get'		=> json_encode(['notifyNumber' => $notifyNumber]),
                        'req_files'		=> "",
                        'req_time'		=> time(),
                        'req_formate_time'	=> date('Y-m-d H:i:s',time()),
                );
        $arrRes['req_files'] = $qry;
        DB::table('a_z_request_logs')->insertGetId($arrRes);
        
        $results = DB::select($qry);
        $notificationList = parseResponse($results);
        
        $tripIdList = [];
        foreach ($notificationList as $key => $value){
            $tripIdList[] = $value['trp_id'];
        }
        
        $updateDataPreNotification = [
                'trp_pre_nt_status' => '2',
                'trp_pre_nt_modified_time' => time()
            ];
        DB::table('a_trip_pre_notifications')
                ->where('trp_pre_nt_status','1')
                ->whereIn('trp_pre_nt_trp_id',$tripIdList)
                ->update($updateDataPreNotification);
        
        $update = [
            'trp_is_pre_notification_start' => '1',
            'trp_modified_date' => time()
        ];
        DB::table('a_trips')->whereIn('trp_id',$tripIdList)->where('trp_is_pre_notification_start','0')->where('trp_status','pending')->where('trp_id',"<",$maxDataId)->update($update);
        
        $log = [];
        foreach ($notificationList as $key => $value){
            $preNotificationId = $value['trp_pre_nt_id'];
            
            if($minutesBefore == 60){
                $notiText = 'a hour';
            }else{
                $notiText = $minutesBefore.' minutes';
            }
            
            $log[$key]['drvId'] = $value['udt_u_id'];
                
            if(strlen($value['udt_device_token']) > 16){
                $tokenList['allList'][] = $value['udt_device_token'];
                $payLoad  = [
                        'data' => [
                                'title' =>'Are you on the way?',
                                'body'  => "Your ride from ".$value['trp_departing_from_address']." to ".$value['trp_arriving_to_address']." Starts in ".$notiText.", are you on going to be on time?",
                                'sound' => 'default',
                                'content_available' => true,
                                'type'   => 'driverPreNotification_'.$minutesBefore,
                                'tripId' => $value['trp_id'],
                                'notifyNo' => $notifyNumber,
                            ]
                        ];
                $payLoad['notification'] = $payLoad['data'];
                $fcmFeedback = User::sendPushNotification($tokenList,$payLoad,'all');   
                $log[$key]['tokenList'] = $tokenList;
            }
            
            $updateDataPreNotification = [
                'trp_pre_nt_modified_time' => time()
            ];
            if($notifyNumber == "1"){ 
                $updateDataPreNotification['trp_pre_nt_1'] = '0';
            }else if($notifyNumber == "2"){
                $updateDataPreNotification['trp_pre_nt_2'] = '0';
            }else if($notifyNumber == "3"){
                $updateDataPreNotification['trp_pre_nt_3'] = '0';
            }

            DB::table('a_trip_pre_notifications')
                    ->where('trp_pre_nt_status','2')
                    ->where('trp_pre_nt_trp_id',$value['trp_id'])
                    ->update($updateDataPreNotification);
        }
        
        $updateDataPreNotification = [
                'trp_pre_nt_status' => '1',
                'trp_pre_nt_modified_time' => time()
            ];
        DB::table('a_trip_pre_notifications')
                ->where('trp_pre_nt_status','2')
                ->whereIn('trp_pre_nt_trp_id',$tripIdList)
                ->update($updateDataPreNotification);
        
        $arr['req_files'] = json_encode($log);
        $insertId = DB::table('a_z_request_logs')->insertGetId($arr);
        $data = [];
      //  $data['tokenList'] = $tokenList;
        return json_encode($data);
    }
    
    public static function sendPreNotifyToPassengerForTripStart($notifyNumber){
        $arr = array(
                        'req_api_name'		=> 'cron/sendPreNotifyToPassengerForTripStart',
                        'req_u_id'		=> 0,
                        'req_post'		=> json_encode([]),
                        'req_get'		=> json_encode(['notifyNumber' => $notifyNumber]),
                        'req_files'		=> "",
                        'req_time'		=> time(),
                        'req_formate_time'	=> date('Y-m-d H:i:s',time()),
                );
        $maxDataId = DB::table('a_trip_pre_notifications')->where('trp_pre_nt_status','1')->max('trp_pre_nt_id');
        if(!is_numeric($maxDataId) || $maxDataId < 1){
            $maxDataId = 0;
        }
        if(!is_numeric($maxDataId) || $maxDataId < 1){
            $maxDataId = 0;
        }
        $where = "";
        $minutesBefore = 60;
        if($notifyNumber == "1"){ 
            $minutesBefore = 60;
            $where = " trp_psngr_pre_nt_1 > '9' AND trp_psngr_pre_nt_1 < '".time()."' AND";
        }else if($notifyNumber == "2"){
            $minutesBefore = 30;
            $where = " trp_psngr_pre_nt_2 > '9' AND trp_psngr_pre_nt_2 < '".time()."' AND";
        }else if($notifyNumber == "3"){
            $minutesBefore = 15;
            $where = " trp_psngr_pre_nt_3 > '9' AND trp_psngr_pre_nt_3 < '".time()."' AND";
        }else{
            return [];
        }
        
        $qry = "
                SELECT
                    *
                FROM
                    a_trip_pre_notifications as trpNt
                JOIN 
                    a_trips as trp on trpNt.trp_pre_nt_trp_id = trp.trp_id AND trp_status = 'pending'
                JOIN
                    a_users ON u_id = trp_u_id AND u_status = '1'
                WHERE
                    ".$where."
                    trp_pre_nt_id < '".$maxDataId."' AND
                    trp_pre_nt_status = '1'
            ";
        $results = DB::select($qry);
        $notificationList = parseResponse($results);
        
        $tripIdList = [];
        foreach ($notificationList as $key => $value){
            $tripIdList[] = $value['trp_id'];
        }
        
        $updateDataPreNotification = [
                'trp_pre_nt_status' => '2',
                'trp_pre_nt_modified_time' => time()
            ];
        DB::table('a_trip_pre_notifications')
                ->where('trp_pre_nt_status','1')
                ->whereIn('trp_pre_nt_trp_id',$tripIdList)
                ->update($updateDataPreNotification);
                
        $update = [
            'trp_is_pre_notification_start' => '1',
            'trp_modified_date' => time()
        ];
        DB::table('a_trips')->whereIn('trp_id',$tripIdList)
                ->where('trp_is_pre_notification_start','0')
                ->where('trp_status','pending')->update($update);
        
        $log = [];
        foreach ($notificationList as $key => $value){
            $preNotificationId = $value['trp_pre_nt_id'];
            
            if($minutesBefore == 60){
                $title = "Your trip starts in 1 hour";
                $notiText = 'Prepare for your trip, we recommend you are at the meeting point at least 5 minutes prior to the departing time.';
            }else{
                $title = "Are you on the way?";
                $notiText = 'Your trip departs in 30 minutes. Make sure you are on time so you\'re not left behind';
            }
            
            $getBookingList = DB::table('a_trip_bookings')
                    ->where('trp_bk_trp_id', $value['trp_id'])
                    ->Where('trp_bk_status', 1)
                    ->get()->toArray();

            $passengerUserIdList = [];
            foreach ($getBookingList as $bookKey => $bookValue){
                $passengerUserIdList[] = $bookValue['trp_bk_u_id'];
            }
            
            $log[$key]['psgnrId'] = $passengerUserIdList;
            if(count($passengerUserIdList) > 0){
                $passengerUserId = implode(',',$passengerUserIdList);
                $tokenList       = User::getUserDeviceTokenList($passengerUserId);
                $payLoad  = [
                        'data' => [
                                'title' =>$title,
                                'body'  => $notiText,
                                'sound' => 'default',
                                'content_available' => true,
                                'type'   => 'passengerPreNotification_'.$minutesBefore,
                                'tripId' => $value['trp_id'],
                            ]
                        ];
                $payLoad['notification'] = $payLoad['data'];
                $fcmFeedback = User::sendPushNotification($tokenList,$payLoad,'all');   
                $log[$key]['tokenList'] = $tokenList;
            }
            $updateDataPreNotification = [
                'trp_pre_nt_modified_time' => time()
            ];
            if($notifyNumber == "1"){ 
                $updateDataPreNotification['trp_psngr_pre_nt_1'] = '0';
            }else if($notifyNumber == "2"){
                $updateDataPreNotification['trp_psngr_pre_nt_2'] = '0';
            }else if($notifyNumber == "3"){
                $updateDataPreNotification['trp_psngr_pre_nt_3'] = '0';
            }

            DB::table('a_trip_pre_notifications')
                    ->where('trp_pre_nt_status','2')
                    ->where('trp_pre_nt_trp_id',$value['trp_id'])
                    ->update($updateDataPreNotification);
        }
        
        $updateDataPreNotification = [
                'trp_pre_nt_status' => '1',
                'trp_pre_nt_modified_time' => time()
            ];
        DB::table('a_trip_pre_notifications')
                ->where('trp_pre_nt_status','2')
                ->whereIn('trp_pre_nt_trp_id',$tripIdList)
                ->update($updateDataPreNotification);
        
        $arr['req_files'] = json_encode($log);
        $insertId = DB::table('a_z_request_logs')->insertGetId($arr);
        $data = [];
      //  $data['tokenList'] = $tokenList;
        return json_encode($data);
    }
   
    //p18 - done
    public static function sendNotifyToDriverOnBoardTrip(){ 
        $arr = array(
                        'req_api_name'		=> 'cron/sendNotifyToDriverOnBoardTrip',
                        'req_u_id'		=> 0,
                        'req_post'		=> json_encode([]),
                        'req_get'		=> json_encode([]),
                        'req_files'		=> "",
                        'req_time'		=> time(),
                        'req_formate_time'	=> date('Y-m-d H:i:s',time()),
                );
        $maxDataId = DB::table('a_trip_pre_notifications')->where('trp_pre_nt_status','1')->max('trp_pre_nt_id');
        if(!is_numeric($maxDataId) || $maxDataId < 1){
            $maxDataId = 0;
        }
        $qry = "
                SELECT
                    trp.*,u.*,udt.*,trpNt.*
                FROM
                     a_trip_pre_notifications as trpNt
                JOIN 
                    a_trips as trp on trpNt.trp_pre_nt_trp_id = trp.trp_id AND trp_status = 'started'
                JOIN
                    a_users as u ON u.u_id = trp_id AND u.u_status = '1' AND u.u_push_notification_status = 'on'
                LEFT JOIN
                    a_user_device_tokens as udt ON udt.udt_u_id = trp_u_id AND udt.udt_device_token != '' AND udt.udt_device_token IS NOT NULL AND udt.udt_status = 1
                WHERE
                    trpNt.trp_pre_nt_driver_on_board > '9' AND
                    trpNt.trp_pre_nt_driver_on_board < '".time()."' AND
                    trpNt.trp_pre_nt_status = '1' AND
                    trpNt.trp_pre_nt_id < '".$maxDataId."'
            ";
        $results = DB::select($qry);
        $notificationList = parseResponse($results);
        
        $tripIdList = [];
        foreach ($notificationList as $key => $value){
            $tripIdList[] = $value['trp_id'];
        }
        
        $updateDataPreNotification = [
                'trp_pre_nt_status' => '2',
                'trp_pre_nt_modified_time' => time()
            ];
        DB::table('a_trip_pre_notifications')
                ->where('trp_pre_nt_status','1')
                ->whereIn('trp_pre_nt_trp_id',$tripIdList)
                ->update($updateDataPreNotification);
        $log = [];
        foreach ($notificationList as $key => $value){
            $log[$key]['drvId'] = $value['udt_u_id'];
            if(strlen($value['udt_device_token']) > 16){
                $tokenList['allList'][] = $value['udt_device_token'];
                $payLoad  = [ //p18
                        'data' => [
                                'title' =>'Is everyone onboard?',
                                'body'  => 'Tap "Yes" if everyone is in a car, tap "No" if someone\'s missing, so you can text him or her.',
                                'sound' => 'default',
                                'content_available' => true,
                                'type'   => 'driverOnBoardP18',
                                'tripId' => $value['trp_id'],
                            ]
                        ];
                $payLoad['notification'] = $payLoad['data'];
                $fcmFeedback = User::sendPushNotification($tokenList,$payLoad,'all');   
                $log[$key]['tokenList'] = $tokenList;
            }
            
            $updateDataPreNotification = [
                'trp_pre_nt_driver_on_board' => '0',
                'trp_pre_nt_modified_time' => time()
            ];
            DB::table('a_trip_pre_notifications')
                    ->where('trp_pre_nt_status','2')
                    ->where('trp_pre_nt_trp_id',$value['trp_id'])
                    ->update($updateDataPreNotification);
        }
        
        $updateDataPreNotification = [
                'trp_pre_nt_status' => '1',
                'trp_pre_nt_modified_time' => time()
            ];
        DB::table('a_trip_pre_notifications')
                ->where('trp_pre_nt_status','2')
                ->whereIn('trp_pre_nt_trp_id',$tripIdList)
                ->update($updateDataPreNotification);
        
        $arr['req_files'] = json_encode($log);
        $insertId = DB::table('a_z_request_logs')->insertGetId($arr);
        $data = [];
      //  $data['tokenList'] = $tokenList;
        return json_encode($data);
    }
    
    
    //p6 OR p12 - done //////////// DONE
    public static function sendNotifyToPassengerOnBoardTripOrCancelTrip(){ 
        $arr = array(
                        'req_api_name'		=> 'cron/sendNotifyToPassengerOnBoardTrip',
                        'req_u_id'		=> 0,
                        'req_post'		=> json_encode([]),
                        'req_get'		=> json_encode([]),
                        'req_files'		=> "",
                        'req_time'		=> time(),
                        'req_formate_time'	=> date('Y-m-d H:i:s',time()),
                );
        $maxDataId = DB::table('a_trip_pre_notifications')->where('trp_pre_nt_status','1')->max('trp_pre_nt_id');
        if(!is_numeric($maxDataId) || $maxDataId < 1){
            $maxDataId = 0;
        }
        /* trpNt.trp_pre_nt_passenger_on_board > '9' AND
                    trpNt.trp_pre_nt_passenger_on_board < '".time()."' AND
                    trpNt.trp_pre_nt_status = '1' AND
                    trpNt.trp_pre_nt_id < '".$maxDataId."' */
        ///// p12  //////////
        $qry = "
                SELECT
                    *
                FROM
                    a_trip_pre_notifications as trpNt
                JOIN 
                    a_trips as trp on trpNt.trp_pre_nt_trp_id = trp.trp_id AND trp_status = 'started'
                JOIN
                    a_users as u ON u.u_id = trp_u_id
                WHERE
                    trp_pre_nt_id < '".$maxDataId."' AND
                    trp_pre_nt_1 = 0 AND
                    trp_pre_nt_2 = 0 AND
                    trp_pre_nt_3 = 0 AND
                    trp_pre_nt_passenger_on_board <= '".(time() - (5*60))."' AND
                    trp_pre_nt_status = '1'
            "; // cancel trip delay 5 min (my own thought) -still give 5 min to response driver on this.
        $results = DB::select($qry);
        $tripList = parseResponse($results);
        
        $tripIdList = [];
        foreach ($tripList as $key => $value){
            $tripIdList[] = $value['trp_id'];
        }
        
        $updateDataPreNotification = [
                'trp_pre_nt_status' => '3',
                'trp_pre_nt_modified_time' => time()
            ];
        DB::table('a_trip_pre_notifications')
                ->where('trp_pre_nt_status','1')
                ->whereIn('trp_pre_nt_trp_id',$tripIdList)
                ->update($updateDataPreNotification);
        $log['cancelTrip'] = [];
        foreach($tripList as $key => $tripData){
            
            $tripCancel = [
                'trp_cancel_reason' => 'Driver not responded',
                'trp_modified_date' => time(),
                'trp_status' => 'deleted'
            ];
            $isTripCancel = DB::table('a_trips')
                ->where('trp_id', $tripData['trp_id'])
                ->Where('trp_status', "<>", "deleted")
                ->update($tripCancel);

            if ($isTripCancel) {

                $getBookingList = DB::table('a_trip_bookings')
                        ->where('trp_bk_trp_id', $tripData['trp_id'])
                        ->Where('trp_bk_status', 1)
                        ->get()->toArray();
            
                $passengerUserIdList = [];
                $notificationInsertData = [];
                
                foreach ($getBookingList as $bookKey => $bookValue){
                    $passengerUserIdList[] = $bookValue['trp_bk_u_id'];
                    $notificationInsertData[] = [
                        'nt_type' => "cancelTrip",
                        'nt_content_id' => $tripData['trp_id'],
                        'nt_content_type' => "tripId",
                        'nt_gen_for_id' => $bookValue['trp_bk_u_id'],
                        'nt_gen_for_type' => "userId",
                        'nt_gen_by_id' => $tripData['trp_id'],
                        'nt_created_date' => time(),
                        'nt_modified_date' => time(),
                        'nt_status' => 1,
                    ];
                }
                
                $log['cancelTrip'][$key]['psgnrId'] = $passengerUserIdList;
                
                $bookingCancle = [
                    'trp_bk_modified_date' => time(),
                    'trp_bk_status' => 9
                ];
                $isBookingDeleted = DB::table('a_trip_bookings')
                        ->where('trp_bk_trp_id', $tripData['trp_id'])
                        ->Where('trp_bk_status', 1)
                        ->update($bookingCancle);
                
                if (count($notificationInsertData) > 0) {
                    $notificationId = DB::table('a_notification_histories')->insert($notificationInsertData);
                }
                
                $rewardAmount = [
                    'u_modified_date' => time(),
                ];
                $isRewarded = DB::table('a_users')
                        ->whereIn('u_id', $passengerUserIdList)
                        ->where('u_status', 1)
                        ->increment('u_reward_amount', 5, $rewardAmount);
                
                $tokenList = [];
                if(count($passengerUserIdList) > 0){
                    $passengerUserId = implode(',',$passengerUserIdList);
                    $tokenList       = User::getUserDeviceTokenList($passengerUserId);
                    $payLoad  = [//p12
                            'data' => [
                                    'title' =>'Trip has been canceled. ',
                                    'body'  => "We couldn't contact the driver over 3 times, unfortunately we will have to cancel the trip.",
                                    'sound' => 'default',
                                    'content_available' => true,
                                    'type'   => 'cancelTripByCarpo',
                                    'tripId' => $tripId
                                ]
                            ];
                    $payLoad['notification'] = $payLoad['data'];
                    $fcmFeedback = User::sendPushNotification($tokenList,$payLoad,'all');   
                    $log['cancelTrip'][$key]['tokenList'] = $tokenList;
                }
                
                $updateDataPreNotification = [
                        'trp_pre_nt_status' => '2',
                        'trp_pre_nt_modified_time' => time()
                    ];
                DB::table('a_trip_pre_notifications')
                        ->where('trp_pre_nt_status','1')
                        ->where('trp_pre_nt_trp_id',$tripData['trp_id'])
                        ->update($updateDataPreNotification);
            }
        }    
        ////////////// ON BOARD TO PASSENGER /// ///////

        $qry = "
            SELECT
                *
            FROM
                a_trip_pre_notifications as trpNt
            JOIN 
                a_trips as trp on trpNt.trp_pre_nt_trp_id = trp.trp_id AND trp_status = 'started'
            JOIN
                a_users as u ON u.u_id = trp_u_id
            WHERE
                trp_pre_nt_id < '".$maxDataId."' AND
                (
                    trp_pre_nt_1 = 1 OR
                    trp_pre_nt_2 = 1 OR
                    trp_pre_nt_3 = 1 
                ) AND
                trp_pre_nt_passenger_on_board <= '".time()."' AND
                trp_pre_nt_status = '1'
        ";
        $results = DB::select($qry);
        $tripList = parseResponse($results);

        $tripIdList = [];
        foreach ($tripList as $key => $value){
            $tripIdList[] = $value['trp_id'];
        }

        $updateDataPreNotification = [
                'trp_pre_nt_status' => '2',
                'trp_pre_nt_modified_time' => time()
            ];
        DB::table('a_trip_pre_notifications')
                ->where('trp_pre_nt_status','1')
                ->whereIn('trp_pre_nt_trp_id',$tripIdList)
                ->update($updateDataPreNotification);

        $log['onboardPassenger'] = [];
        foreach($tripList as $key => $tripData){

            $getBookingList = DB::table('a_trip_bookings')
                    ->where('trp_bk_trp_id', $tripData['trp_id'])
                    ->Where('trp_bk_status', 1)
                    ->get()->toArray();

            $passengerUserIdList = [];
            $notificationInsertData = [];

            foreach ($getBookingList as $bookKey => $bookValue){
                $passengerUserIdList[] = $bookValue['trp_bk_u_id'];
            }
            
            $log['onboardPassenger'][$key]['psgnrId'] = $passengerUserIdList;
            
            $tokenList = [];
            if(count($passengerUserIdList) > 0){
                $passengerUserId = implode(',',$passengerUserIdList);
                $tokenList       = User::getUserDeviceTokenList($passengerUserId);
                $payLoad  = [ //p6
                    'data' => [
                            'title' =>'Are you onboard?',
                            'body'  => "If you are not at the meeting point by now, Please contact your driver immediately.",
                            'sound' => 'default',
                            'content_available' => true,
                            'type'   => 'passengerOnBoardP6',
                            'tripId' => $tripData['trp_id'],
                        ]
                    ];
                $payLoad['notification'] = $payLoad['data'];
                $fcmFeedback = User::sendPushNotification($tokenList,$payLoad,'all');   
                $log['onboardPassenger'][$key]['tokenList'] = $tokenList;
            }

            $updateDataPreNotification = [
                    'trp_pre_nt_status' => '1',
                    'trp_pre_nt_modified_time' => time()
                ];
            DB::table('a_trip_pre_notifications')
                    ->where('trp_pre_nt_status','2')
                    ->where('trp_pre_nt_trp_id',$tripData['trp_id'])
                    ->update($updateDataPreNotification);
            
        }
        $arr['req_files'] = json_encode($log);
        $insertId = DB::table('a_z_request_logs')->insertGetId($arr);
        $data = [];
      //  $data['tokenList'] = $tokenList;
        return json_encode($data);
    }
    
    //p11 - done
    public static function sendNotifyToPassengerWhenDriverNotResponding($notifyNumber){ 
        $arr = array(
                        'req_api_name'		=> 'cron/sendNotifyToPassengerOnBoardTrip',
                        'req_u_id'		=> 0,
                        'req_post'		=> json_encode([]),
                        'req_get'		=> json_encode([]),
                        'req_files'		=> "",
                        'req_time'		=> time(),
                        'req_formate_time'	=> date('Y-m-d H:i:s',time()),
                );
        $maxDataId = DB::table('a_trip_pre_notifications')->where('trp_pre_nt_status','1')->max('trp_pre_nt_id');
        if(!is_numeric($maxDataId) || $maxDataId < 1){
            $maxDataId = 0;
        }
        $where = ""; 
        // right now we will have only after p15 so $notifyNumber = 2 useless.
        if($notifyNumber == '1'){
            $where .= " trpNt.trp_pre_nt_1 = '0' AND ";
            $dateCheckStart = time() + (45 * 60);
        }else if($notifyNumber == '2'){ 
            $where .= " trpNt.trp_pre_nt_2 = '0' AND ";
            $dateCheckStart = time() + (20 * 60);
        }else {
            return [];
        }
        $dateCheckStart = date("Y-m-d H:i:s",$dateCheckStart);
        
        $qry = "
                SELECT
                    trp.*,u.*,trpNt.*
                FROM
                     a_trip_pre_notifications as trpNt
                JOIN 
                    a_trips as trp on trpNt.trp_pre_nt_trp_id = trp.trp_id AND trp_status = 'pending'
                JOIN
                    a_users as u ON u.u_id = trp_id AND u.u_status = '1'
                WHERE
                    ".$where."
                    trp.trp_start_date_time < '".$dateCheckStart."' AND
                    trpNt.trp_pre_nt_status = '1' AND
                    trpNt.trp_pre_nt_id < '".$maxDataId."'
            ";
        $results = DB::select($qry);
        $tripList = parseResponse($results);

        $tripIdList = [];
        foreach ($tripList as $key => $value){
            $tripIdList[] = $value['trp_id'];
        }

        $updateDataPreNotification = [
                'trp_pre_nt_status' => '2',
                'trp_pre_nt_modified_time' => time()
            ];
        DB::table('a_trip_pre_notifications')
                ->where('trp_pre_nt_status','1')
                ->whereIn('trp_pre_nt_trp_id',$tripIdList)
                ->update($updateDataPreNotification);
        $log = [];
        foreach($tripList as $key => $tripData){

            $getBookingList = DB::table('a_trip_bookings')
                    ->where('trp_bk_trp_id', $tripData['trp_id'])
                    ->Where('trp_bk_status', 1)
                    ->get()->toArray();

            $passengerUserIdList = [];
            $notificationInsertData = [];

            foreach ($getBookingList as $bookKey => $bookValue){
                $passengerUserIdList[] = $bookValue['trp_bk_u_id'];
            }
            
            $log[$key]['psgnrId'] = $passengerUserIdList;
            
            $tokenList = [];
            if(count($passengerUserIdList) > 0){
                $passengerUserId = implode(',',$passengerUserIdList);
                $tokenList       = User::getUserDeviceTokenList($passengerUserId);
                $payLoad  = [//p11
                        'data' => [
                                'title' =>'Driver not responding',
                                'body'  => "We just tried to contact the driver but he isn't responding. We will try few more time and inform you as soon as possible.",
                                'sound' => 'default',
                                'content_available' => true,
                                'type'   => 'driverNotRespondingP11',
                                'tripId' => $tripData['trp_id'],
                            ]
                        ];
                $payLoad['notification'] = $payLoad['data'];
                $fcmFeedback = User::sendPushNotification($tokenList,$payLoad,'all');   
                $log[$key]['tokenList'] = $tokenList;
            }
            
            $updateDataPreNotification = [
                    'trp_pre_nt_status' => '1',
                    'trp_pre_nt_modified_time' => time()
                ];
            
            if($notifyNumber == '1'){
                $updateDataPreNotification["trp_pre_nt_1"] = '2';
            }else if($notifyNumber == '2'){
                $updateDataPreNotification["trp_pre_nt_2"] = '2';
            }

            
            DB::table('a_trip_pre_notifications')
                    ->where('trp_pre_nt_status','2')
                    ->where('trp_pre_nt_trp_id',$tripData['trp_id'])
                    ->update($updateDataPreNotification);
            
        }
        $arr['req_files'] = json_encode($log);
        $insertId = DB::table('a_z_request_logs')->insertGetId($arr);
        $data = [];
        return json_encode($data);
    }
    
    
}