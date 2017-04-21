@extends('layouts.common')

@section('pageTitle','Update App Config')

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
                            <h5>Reefill : Settings</h5>
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
													
							<form method="post" class="form-horizontal" action="{{route('admin::doSettingsEdit')}}" name="appSettings"  accept-charset="UTF-8">
                                        
								<div class="form-group">
									<label class="col-sm-3 control-label">Station On time (Seconds)</label>
									<div class="col-sm-9">
										<input type="text" class="form-control" placeholder="Station On Time (In seconds)" name="reefillTimeAllow" id="reefillTimeAllow" value="{{ $detail['reefillTimeAllow'] or '' }}" >
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">Twitter Username</label>
									<div class="input-group col-sm-9" style="padding: 0 15px;">
										<span class="input-group-addon">@</span>
										<input type="text" class="form-control" placeholder="twitterusername" name="reefillTwitterUsername" id="reefillTwitterUsername" value="{{ $detail['reefillTwitterUsername'] or '' }}" >
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">Support Phone</label>
									<div class="col-sm-9">
										<input type="text" class="form-control" data-mask="(999) 999-9999" placeholder="" name="reefillSupportPhone" id="reefillSupportPhone" value="{{ $detail['reefillSupportPhone'] or '' }}" >
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">Support Email</label>
									<div class="col-sm-9">
										<input type="text" class="form-control" placeholder="Support Email" name="reefillSupportEmail" id="reefillSupportEmail" value="{{ $detail['reefillSupportEmail'] or '' }}" >
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">Max Station distance range allow to list (Miles)</label>
									<div class="col-sm-9">
										<input type="text" class="form-control" placeholder="In Miles" name="maxStationDistanceRangeAllowTosee" id="maxStationDistanceRangeAllowTosee" value="{{ $detail['maxStationDistanceRangeAllowTosee'] or '' }}" >
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">Max Nearest Station distance (Miles)</label>
									<div class="col-sm-9">
										<input type="text" class="form-control" placeholder="In Miles" name="maxNearestStationDistance" id="maxNearestStationDistance" value="{{ $detail['maxNearestStationDistance'] or '' }}" >
									</div>
								</div>
                                                                <div class="form-group">
									<label class="col-sm-3 control-label">Max Nearest Station distance for badge count (Miles)</label>
									<div class="col-sm-9">
										<input type="text" class="form-control" placeholder="In Miles" name="maxNearestStationDistanceForCount" id="maxNearestStationDistanceForCount" value="{{ $detail['maxNearestStationDistanceForCount'] or '' }}" >
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
			
		$(document).ready(function () {
			$("form").validate({
                 rules: {
                     reefillTimeAllow: {
                         required: true,
                     },
					 reefillTwitterUsername: {
                         required: true,
					 },
					 reefillSupportPhone: {
                         required: true,
					 },
					 reefillSupportEmail: {
                         required: true
                     },
                    maxStationDistanceRangeAllowTosee: {
                        required: true,
			number: true
                     },
                    maxNearestStationDistance: {
                        required: true,
			number: true
                    },
                    maxNearestStationDistanceForCount: {
                        required: true,
			number: true
                    }
                 },
					messages: {
                     reefillTimeAllow: {
                         required: 'Enter valid time in seconds.',
                     },
					 reefillTwitterUsername: {
                         required: 'Enter valid twitter username.',
                     },
					 reefillSupportPhone: {
                         required: 'Enter valid support phone.',
                     },
					 reefillSupportEmail: {
                         required: 'Enter valid support email.',
                     },
					 maxStationDistanceRangeAllowTosee: {
                         required: 'Enter valid max station distance range allow to list.',
						 number: 'Enter valid max station distance range allow to list.',
                     },
                    maxNearestStationDistance: {
                         required: 'Enter valid max nearest station distance.',
						 number: 'Enter valid max nearest station distance.',
                     }
                    maxNearestStationDistanceForCount: {
                        required: 'Enter valid max nearest station distance for badge count.',
			number: 'Enter valid max nearest station distance for badge count.',
                     }
                 }
								 
             });
						 
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