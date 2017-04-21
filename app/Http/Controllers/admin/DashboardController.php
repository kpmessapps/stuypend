<?php

namespace App\Http\Controllers\admin;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use DB;
use Hash;
use Illuminate\Http\Request as Request;
use Auth;
use Validator;
use Illuminate\Routing\Redirector;
class DashboardController extends Controller
{
    public function __construct(Redirector $redirect)
    {
        $this->middleware(function ($request, $next) {
            if(!$request->session()->has('LOGGED_IN')){
                return redirect()->route('admin::login');
            }
            return $next($request);
        });
//        if(User::checkAdminSession() === false){   
//             $redirect->to('/admin/logout')->send();
//        }
    }
    
    public function doLogout(Request $request) {
        Auth::logout();
        $request->session()->flush();
        return redirect()->route('admin::login');
    }
    
    public function getIndex()
    {
        $z = date("Z",time());
        if($z >= 0){
            $z = "+".trim($z,'+');
        }
        
        if(date('D', time()+$z) == "Mon") {
            $lastMondayTime = strtotime('This Monday', time()+$z);
        }else{
            $lastMondayTime = strtotime('Last Monday', time()+$z);
        }
        
        $thisMonthTime 	= strtotime( 'first day of ' . date( 'F Y')) ;
        
        $monthArr = [
            '1' => "January",
            '2' => "February",
            '3' => "March",
            '4' => "April",
            '5' => "May",
            '6' => "June",
            '7' => "July",
            '8' => "August",
            '9' => "September",
            '10' => "October",
            '11' => "November",
            '12' => "December",
        ];
        
        for($i = date('Y'); $i >= 2016;$i--){
            $yearArr[] = $i;
        }
    
        $qry = "SELECT
                    count(u_id) as totalCount
                FROM
                    `a_users` as u 
                WHERE
                    date(FROM_UNIXTIME(u_created_date ".$z.")) = '".date('Y-m-d')."' AND
                    u_status = 1
		"; 
        $todayNewActivatedUsers =  DB::select($qry);
        
        $qry = "SELECT
                    count(u_id) as totalCount
                FROM
                    `a_users` as u 
                WHERE
                    u_created_date >= '".$lastMondayTime."' AND
                    u_status = 1
		"; 
        $weekNewActivatedUsers =  DB::select($qry);
        
        $qry = "SELECT
                    count(u_id) as totalCount
                FROM
                    `a_users` as u 
                WHERE
                    u_created_date >= '".$thisMonthTime."' AND
                    u_status = 1
		"; 
        $monthNewActivatedUsers =  DB::select($qry);
        
        $qry = "SELECT
                    count(ans_id) as totalCount
                FROM
                    `a_answers`
                WHERE
                    ans_date = '".date('Y-m-d')."' AND
                    ans_status = 1
		"; 
        $todayAnswers =  DB::select($qry);
        
        $qry = "SELECT
                    count(ans_id) as totalCount
                FROM
                    `a_answers`
                WHERE
                    ans_created_date >= '".$lastMondayTime."' AND
                    ans_status = 1
		";
        $weekAnswers =  DB::select($qry);
        
        $qry = "SELECT
                    count(ans_id) as totalCount
                FROM
                    `a_answers`
                WHERE
                    ans_created_date >= '".$thisMonthTime."' AND
                    ans_status = 1
		"; 
        $monthAnswers =  DB::select($qry);

        $sql = "
				SELECT 
                                    *
				FROM
					`a_activities`
				WHERE
                                        act_type = '1' AND
					act_status != '9'
                                ORDER BY
					act_desc ASC";
        $activityList = DB::select($sql);
        $activityList = parseResponse($activityList);
        
        $data = [
                    'dashboardTab'      => "active",
                    'activityList'      => $activityList,
                    'todayNewActivatedUsers' 	=> $todayNewActivatedUsers[0]->totalCount,
                    'weekNewActivatedUsers' 	=> $weekNewActivatedUsers[0]->totalCount,
                    'monthNewActivatedUsers' 	=> $monthNewActivatedUsers[0]->totalCount,
                    'todayAnswers'            => $todayAnswers[0]->totalCount,
                    'weekAnswers'             => $weekAnswers[0]->totalCount,
                    'monthAnswers'            => $monthAnswers[0]->totalCount,
                    'currentMonth'              => date('n'),
                    'currentYear'               => date('Y'),
                    'monthArr'                  => $monthArr,
                    'yearArr'                   => $yearArr,
                ];
   //     dd($data);
        return view('admin.dashboard.dashboard')->with($data);
    }
    
    public function ajaxUserGraphReport()
    {
        $name         = Input::get('name','');
        $isAdmin    = Input::get('isAdmin','');
        $year       = Input::get('year',date('Y'));
        $month = Input::get('month','');
        $status         = Input::get('status','');
        $z = date("Z",time());
        if($z >= 0){
            $z = "+".trim($z,'+');
        }
        
        $where = "";
        
        if($isAdmin > 0){
            $where .= " u_is_admin = '".$isAdmin."' AND ";
        }
        
        if($status != ''){
            $where .= " u_status = '".$status."' AND ";
        }else{
            $where .= " u_status != '9' AND ";
        }
        
        if($name != ""){
            $where .= " CONCAT(u_first_name,' ',u_last_name) LIKE '".$name."%' AND ";
        }
        
        if($month > 0){
            $totalDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $thisMonthStartTime     = strtotime( $year."-".$month."-01 00:00:00") ;
            $thisMonthEndTime       = strtotime( $year."-".$month."-".$totalDaysInMonth." 23:59:59") ;

            $sql = "SELECT
                                    date(FROM_UNIXTIME(u_created_date ".$z.")) as date,count(u_id) as totalCount
                            FROM
                                    `a_users` as u
                            WHERE
                                    ".$where."
                                    u_created_date >= '".$thisMonthStartTime."' AND
                                    u_created_date <= '".$thisMonthEndTime."' AND
                                    u_status != 9
                            GROUP BY
                                    date(FROM_UNIXTIME(u_created_date ".$z."))
                            ORDER BY
                                    date ASC
                    ";
            $tempThisMonthAllDate = DB::select($sql);
            $tempThisMonthAllDate = parseResponse($tempThisMonthAllDate);

            $monthLineChartArr = array();
            for($i = 1; $i <= $totalDaysInMonth;$i++){
                    $d = $i;
                    if($i < 10){
                            $d = "0".$d;
                    }
                    $monthLineChartArr[$d] = 0;
            }

            foreach($tempThisMonthAllDate as $key => $value){
                    $dateEx = explode('-',$value['date']);
                    $iDate = $dateEx['2'];
                    $monthLineChartArr[$iDate] = $value['totalCount'];
            }
            $x_graph = [];
            $y_graph = [];
            foreach($monthLineChartArr as $key => $value){
                    $x_graph[] = $key;
                    $y_graph[] = $value;
            }
        }else{
            $thisYearStartTime     = strtotime( $year."-01-01 00:00:00") ;
            $thisYearEndTime       = strtotime( $year."-12-31 23:59:59") ;
            
            for($i=0;$i<12;$i++){
                $y_graph[$i] = 0;
            }
            
            $sql = "SELECT
                    YEAR(reportYearMonth) AS reportYear,
                    MONTH(reportYearMonth) AS reportMonth,
                    reportValue
                  FROM (
                    SELECT
                        LAST_DAY(FROM_UNIXTIME(a.u_created_date ".$z.")) as reportYearMonth,
                        COUNT(u_id) AS reportValue
                    FROM  
                        a_users AS a
                    WHERE 
                        ".$where."
                        u_status = '1' AND
                        u_created_date >= '".$thisYearStartTime."' AND
                        u_created_date <= '".$thisYearEndTime."'
                    GROUP BY
                      reportYearMonth
                  ) AS s
                  ORDER BY
                    reportYear            ASC,
                    MONTH(reportYearMonth) ASC
                  ";
            $dataList = DB::select($sql);
            $result = parseResponse($dataList);


            $data = array();
            foreach($result as $key => $value){
                $month = $value['reportMonth'];
                $y_graph[$month-1] = $value['reportValue'] * 1;
            }
         
            $x_graph = ["January","February","March","April","May","June","July","August","September","October","November","December"];
        }
        
        $jsonResponse = array(
                "statusCode"        => "1",
                "errors"            => array(),
                "message"           => "Success.",
                'x_graph'           => $x_graph,
                'y_graph'           => $y_graph,
                'graphTitle'        => $sql,
            );
        return response(parseResponse($jsonResponse), 200)->header('Content-Type', "json");
    }
    
    
    public function ajaxAnswerGraphReport()
    {
        $activityId  = Input::get('activityId','');
        $answerYear  = Input::get('answerYear',date('Y'));
        $answerMonth = Input::get('answerMonth','0');
        
        $z = date("Z",time());
        if($z >= 0){
            $z = "+".trim($z,'+');
        }
        
        $totalDaysInMonth = cal_days_in_month(CAL_GREGORIAN, date('m'), date('y'));
        
        $where = "";
        if($activityId > 0){
            $where .= " ans_act_id = '".$activityId."' AND ";
        }
        
        if($answerMonth > 0){
            $thisMonthStartTime     = strtotime( $answerYear."-".$answerMonth."-01 00:00:00") ;
            $thisMonthEndTime       = strtotime( $answerYear."-".$answerMonth."-".$totalDaysInMonth." 23:59:59") ;

            $sql = "SELECT
                                    date(FROM_UNIXTIME(ans_created_date ".$z.")) as date,count(ans_id) as totalCount
                            FROM
                                    `a_answers`
                            WHERE
                                    ".$where."
                                    ans_created_date >= '".$thisMonthStartTime."' AND
                                    ans_created_date <= '".$thisMonthEndTime."' AND
                                    ans_status = 1
                            GROUP BY
                                    date(FROM_UNIXTIME(ans_created_date ".$z."))
                            ORDER BY
                                    date ASC
                    ";
            $tempThisMonthAllDate = DB::select($sql);
            $tempThisMonthAllDate = parseResponse($tempThisMonthAllDate);

            $totalDaysInMonth = cal_days_in_month(CAL_GREGORIAN, date('m'), date('y'));

            $monthLineChartArr = array();
            for($i = 1; $i <= $totalDaysInMonth;$i++){
                    $d = $i;
                    if($i < 10){
                            $d = "0".$d;
                    }
                    $monthLineChartArr[$d] = 0;
            }

            foreach($tempThisMonthAllDate as $key => $value){
                    $dateEx = explode('-',$value['date']);
                    $iDate = $dateEx['2'];
                    $monthLineChartArr[$iDate] = $value['totalCount'];
            }
            $x_graph = [];
            $y_graph = [];
            foreach($monthLineChartArr as $key => $value){
                    $x_graph[] = $key;
                    $y_graph[] = $value;
            }
        }else{
            $thisYearStartTime     = strtotime( $answerYear."-01-01 00:00:00") ;
            $thisYearEndTime       = strtotime( $answerYear."-12-31 23:59:59") ;
            
            for($i=0;$i<12;$i++){
                $y_graph[$i] = 0;
            }
            
            $sql = "SELECT
                    YEAR(reportYearMonth) AS reportYear,
                    MONTH(reportYearMonth) AS reportMonth,
                    reportValue
                  FROM (
                    SELECT
                        LAST_DAY(FROM_UNIXTIME(a.ans_created_date ".$z.")) as reportYearMonth,
                        COUNT(ans_id) AS reportValue
                    FROM  
                        a_answers AS a
                    WHERE 
                        ".$where."
                        ans_status = '1' AND
                        ans_created_date >= '".$thisYearStartTime."' AND
                        ans_created_date <= '".$thisYearEndTime."'
                    GROUP BY
                      reportYearMonth
                  ) AS s
                  ORDER BY
                    reportYear            ASC,
                    MONTH(reportYearMonth) ASC
                  ";
            $dataList = DB::select($sql);
            $result = parseResponse($dataList);


            $data = array();
            foreach($result as $key => $value){
                $month = $value['reportMonth'];
                $y_graph[$month-1] = $value['reportValue'] * 1;
            }
         
            $x_graph = ["January","February","March","April","May","June","July","August","September","October","November","December"];
        }
        
        $jsonResponse = array(
                "statusCode"        => "1",
                "errors"            => array(),
                "message"           => "Success.",
                'x_graph'           => $x_graph,
                'y_graph'           => $y_graph,
                'graphTitle'        => $sql,
            );
        return response(parseResponse($jsonResponse), 200)->header('Content-Type', "json");
    }
    
    public function settings()
    {
		$data = array(
                "settingsTab"   => "active",
                "detail"        => [

                        ]
            );
	//	dd($data);
		return view('admin.dashboard.settings')->with($data);
    }
    
	
    public function doSettingsEdit(){
			
		
		return redirect()->route('admin::settings')->with('successMessage', 'Settings updated successfully!');
	
	
    }
    
    public function changePassword(){
        $data = [
            "changePasswordTab"            => "active",
        ];
        return view('admin.dashboard.changepassword')->with($data);
    }
    
    public function doChangePasswordEdit(){
	
        $adminId = Auth::id();
        
        // validate the info, create rules for the inputs
        $rules = array(
            'currentPassword'     => 'required',
            'newPassword'  => 'required|min:3|alpha_dash|same:confirmPassword'
        );

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            return redirect()->route('admin::changePassword')->with('errorMessage', (isset($messages['0']) ? $messages['0'] :  "Please, Fill all the mendotary detail."));
        } 
        
        $oldPassword	= Input::get('currentPassword');
        $newPassword	= Input::get('newPassword');
        
        $existPassword = DB::table('a_users')->where('u_is_admin','1')->where('u_id',$adminId)->where('u_status','1')->value('u_password');
      //  $existPassword = Hash::make($oldPassword);
        $credentials = [
            'u_id' => $adminId,
            'password' => $oldPassword,
            'u_is_admin' => '1',
            'u_status'    => '1'
        ];
        if (!Auth::validate($credentials))
        {
            return redirect()->route('admin::changePassword')->with('errorMessage', 'Your current password doesn\'t match!');
        }
        
        $updateData = [
                'u_password'        => Hash::make($newPassword),
                'u_modified_date'   => time(),  
            ];
        $isUpdated = DB::table('a_users')->where('u_is_admin','1')->where('u_id',$adminId)->where('u_status','1')->update($updateData);

        if($isUpdated){
            return redirect()->route('admin::changePassword')->with('successMessage', 'Password updated successfully!');
        }else{
            return redirect()->route('admin::changePassword')->with('errorMessage', 'There are something problem with updating your password!');
        }
    }
    
}