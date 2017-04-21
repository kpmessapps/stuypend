<?php

namespace App\Http\Controllers\admin;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use DB;
use Request;
use Auth;
use Validator;
use Illuminate\Routing\Redirector;
use Hash;

class UserAdminController extends Controller {

    public function __construct(Redirector $redirect) {
//        if (User::checkAdminSession() === false) {
//            $redirect->to('/admin/logout')->send();
//        }
        $this->middleware(function ($request, $next) {
            if (!$request->session()->has('LOGGED_IN')) {
                return redirect()->route('admin::login');
            }
            return $next($request);
        });
    }

    /**
     * Show the Dashboard for the logged in admin.
     *
     * @param  
     * @return Response HTML
     */
    public function getIndex() {
        return view('admin.user.list')->with('userTab', "active");
    }

    /**
     * Session destroy for the logged in admin.
     *
     * @param  
     * @return redirect to login
     */
    public function logout() {
        Auth::logout();
        Session::flush();
        return redirect()->route('admin::login');
    }

    public function loadListData() {
        $page = Input::get('page');
        $limit = Input::get('rows');
        $sidx = Input::get('sidx');
        $sord = Input::get('sord');
        $name = Input::get('name', '');
        $email = Input::get('email', '');
        $type = Input::get('type', '');

        if ($sidx == "") {
            $sidx = "u_id";
            $sord = "desc";
        }

        $qryData = array();
        $where = "";

        if ($name != "") {
            $where .=" AND CONCAT(u_first_name,' ',u_last_name) Like '" . $name . "%' ";
        }
        
        if ($email != "") {
            $where .=" AND u_email Like '" . $email . "%' ";
        }
        
        if ($type == "1" || $type == "2") {
            $where .=" AND u_is_admin = '" . $type . "' ";
        }

        /*
          if($status != ""){
          $where .=" AND u_status = :status ";
          $qryData['status']	= $status;
          } */

        //	DB::enableQueryLog();
        $countQuery = "SELECT COUNT(*) AS count  FROM a_users WHERE u_is_admin != '1' AND u_status != '9' " . $where;
        $result = DB::select($countQuery, $qryData);
        $count = $result[0]->count;
        //	dd(DB::getQueryLog());
        $total_pages = ( $count > 0 ) ? ceil($count / $limit) : 0;
        $total_pages = ( $page > $total_pages ) ? $page = $total_pages : $total_pages;
        $start = $limit * $page - $limit; // do not put $limit*($page - 1)
        $start = ( $start < 0 ) ? 0 : $start;

        $sql = "
				SELECT
					*,CONCAT(u_first_name,' ',u_last_name) as name,IF(u_is_admin=1,'Admin','App User') as type
				FROM
					`a_users` as u
				WHERE
					u_status != '9'
					" . $where . "
				ORDER BY
					" . $sidx . " " . $sord . "
				LIMIT
					" . $start . " , " . $limit;
        $resourceList = (array) DB::select($sql, $qryData);

        $responce = array();
        $responce['page'] = $page;
        $responce['total'] = $total_pages;
        $responce['records'] = $count;

        $i = 0;

        foreach ($resourceList as $key => $row) {
            $row = (array) $row;

            $responce['rows'][$i]['id'] = $row['u_id'];
            $responce['rows'][$i]['cell'] = array(
                $row['u_id'],
                $row['name'],
                $row['u_email'],
                $row['type'],
                $row['u_total_activity_attempted'],
                date("Y-m-d H:i:s",$row['u_created_date']),
                '<a href="' . route('admin::userActivityList', ['userId' => $row['u_id']]) . '" class="btn btn-default btn-xs" style="color:white;"><i class="fa fa-folder-open"></i></a>'.' '.
                '<a href="' . route('admin::filterAnswerList', ['dataId' => $row['u_id'],'type' => 'user']) . '" class="btn btn-default btn-xs" style="color:white;"><i class="fa fa-list-alt"></i></a>'
            );
            $i++;
        }
        echo json_encode($responce);
        exit;
    }

}
