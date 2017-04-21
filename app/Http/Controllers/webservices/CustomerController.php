<?php

namespace App\Http\Controllers\Webservices;

use Mail;
//use Request;
use Illuminate\Http\Request as Request;
use Validator;
use App\User as User;
use App\Http\Controllers\Controller;
use DB;
use Hash;
use Storage;
use Image;
use Edujugon\PushNotification\PushNotification;
use JWTAuth;
use JWTFactory;
use Tymon\JWTAuth\Exceptions\JWTException;

class JobController extends Controller {
    protected  $time;
    public function __construct()
    {
        $this->time = time();
    }
    
    public function sendJobRequest(Request $request,$jobId) {
	$requestLogId = 0;
        try{
            $xSessionID      = $request->header('X-Session-ID','');
            $userData        = User::checkAccessToken($xSessionID);
            $getArr = [];
            if(!checkArr($userData)){
                $requestLogId = User::requestLogData('0','job/sendJobRequest',$_POST,array_merge($getArr,$_GET),$_FILES,$request->header());
                $jsonResponse = array(
                        "statusCode" => 9,
                        "errors"     => array(),
                        "message"    => "Access denied.",
                    );
                return response()->json(parseResponse($jsonResponse), 401);
            }
            $userId         = $userData['u_id'];
            $securityToken  = $userData['securityToken'];
            
            $requestLogId = User::requestLogData($userId,'job/sendJobRequest',$_POST,array_merge($getArr,$_GET),$_FILES,$request->header());
            
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
            
         //   $name           = $request->input('jobName','');
            
            $queryData = [];
            $qry = "SELECT 
                        job.*,jobCon.*,client.u_name as clientName,exe.u_name as executiveName
                    FROM 
                        a_jobs as job
                    JOIN 
                        a_users as client ON  client.u_id = job_client_u_id and client.u_status = 1 
                    JOIN 
                        a_users as exe ON  exe.u_id = job_exe_u_id and exe.u_status = 1 
                    LEFT JOIN 
                        a_job_contractors as jobCon ON  job_con_u_id = '".$userId."' AND job_con_job_id = job_id and job_status != 9 
                    WHERE 
                        job_id = '".$jobId."' AND 
                        job_status != 9 
                    ";
            $dataList = DB::select($qry,$queryData);
            $dataList = parseResponse($dataList);
            $jobData = isset($dataList[0]) && checkArr($dataList[0]) ? $dataList[0]  : [];
          //  _kd($jobData);
            if(!checkArr($jobData)){
                $jsonResponse = [
                    "statusCode" => 2,
                    "errors"     => [],
                    "message"    => "This job not available.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,5);
                return response()->json(parseResponse($jsonResponse), 400);
            }
            
            if($jobData['job_con_status'] == '0'){
                $jsonResponse = [
                    "statusCode" => 2,
                    "errors"     => [],
                    "message"    => "This job request already pending for approval.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,5);
                return response()->json(parseResponse($jsonResponse), 400);
            }
            
            if($jobData['job_con_status'] == 1){
                $jsonResponse = [
                    "statusCode" => 2,
                    "errors"     => [],
                    "message"    => "This job already assigned to you.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,5);
                return response()->json(parseResponse($jsonResponse), 400);
            }
            
            $insertData = array(
                            'job_con_u_id'	=> $userId,
                            'job_con_job_id'	=> $jobId,
                            'job_exe_status'	=> 0,
                            'job_client_status'	=> 0,
                            'job_con_created_date'	=> $this->time,
                            'job_con_modified_date'	=> $this->time,
                            'job_con_status'		=> 0,
                    );
            $requestId = DB::table('a_job_contractors')->insertGetId($insertData);
            if($requestId){
                $notificationData = [
                        'nt_type'           => "jobRequestToClient",
                        'nt_content_id'     => $requestId,
                        'nt_content_type'   => "jobContractorId",	
                        'nt_gen_for_id'     => $jobData['job_client_u_id'],
                        'nt_gen_for_type'   => "userId",	
                        'nt_gen_by_id'      => $userId,
                        'nt_created_date'   => time(),
                        'nt_modified_date'  => time(),
                        'nt_status'         => "1",
                    ];
                $notificationId = DB::table('a_notification_histories')->insertGetId($notificationData);

//                            $isUpdated = DB::table('a_users')
//                                ->where('u_id', $otherUserId)
//                                ->where('u_status', "1")
//                                ->increment('u_badge_count', 1);
//
//                            $tokenList = User::getUserDeviceTokenList($otherUserId);
//                            $allTokenList['token'][$otherUserId] = $tokenList;
//                            $payLoad['aps'] = [
//                                    'icon'      => "appicon",
//                                    'alert'     => $userData['u_first_name']." ".$userData['u_last_name']." requested to add you as a friend.",
//                                    'sound'     => "default",
//                                    'type'      => "friendRequest",
//                                    'uid'       => $otherUserId,
//                                ];
//                            $allTokenList['resp'][$otherUserId] = $apnsFeedback = User::sendPushNotification($tokenList,$payLoad,'ios');
//
                
                $jsonResponse = [
                        "statusCode"    => 1,
                        "errors"        => [],
                        "message"       => "Request sent.",
                    ];
                $jsonResponse['data'] = [
                        'jobRequestId' => $requestId
                    ]; 
                User::responseLogData($requestLogId,$jsonResponse);
                return response(parseResponse($jsonResponse), 200)->header('Content-Type', "json");
                    
            }
           
            $jsonResponse = [
                    "statusCode"    => 2,
                    "errors"        => [],
                    "message"       => "There are problem with sending your request.",
                ];
            User::responseLogData($requestLogId,$jsonResponse);
            return response(parseResponse($jsonResponse), 200)->header('Content-Type', "json");
        }catch (\Exception $e) {
            $jsonResponse = array(
                    "statusCode" => 2,
                    "errors"     => [$e->getMessage(),"Line number : ".$e->getLine(),"Whole error : ".$e->getTraceAsString()],
                    "message"    => (env('APP_SHOW_EXCEPTION_MSG',false) == true) ? $e->getMessage() : "There are problem with passing your request. Please, try again.",
            );
            User::responseLogData($requestLogId,$jsonResponse,1);
            return response()->json(parseResponse($jsonResponse), 500);
        }
    }
    
    
    public function moderateJobRequest(Request $request,$moderateBy,$contractorId,$jobId) {
	$requestLogId = 0;
        try{
            $xSessionID      = $request->header('X-Session-ID','');
            $userData        = User::checkAccessToken($xSessionID);
            $getArr = [];
            if(!checkArr($userData)){
                $requestLogId = User::requestLogData('0','job/moderateJobRequest',$_POST,array_merge($getArr,$_GET),$_FILES,$request->header());
                $jsonResponse = array(
                        "statusCode" => 9,
                        "errors"     => array(),
                        "message"    => "Access denied.",
                    );
                return response()->json(parseResponse($jsonResponse), 401);
            }
            $userId         = $userData['u_id'];
            $securityToken  = $userData['securityToken'];
            
            $requestLogId = User::requestLogData($userId,'job/moderateJobRequest',$_POST,array_merge($getArr,$_GET),$_FILES,$request->header());
            
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
            
            $status           = $request->input('status','9');
            $where = "";
            $queryData = [];
            if($moderateBy == "executive"){
                $queryData['executiveId'] = $userId;
                $where .= " job_exe_u_id = :executiveId AND ";
            }else if($moderateBy == "client"){
                $queryData[':clientId'] = $userId;
                $where .= " job_exe_u_id = :clientId AND ";
            }else{
                $jsonResponse = [
                    "statusCode" => 2,
                    "errors"     => [],
                    "message"    => "Invalid request.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,5);
                return response()->json(parseResponse($jsonResponse), 400);
            }
            $qry = "SELECT 
                        job.*,jobCon.*,client.u_name as clientName,exe.u_name as executiveName
                    FROM 
                        a_jobs as job
                    JOIN 
                        a_users as client ON  client.u_id = job_client_u_id and client.u_status = 1 
                    JOIN 
                        a_users as exe ON  exe.u_id = job_exe_u_id and exe.u_status = 1 
                    LEFT JOIN 
                        a_job_contractors as jobCon ON  job_con_u_id = '".$contractorId."' AND job_con_job_id = job_id and job_status != 9 
                    WHERE 
                        ".$where."
                        job_id = '".$jobId."' AND 
                        job_status != 9 
                    ";
            $dataList = DB::select($qry,$queryData);
            $dataList = parseResponse($dataList);
            $jobData = isset($dataList[0]) && checkArr($dataList[0]) ? $dataList[0]  : [];
          //  _kd($jobData);
            if(!checkArr($jobData)){
                $jsonResponse = [
                    "statusCode" => 2,
                    "errors"     => [],
                    "message"    => "Now, this job not available longer to your list.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,5);
                return response()->json(parseResponse($jsonResponse), 400);
            }
            
            $keyJobModerate = ($moderateBy == "executive") ? 'job_con_exe_status' : 'job_con_client_status';
            
            if($jobData[$keyJobModerate] == '1'){
                $jsonResponse = [
                    "statusCode" => 2,
                    "errors"     => [],
                    "message"    => "This job already approved by you.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,5);
                return response()->json(parseResponse($jsonResponse), 400);
            } else if($jobData[$keyJobModerate] == '9'){
                $jsonResponse = [
                    "statusCode" => 2,
                    "errors"     => [],
                    "message"    => "This job already rejected by you.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,5);
                return response()->json(parseResponse($jsonResponse), 400);
            }
            if($moderateBy == "executive" && $jobData['job_con_client_status'] == '0'){
                $jsonResponse = [
                    "statusCode" => 2,
                    "errors"     => [],
                    "message"    => "This job is under client approval. Please, try after.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,5);
                return response()->json(parseResponse($jsonResponse), 400);
            }
            
            if($moderateBy == "executive" && $jobData['job_con_client_status'] == '9'){
                $jsonResponse = [
                    "statusCode" => 2,
                    "errors"     => [],
                    "message"    => "This job was already rejected by client.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,5);
                return response()->json(parseResponse($jsonResponse), 400);
            }
            
            $jobConStatus = ($status == 9) ? $status : 0;
            if($moderateBy == "executive" && $status == '1'){
                $jobConStatus = '1';
            }
            $updateJobData = array(
                            $keyJobModerate             => $status,
                            'job_con_modified_date'	=> $this->time,
                            'job_con_status'		=> $jobConStatus,
                    );
            $isUpdated = DB::table('a_job_contractors')
                    ->where('job_con_u_id',$contractorId)
                    ->where('job_con_job_id',$jobId)
                    ->where('job_status','0')
                    ->update($updateJobData);
            if($isUpdated){
                 if($moderateBy == "client" && $status == '1'){
                    $notificationData = [
                        'nt_type'           => "jobRequestToExecutive",
                        'nt_content_id'     => $jobData['job_con_id'],
                        'nt_content_type'   => "jobContractorId",	
                        'nt_gen_for_id'     => $jobData['job_exe_u_id'],
                        'nt_gen_for_type'   => "userId",	
                        'nt_gen_by_id'      => $userId,
                        'nt_created_date'   => time(),
                        'nt_modified_date'  => time(),
                        'nt_status'         => "1",
                    ];
                    $notificationId = DB::table('a_notification_histories')->insertGetId($notificationData);
                 }else if($moderateBy == "executive" && $status == '1'){
                    $notificationData = [
                        'nt_type'           => "jobApprovedByExecutive",
                        'nt_content_id'     => $jobData['job_con_id'],
                        'nt_content_type'   => "jobContractorId",	
                        'nt_gen_for_id'     => $contractorId,
                        'nt_gen_for_type'   => "userId",	
                        'nt_gen_by_id'      => $userId,
                        'nt_created_date'   => time(),
                        'nt_modified_date'  => time(),
                        'nt_status'         => "1",
                    ];
                    $notificationId = DB::table('a_notification_histories')->insertGetId($notificationData);
                 }
                //jobApprovedByExecutive
                
                
                

//                            $isUpdated = DB::table('a_users')
//                                ->where('u_id', $otherUserId)
//                                ->where('u_status', "1")
//                                ->increment('u_badge_count', 1);
//
//                            $tokenList = User::getUserDeviceTokenList($otherUserId);
//                            $allTokenList['token'][$otherUserId] = $tokenList;
//                            $payLoad['aps'] = [
//                                    'icon'      => "appicon",
//                                    'alert'     => $userData['u_first_name']." ".$userData['u_last_name']." requested to add you as a friend.",
//                                    'sound'     => "default",
//                                    'type'      => "friendRequest",
//                                    'uid'       => $otherUserId,
//                                ];
//                            $allTokenList['resp'][$otherUserId] = $apnsFeedback = User::sendPushNotification($tokenList,$payLoad,'ios');
//
                
                $jsonResponse = [
                        "statusCode"    => 1,
                        "errors"        => [],
                        "message"       => "Request sent.",
                    ];
                $jsonResponse['data'] = [
                        'jobRequestId' => $requestId
                    ]; 
                User::responseLogData($requestLogId,$jsonResponse);
                return response(parseResponse($jsonResponse), 200)->header('Content-Type', "json");
                    
            }
           
            $jsonResponse = [
                    "statusCode"    => 2,
                    "errors"        => [],
                    "message"       => "There are problem with sending your request.",
                ];
            User::responseLogData($requestLogId,$jsonResponse);
            return response(parseResponse($jsonResponse), 200)->header('Content-Type', "json");
        }catch (\Exception $e) {
            $jsonResponse = array(
                    "statusCode" => 2,
                    "errors"     => [$e->getMessage(),"Line number : ".$e->getLine(),"Whole error : ".$e->getTraceAsString()],
                    "message"    => (env('APP_SHOW_EXCEPTION_MSG',false) == true) ? $e->getMessage() : "There are problem with passing your request. Please, try again.",
            );
            User::responseLogData($requestLogId,$jsonResponse,1);
            return response()->json(parseResponse($jsonResponse), 500);
        }
    }
    
    public function createJob(Request $request) {
        $requestLogId = 0;
        try{
            $xSessionID      = $request->header('X-Session-ID','');
            $userData        = User::checkAccessToken($xSessionID);
            $getArr = [];
            if(!checkArr($userData)){
                $requestLogId = User::requestLogData('0','job/createJob',$_POST,array_merge($getArr,$_GET),$_FILES,$request->header());
                $jsonResponse = array(
                        "statusCode" => 9,
                        "errors"     => array(),
                        "message"    => "Access denied.",
                    );
                return response()->json(parseResponse($jsonResponse), 401);
            }
            $userId         = $userData['u_id'];
            $securityToken  = $userData['securityToken'];
            
            $requestLogId = User::requestLogData($userId,'job/createJob',$_POST,array_merge($getArr,$_GET),$_FILES,$request->header());
            
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
                'clientUserId' => 'required',
                'jobName' => 'required',
                'jobScopeOfWork' => 'required',
                'jobAddress' => 'required',
                'jobRate' => 'required',
                'jobDescription' => 'required',
                'jobSkillIdList' => 'required',
                'jobHoursOfweek' => 'required',
                'jobPersonsNeeded' => 'required'
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
            
            $name           = $request->input('jobName','');
            $scopeOfWork      = $request->input('jobScopeOfWork');
            $address     = $request->input('jobAddress','');
            $rate    = $request->input('jobRate','');
            $latitude     = $request->input('jobLatitude','');
            $longitude    = $request->input('jobLongitude','');
            $clientUserId    = $request->input('clientUserId','');
            $description     = $request->input('jobDescription','');
            $skills     = $request->input('jobSkillIdList','');
            $hoursOfweek = $request->input('jobHoursOfweek','');
            $personsNeeded  = $request->input('jobPersonsNeeded','');
            
//            if($gameMode == "friends" && $gameCode != ""){
//                $messages = $validator->errors()->all();
//                $jsonResponse = [
//                    "statusCode" => 2,
//                    "errors"     => [],
//                    "message"    => "Game code is required.",
//                ];
//                User::responseLogData($requestLogId,$jsonResponse,5);
//                return response()->json(parseResponse($jsonResponse), 400);
//            }
            
            $clientData = (array) DB::table('a_users')
                ->join('a_executive_clients', function ($join) use ($userId){
                        $join->on('exe_client_client_u_id', '=', 'u_id')
                             ->where('exe_client_executive_u_id', '=', $userId);
                    })
                ->where('u_id', $clientUserId)
                ->where('u_status', 1)->first();  
            if(!checkArr($clientData)){
                $jsonResponse = [
                    "statusCode" => 2,
                    "errors"     => [],
                    "message"    => "This client not available to your list.",
                ];
                User::responseLogData($requestLogId,$jsonResponse,5);
                return response()->json(parseResponse($jsonResponse), 400);
            }   
            
            $insertData = [
                    'job_name'             => $name,
                    'job_client_u_id'      => $clientUserId,
                    'job_exe_u_id'         => $userId,
                    'job_contractor_u_id' => 0,
                    'job_scope_of_work'    => $scopeOfWork,
                    'job_address'          => $address,
                    'job_latitude'         => $latitude,
                    'job_longitude'        => $longitude,
                    'job_description'      => $description,
                    'job_rate'             => $rate,
                    'job_skills'           => $skills,
                    'job_hour_week'        => $hoursOfweek,
                    'job_persons_needed'   => $personsNeeded,
                    'job_created_date'     => $this->time,
                    'job_modified_date'    => $this->time,
                    'job_status'           => 0,
                ];
            $jobId = DB::table('a_jobs')->insertGetId($insertData);

            if($jobId){
                $jsonResponse = [
                        "statusCode"    => 1,
                        "errors"        => [],
                        "message"       => "Success.",
                    ];
                $jsonResponse['data'] = [
                        'jobId' => $jobId
                    ]; 
                User::responseLogData($requestLogId,$jsonResponse);
                return response(parseResponse($jsonResponse), 200)->header('Content-Type', "json");
            }

            $jsonResponse = [
                    "statusCode" => 2,
                    "errors"     => [],
                    "message"    => "Sorry, we are unable to create job. Please, try again.",
                ];
            User::responseLogData($requestLogId,$jsonResponse,8);
            return response(parseResponse($jsonResponse), 404)->header('Content-Type', "json");

        }catch (\Exception $e) {
            $jsonResponse = array(
                    "statusCode" => 2,
                    "errors"     => [$e->getMessage(),"Line number : ".$e->getLine(),"Whole error : ".$e->getTraceAsString()],
                    "message"    => (env('APP_SHOW_EXCEPTION_MSG',false) == true) ? $e->getMessage() : "There are problem with passing your request. Please, try again.",
            );
            User::responseLogData($requestLogId,$jsonResponse,1);
            return response()->json(parseResponse($jsonResponse), 500);
        }
    }
    
    public function getExecutiveClientList(Request $request) {
        $requestLogId = 0;
        try{
            $xSessionID      = $request->header('X-Session-ID','');
            $userData        = User::checkAccessToken($xSessionID);
            $getArr = [];
            if(!checkArr($userData)){
                $requestLogId = User::requestLogData('0','job/getExecutiveClientList',$_POST,array_merge($getArr,$_GET),$_FILES,$request->header());
                $jsonResponse = array(
                        "statusCode" => 9,
                        "errors"     => array(),
                        "message"    => "Access denied.",
                    );
                return response()->json(parseResponse($jsonResponse), 401);
            }
            $userId         = $userData['u_id'];
            $securityToken  = $userData['securityToken'];
            
            $requestLogId = User::requestLogData($userId,'job/getExecutiveClientList',$_POST,array_merge($getArr,$_GET),$_FILES,$request->header());
            
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
            
            $pageNo      = $request->input('pageNo','1');
            $search      = $request->input('search','');
            $maxId       = $request->input('maxId','');
            
            if (!is_numeric($pageNo) || $pageNo < 1){
                $pageNo = 1;
            }
            $limit              = 50;
            $pageStartLimit	= ($pageNo - 1) * $limit;
            
            
            if (!is_numeric($maxId) || $maxId < 1) {
                $maxId = DB::table('a_executive_clients')->where('exe_client_executive_u_id',  $userId)->max('exe_client_id');
                if (!is_numeric($maxId) || $maxId < 1) {
                    $maxId = 0;
                }
            }
            
            $whereQry = "";
            $queryDaata = [];
            if ($search != ''){
                $queryDaata['search'] = $search."%";
                $whereQry .= " u_name LIKE :search AND ";
            }
            
            
            $qry = "SELECT 
                        * 
                    FROM 
                        a_executive_clients 
                    JOIN 
                        a_users ON u_id = exe_client_client_u_id and u_status = 1 
                    WHERE 
                        ".$whereQry."
                        exe_client_executive_u_id = '".$userId."' 
                    LIMIT "
                    . $pageStartLimit.", ".$limit;
            $dataList = DB::select($qry,$queryDaata);
            $dataList = parseResponse($dataList);
            
            $newList = [];
            foreach ($dataList as $key => $value){
                $temp = getUserResp($value);
                $newList[] = $temp;
            }
            
            if(checkArr($newList)){
                
                $jsonResponse = [
                        "statusCode"        => "1",
                        "errors"            => [],
                        "message"           => "Success.",
                    ];
                $jsonResponse["data"] = [
                        "clientList"  => $newList,
                        "maxId"  => $maxId,
                    ];
                User::responseLogData($requestLogId,$jsonResponse);
                return response(parseResponse($jsonResponse), 200)->header('Content-Type', "json");
            }
            $jsonResponse = [
                "statusCode" => 2,
                "errors"     => [],
                "message"    => "There are no client available.",
            ];
            User::responseLogData($requestLogId,$jsonResponse,8);
            return response(parseResponse($jsonResponse), 404)->header('Content-Type', "json");

        }catch (\Exception $e) {
            $jsonResponse = array(
                    "statusCode" => 2,
                    "errors"     => [$e->getMessage(),"Line number : ".$e->getLine(),"Whole error : ".$e->getTraceAsString()],
                    "message"    => (env('APP_SHOW_EXCEPTION_MSG',false) == true) ? $e->getMessage() : "There are problem with passing your request. Please, try again.",
            );
            User::responseLogData($requestLogId,$jsonResponse,1);
            return response()->json(parseResponse($jsonResponse), 500);
        }
    }
    
    
    public function getJobList(Request $request) {
        $requestLogId = 0;
        try{
            $xSessionID      = $request->header('X-Session-ID','');
            $userData        = User::checkAccessToken($xSessionID);
            $getArr = [];
            if(!checkArr($userData)){
                $requestLogId = User::requestLogData('0','job/getJobList',$_POST,array_merge($getArr,$_GET),$_FILES,$request->header());
                $jsonResponse = array(
                        "statusCode" => 9,
                        "errors"     => array(),
                        "message"    => "Access denied.",
                    );
                User::responseLogData($requestLogId,$jsonResponse,4);
                return response()->json(parseResponse($jsonResponse), 401);
            }
            $userId         = $userData['u_id'];
            $securityToken  = $userData['securityToken'];
            
            $requestLogId = User::requestLogData($userId,'job/getJobList',$_POST,array_merge($getArr,$_GET),$_FILES,$request->header());
            
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
            
            $pageNo             = $request->input('pageNo','1');
            $search             = $request->input('search','');
            $maxId              = $request->input('maxId','');
            $clientUserId       = $request->input('clientUserId','');
            $latitude           = $request->input('latitude','');
            $longitude          = $request->input('longitude','');
            $isMyJob            = $request->input('isMyJob','');
            
            
            
            
            $limit              = 50;
            $pageStartLimit	= ($pageNo - 1) * $limit;
            
            if (!is_numeric($pageNo) || $pageNo < 1){
                $pageNo = 1;
            }
            
            if (!is_numeric($maxId) || $maxId < 1) {
                $maxId = DB::table('a_jobs')->max('job_id');
                if (!is_numeric($maxId) || $maxId < 1) {
                    $maxId = 0;
                }
            }
            
            $whereQry = "";
            $queryDaata = [];
            
            if($clientUserId > 0){
                $clientData = (array) DB::table('a_users')
                    ->join('a_executive_clients', function ($join) use ($userId){
                            $join->on('exe_client_client_u_id', '=', 'u_id')
                                 ->where('exe_client_executive_u_id', '=', $userId);
                        })
                    ->where('u_id', $clientUserId)
                    ->where('u_status', 1)->first();  


                if(!checkArr($clientData)){
                    $jsonResponse = [
                            "statusCode"        => "1",
                            "errors"            => [],
                            "message"           => "This client not found to your assigned list.",
                        ];
                    $jsonResponse["data"] = [
                        ];
                    User::responseLogData($requestLogId,$jsonResponse);
                    return response(parseResponse($jsonResponse), 200)->header('Content-Type', "json");
                }
                
                $queryDaata['clientUserId'] = $clientUserId;
                $whereQry .= " job_client_u_id = :clientUserId AND ";
            }
            
            if($userData['u_type'] == 2){
            //    $queryDaata['userId'] = $userId;
                $whereQry .= " job_client_u_id = '".$userId."' AND ";
            }
            
            if($userData['u_type'] == 3){
                //$queryDaata['userId'] = $userId;
                $whereQry .= " job_exe_u_id = '".$userId."' AND ";
            }
            
            if($userData['u_type'] == 4){
                $queryDaata['userId'] = $userId;
                $orQuery = "";
                if($isMyJob == 1){
                    $whereQry .= " job_status != 9 AND job_id IN ( SELECT job_con_job_id FROM a_job_contractors WHERE job_con_u_id = '".$userId."' AND job_con_status = 1 ) AND  ";
                } else{
                    $whereQry .= " job_status = 0 AND job_id NOT IN ( SELECT job_con_job_id FROM a_job_contractors WHERE job_con_u_id = '".$userId."' AND job_con_status = 1 ) AND ";
                }
            }
            
            $qry = "SELECT 
                        job.*,
                        client.u_name as clientName,client.u_phone as clientPhone,client.u_phone_country_code as clientPhoneCountryCode,
                      exe.u_name as executiveName,exe.u_phone as executivePhone,exe.u_phone_country_code as executivePhoneCountryCode,
                        get_distance_in_miles_between_geo_locations('".$latitude."', '".$longitude."', job_latitude, job_longitude) as distance
                    FROM 
                        a_jobs as job
                    JOIN 
                        a_users as client ON  client.u_id = job_client_u_id and client.u_status = 1 
                    JOIN 
                        a_users as exe ON  exe.u_id = job_exe_u_id and exe.u_status = 1 
                    WHERE 
                        ".$whereQry."
                        job_status != 9 
                    ORDER BY
                        distance ASC
                    LIMIT "
                     . $pageStartLimit.", ".$limit;
            $dataList = DB::select($qry,$queryDaata);
            $dataList = parseResponse($dataList);
            
            $newList = [];
            foreach ($dataList as $key => $value){
                $temp = getJobResp($value);
                $newList[] = $temp;
            }
            
            if(checkArr($newList)){
                
                $jsonResponse = [
                        "statusCode"        => "1",
                        "errors"            => [],
                        "message"           => "Success.",
                    ];
                $jsonResponse["data"] = [
                        "jobList"  => $newList,
                        "maxId"  => $maxId,
                        "qry" => $qry,
                    ];
                User::responseLogData($requestLogId,$jsonResponse);
                return response(parseResponse($jsonResponse), 200)->header('Content-Type', "json");
            }
            $jsonResponse = [
                "statusCode" => 2,
                "errors"     => [],
                "message"    => "There are no jobs available.",
            ];
            User::responseLogData($requestLogId,$jsonResponse,8);
            return response(parseResponse($jsonResponse), 200)->header('Content-Type', "json");

        }catch (\Exception $e) {
            $jsonResponse = array(
                    "statusCode" => 2,
                    "errors"     => [$e->getMessage(),"Line number : ".$e->getLine(),"Whole error : ".$e->getTraceAsString()],
                    "message"    => (env('APP_SHOW_EXCEPTION_MSG',false) == true) ? $e->getMessage() : "There are problem with passing your request. Please, try again.",
            );
            User::responseLogData($requestLogId,$jsonResponse,1);
            return response()->json(parseResponse($jsonResponse), 500);
        }
    }
    
    
    public function getJobDetail(Request $request) {
        $requestLogId = 0;
        try{
            $xSessionID      = $request->header('X-Session-ID','');
            $userData        = User::checkAccessToken($xSessionID);
            $getArr = [];
            if(!checkArr($userData)){
                $requestLogId = User::requestLogData('0','job/getJobDetail',$_POST,array_merge($getArr,$_GET),$_FILES,$request->header());
                $jsonResponse = array(
                        "statusCode" => 9,
                        "errors"     => array(),
                        "message"    => "Access denied.",
                    );
                return response()->json(parseResponse($jsonResponse), 401);
            }
            $userId         = $userData['u_id'];
            $securityToken  = $userData['securityToken'];
            
            $requestLogId = User::requestLogData($userId,'job/getJobDetail',$_POST,array_merge($getArr,$_GET),$_FILES,$request->header());
            
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
            
            $jobId              = $request->input('jobId','');
            
            $whereQry = "";
            $orderQry = " job_id DESC ";
            $havingQry = "";
            $select   = "*";
            if($businessId > 0){ 
                $whereQry .= " bus_id = '".$businessId."' AND ";
            }
            
            if($gameContestId > 0){
                $whereQry .= " bus_last_contest_id = '".$gameContestId."' AND ";
            }
            
            $qry = "SELECT 
                        job.*,
                        client.u_name as clientName,client.u_phone as clientPhone,client.u_phone_country_code as clientPhoneCountryCode,
                      exe.u_name as executiveName,exe.u_phone as executivePhone,exe.u_phone_country_code as executivePhoneCountryCode
                        
                    FROM 
                        a_jobs as job
                    JOIN 
                        a_users as client ON  client.u_id = job_client_u_id and client.u_status = 1 
                    JOIN 
                        a_users as exe ON  exe.u_id = job_exe_u_id and exe.u_status = 1 
                    WHERE 
                        ".$whereQry."
                        job_id = '".$jobId."' AND 
                        job_status != 9 
                    ";
            $dataList = DB::select($qry,$queryDaata);
            $dataList = parseResponse($dataList);
            $tempJobData = isset($dataList[0]) && checkArr($dataList[0]) ? $dataList[0]  : [];
            
            if(checkArr($tempJobData)){
                $jobData = getJobResp($tempJobData);
                
//                $qry = "SELECT 
//                            *
//                        FROM 
//                            a_job_contractors 
//                        JOIN 
//                            a_users ON  u_id = job_con_u_id and u_status = 1 
//                        WHERE 
//                            job_con_job_id  = '".$jobId."' AND 
//                            job_con_status = 1 
//                        ORDER BY
//                            job_con_id DESC
//                        LIMIT
//                            0,20
//                        ";
//                $dataList = DB::select($qry,$queryDaata);
//                $dataList = parseResponse($dataList);
//                
//                $hiredUserList = [];
//                foreach ($dataList as $key => $value){
//                    $hiredUserList[] = getUserResp($value,9);
//                }
//
//                $jobData['hiredList']       = $hiredUserList;
//                
//                
//                $qry = "SELECT 
//                            *
//                        FROM 
//                            a_job_contractors 
//                        JOIN 
//                            a_users ON  u_id = job_con_u_id and u_status = 1 
//                        WHERE 
//                            job_con_job_id  = '".$jobId."' AND 
//                            job_con_status = '0' 
//                        ORDER BY
//                            job_con_id DESC
//                        LIMIT
//                            0,20
//                        ";
//                $dataList = DB::select($qry,$queryDaata);
//                $dataList = parseResponse($dataList);
//                
//                $appliedUserList = [];
//                foreach ($dataList as $key => $value){
//                    $appliedUserList[] = getUserResp($value,9);
//                }
//
//                $jobData['appliedList']       = $appliedUserList;
                
                $jsonResponse = [
                    "statusCode"    => 1,
                    "errors"        => [],
                    "message"       => "Success.",
                    "data"          => $jobData,
                ];
                User::responseLogData($requestLogId,$jsonResponse);
                return response(parseResponse($jsonResponse), 200)->header('Content-Type', "json");
            }

            $jsonResponse = [
                "statusCode" => 2,
                "errors"     => [],
                "message"    => "Sorry, Ads not available. Please, try again.",
            ];
            User::responseLogData($requestLogId,$jsonResponse,8);
            return response(parseResponse($jsonResponse), 404)->header('Content-Type', "json");

        }catch (\Exception $e) {
            $jsonResponse = array(
                    "statusCode" => 2,
                    "errors"     => [$e->getMessage(),"Line number : ".$e->getLine(),"Whole error : ".$e->getTraceAsString()],
                    "message"    => (env('APP_SHOW_EXCEPTION_MSG',false) == true) ? $e->getMessage() : "There are problem with passing your request. Please, try again.",
            );
            User::responseLogData($requestLogId,$jsonResponse,1);
            return response()->json(parseResponse($jsonResponse), 500);
        }
    }
    
    
    public function getJobContractorList(Request $request,$jobId,$statusType="0") {
        $requestLogId = 0;
        try{
            $xSessionID      = $request->header('X-Session-ID','');
            $userData        = User::checkAccessToken($xSessionID);
            $getArr = ['statusType',$statusType];
            if(!checkArr($userData)){
                $requestLogId = User::requestLogData('0','job/getJobContractorList',$_POST,array_merge($getArr,$_GET),$_FILES,$request->header());
                $jsonResponse = array(
                        "statusCode" => 9,
                        "errors"     => array(),
                        "message"    => "Access denied.",
                    );
                User::responseLogData($requestLogId,$jsonResponse,4);
                return response()->json(parseResponse($jsonResponse), 401);
            }
            $userId         = $userData['u_id'];
            $securityToken  = $userData['securityToken'];
            
            $requestLogId = User::requestLogData($userId,'job/getJobContractorList',$_POST,array_merge($getArr,$_GET),$_FILES,$request->header());
            
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
            
            $pageNo             = $request->input('pageNo','1');
            $maxId              = $request->input('maxId','');
            
            $limit              = 50;
            $pageStartLimit	= ($pageNo - 1) * $limit;
            
            if (!is_numeric($pageNo) || $pageNo < 1){
                $pageNo = 1;
            }
            
            if (!is_numeric($maxId) || $maxId < 1) {
                $maxId = DB::table('a_job_contractors')->max('job_id');
                if (!is_numeric($maxId) || $maxId < 1) {
                    $maxId = 0;
                }
            }
            
            $whereQry = "";
            $queryDaata = [];
            
            if($statusType == 'hired' || $statusType != '1'){
                $whereQry .= " job_con_status = '1' AND ";
            }else if($statusType == 'applied'){
                $whereQry .= " job_con_status = '0' AND ";
            }else{
                $whereQry .= " job_con_status != 9 AND ";
            }
            
            $qry = "SELECT 
                        *
                    FROM 
                        a_job_contractors 
                    JOIN 
                        a_users ON  u_id = job_con_u_id and u_status = 1 
                    WHERE 
                        ".$whereQry."
                        job_con_id <= '".$maxId."' AND
                        job_con_job_id  = '".$jobId."' 
                    ORDER BY
                        job_con_id DESC
                    LIMIT "
                 . $pageStartLimit.", ".$limit;
            $dataList = DB::select($qry,$queryDaata);
            $dataList = parseResponse($dataList);

            $hiredUserList = [];
            foreach ($dataList as $key => $value){
                $temp= getUserResp($value,9);
                $temp['contractDetail'] = getJobContractResp($value);
                $hiredUserList[] = $temp;
            }

            if(checkArr($hiredUserList)){
                $jsonResponse = [
                        "statusCode"        => "1",
                        "errors"            => [],
                        "message"           => "Success.",
                    ];
                $jsonResponse["data"] = [
                        "contractorList"  => $hiredUserList,
                        "maxId"  => $maxId,
                    ];
                User::responseLogData($requestLogId,$jsonResponse);
                return response(parseResponse($jsonResponse), 200)->header('Content-Type', "json");
            }
            $jsonResponse = [
                "statusCode" => 2,
                "errors"     => [],
                "message"    => "There are no subcontractor found.",
            ];
            User::responseLogData($requestLogId,$jsonResponse,8);
            return response(parseResponse($jsonResponse), 404)->header('Content-Type', "json");

        }catch (\Exception $e) {
            $jsonResponse = array(
                    "statusCode" => 2,
                    "errors"     => [$e->getMessage(),"Line number : ".$e->getLine(),"Whole error : ".$e->getTraceAsString()],
                    "message"    => (env('APP_SHOW_EXCEPTION_MSG',false) == true) ? $e->getMessage() : "There are problem with passing your request. Please, try again.",
            );
            User::responseLogData($requestLogId,$jsonResponse,1);
            return response()->json(parseResponse($jsonResponse), 500);
        }
    }
    
    
}// END OF CLASS