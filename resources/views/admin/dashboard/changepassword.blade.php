@extends('layouts.common')

@section('pageTitle','Update Admin Password')

@section('newCssLoad')

<link href="{!! asset('asset/css/bootstrap.min.css') !!}" rel="stylesheet">
<link href="{!! asset('asset/css/plugins/jasny/jasny-bootstrap.min.css') !!}" rel="stylesheet">
<link href="{!! asset('asset/font-awesome/css/font-awesome.css') !!}" rel="stylesheet">
<link href="{!! asset('asset/css/plugins/iCheck/custom.css') !!}" rel="stylesheet">
<link href="{!! asset('asset/css/animate.css') !!}" rel="stylesheet">
<link href="{!! asset('asset/css/style.css') !!}" rel="stylesheet">
<link rel="stylesheet" media="all" type="text/css" href="http://code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css" />
<link type='text/css'  href="{!! asset('asset/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css') !!}" rel="stylesheet">
	
<link href="{!! asset('asset/plugins/switchery/switchery.css') !!}" rel="stylesheet" type="text/css" />
<link href="{!! asset('asset/plugins/aehlke-tag-it/css/jquery.tagit.css') !!}" rel="stylesheet" type="text/css" />
<style>
	.form-control, #eduList {
    background-color: #ffffff;
    background-image: none;
    border: 1px solid #e5e6e7;
    border-radius: 1px;
    color: inherit;
    transition: border-color 0.15s ease-in-out 0s, box-shadow 0.15s ease-in-out 0s;
	}
	.form-control:focus, #eduList:focus{
		border-color:#1ab394 !important;
	}
  </style>
@endsection

@section('mainContainerArea')
           
        <div class="wrapper wrapper-content animated fadeInRight" style="z-index:0 !important;">
            
            <div class="row">
		<div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5><?= env('ADMIN_PROJECT_NAME');?> : Update admin password</h5>
                        </div>
                        <div class="ibox-content">
												
                            @if (session('errorMessage'))
                                    <div class="alert alert-danger">
                                                    {{ session('errorMessage') }}
                                    </div>
                            @endif

                            @if (session('successMessage'))
                                    <div class="alert alert-success">
                                                    {{ session('successMessage') }}
                                    </div>
                            @endif

                            <form method="post" class="form-horizontal" autocomplete="off" action="{{route('admin::doChangePasswordEdit')}}" name="changePasswordForm"  accept-charset="UTF-8">

                                    <div class="form-group">
                                            <label class="col-sm-2 control-label">Current password</label>
                                            <div class="col-sm-10">
                                                <input autocomplete="off" type="password" class="form-control" placeholder="Current Password" name="currentPassword" id="currentPassword" value="" >
                                            </div>
                                    </div>
                                    <div class="form-group">
                                            <label class="col-sm-2 control-label">New password</label>
                                            <div class="col-sm-10">
                                                <input autocomplete="off" type="password" class="form-control" placeholder="New Password" name="newPassword" id="newPassword" value="" >
                                            </div>
                                    </div>
                                    <div class="form-group">
                                            <label class="col-sm-2 control-label">Confirm password</label>
                                            <div class="col-sm-10">
                                                <input autocomplete="off" type="password" class="form-control" placeholder="Confirm Password" name="confirmPassword" id="confirmPassword" value="" >
                                            </div>
                                    </div>
                                    <div class="hr-line-dashed"></div>
                                    <div class="form-group">
                                            <div class="col-sm-4 col-sm-offset-2">
                                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                    <button class="btn btn-primary" type="submit">Submit</button>
                                            </div>
                                    </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection

@section('newJsLoad')
	
		<script type="text/javascript" src="http://code.jquery.com/ui/1.11.0/jquery-ui.min.js"></script>

    <!-- Custom and plugin javascript -->
    <script src="{!! asset('asset/js/inspinia.js') !!}"></script>
    <script src="{!! asset('asset/js/plugins/pace/pace.min.js') !!}"></script>
	
	<!-- Input Mask-->
    <script src="{!! asset('asset/js/plugins/jasny/jasny-bootstrap.min.js') !!}"></script>
		
		<!-- Jquery Validate -->
    <script src="{!! asset('asset/js/plugins/validate/jquery.validate.min.js') !!}"></script>


    <!-- iCheck -->
    <script src="{!! asset('asset/js/plugins/iCheck/icheck.min.js') !!}"></script>
    
		<!-- Switchery -->
    <script src="{!! asset('asset/plugins/switchery/switchery.js') !!}"></script>
	
		<script src="{!! asset('asset/plugins/aehlke-tag-it/js/tag-it.min.js') !!}"></script>
@endsection

@section("customPageJs")
	
    <script>
			
	
						 
			$('form').keypress(function(event) {
		
				if (event.keyCode == 13) {
					event.preventDefault();
				}
			});
			$('.i-checks').iCheck({
				checkboxClass: 'icheckbox_square-green',
				radioClass: 'iradio_square-green',
			});
			
			$('.js-switch-grid-action').each(function(){
				var switchery = new Switchery(this, {  size: 'small',color: '#1AB394', secondaryColor : '#ED5565' });
				this.onchange = function() {
						//changeSwitch(this.id);
				  };
			});
		});
	</script>
@endsection