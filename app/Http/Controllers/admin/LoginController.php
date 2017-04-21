<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request as Request;
use Validator;
use App\User;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Routing\Redirector;

class LoginController extends Controller {

    public function __construct(Redirector $redirect) {
//        if (User::checkAdminSession() === true) {
//            $redirect->to('/admin/dashboard')->send();
//        }

        $this->middleware(function ($request, $next) {
            if ($request->session()->has('LOGGED_IN')) {
                return redirect()->route('admin::dashboard');
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
    //ALTER TABLE `a_users` ADD `u_is_admin` TINYINT(1) NOT NULL DEFAULT '2' COMMENT '1: admin , 2: Normal user' AFTER `u_last_login_date`;
    public function getIndex() {
        return view('admin.login')->with('loginTab', "active");
    }

    public function doLogin(Request $request) {


        // validate the info, create rules for the inputs
        $rules = array(
            'username' => 'required', // make sure the email is an actual email
            'password' => 'required|min:3' // password can only be alphanumeric and has to be greater than 3 characters
        );

        // run the validation rules on the inputs from the form
        $validator = Validator::make($request->all(), $rules);

        // if the validator fails, redirect back to the form
        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            return redirect()->route('admin::login')->with('errorMessage', "The entered email and password don't match. Please try again.");
        } else {

            // create our user data for the authentication
            $userdata = array(
                'u_email'   => $request->input('username'),
                'password'  => $request->input('password'),
                'u_reg_type' => "1",
                'u_status' => "1",
            );
            // dd(Auth::attempt($userdata));
            // dd($userdata);
            // attempt to do the login
            if (Auth::attempt($userdata, true)) {
                $request->session()->push('LOGGED_IN', "YES");
                $request->session()->push('USER_ID', Auth::user()->u_id);
//                dd($request->session()->get('LOGGED_IN'));
//                // validation successful!
                // redirect them to the secure section or whatever
                // return Redirect::to('secure');
                // for now we'll just echo success (even though echoing in a controller is bad)
                return redirect()->route('admin::dashboard');
            } else {
                // validation not successful, send back to form 
                return redirect()->route('admin::login')->with('errorMessage', "The entered email and password don't match. Please try again.");
            }
        }
    }

}
