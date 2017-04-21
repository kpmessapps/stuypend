@extends('layouts.common')

@section('pageTitle','User Profile')

@section('newCssLoad')

<link href="{!! asset('asset/css/bootstrap.min.css') !!}" rel="stylesheet">
    <link href="{!! asset('asset/font-awesome/css/font-awesome.css') !!}" rel="stylesheet">
    <link href="{!! asset('asset/css/animate.css') !!}" rel="stylesheet">
    <link href="{!! asset('asset/css/plugins/jQueryUI/jquery-ui-1.10.4.custom.min.css') !!}" rel="stylesheet">
    <link href="{!! asset('asset/css/plugins/jqGrid/ui.jqgrid.css') !!}" rel="stylesheet">
    <link href="{!! asset('asset/css/style.css') !!}" rel="stylesheet">
	<link href="{!! asset('asset/plugins/switchery/switchery.css') !!}" rel="stylesheet" type="text/css" />
    <!-- Toastr style -->
    <link href="{!! asset('asset/plugins/toastr/toastr.min.css') !!}" rel="stylesheet">
	<link href="{!! asset('asset/plugins/colorbox/data/colorbox.css') !!}" rel="stylesheet" type="text/css" />
    <style>
        /* Additional style to fix warning dialog position */
        #alertmod_table_list_2 {
            top: 900px !important;
        }
		.dl-horizontal dt{
			padding: 3px 0;
			text-align: left;
		}
    </style>
@endsection

@section('mainContainerArea')
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <h5>User Profile : {{ $detail['u_first_name']}} {{ $detail['u_last_name']}}</h5>
                </div>
                <div class="ibox-content">
                    <div class="row">
						<div class="col-lg-12">
							<div class="m-b-md">
								<a class="btn btn-white btn-xs pull-right" href="{{route('admin::doUserEdit', ['id' => $detail['u_id'] ] )}}">Edit User</a>
								<h2>{{ $detail['u_first_name']}} {{ $detail['u_last_name']}}</h2>
							</div>
							<dl class="dl-horizontal">
								<dt>Status:</dt>
								<dd>
									@if ($detail['u_status'] == '1' )
										<span class="label label-primary">Active</span>
									@else
										<span class="label label-danger">Deactive</span>
									@endif
									
								</dd>
							</dl>
						</div>
					</div>
					
					<div class="row">
						<div class="col-lg-5">
							<dl class="dl-horizontal">

								<dt>Email:</dt> <dd>{{$detail['u_email']}}</dd>
								<dt>Register From:</dt> <dd>  {{$detail['u_reg_type']}}</dd>
								<dt>Student:</dt> <dd>  {{$detail['u_is_student']}}</dd>
								<dt>Campus:</dt> <dd>  {{$detail['cps_name']}}</dd>
                                                                <dt>Campus Email:</dt> <dd>  {{$detail['cps_active_email']}}</dd>
								<dt>Total Reefill:</dt> <dd>  {{$detail['u_total_reefill']}}</dd>
								<dt>Notification:</dt> <dd>  {{$detail['u_is_notification_on']}}</dd>
								<dt>Unit Measurment	:</dt> <dd>  {{$detail['u_unit_measurment']}}</dd>
								<dt>Created:</dt> <dd>{{$detail['u_created_date']}}</dd>
								@if ($detail['refUserId'] > 0 )
									<dt>Refferal User:</dt> <dd><a style="color: #1ab394;cursor:pointer;"  class="text-navy" href="{{ route('admin::userDetail', ['id' => $detail['refUserId']]) }}"> {{$detail['refUserName']}}</a> </dd>
								@else
									<dt>Refferal User:</dt> <dd> - </dd>
								@endif
							</dl>
						</div>
						<div id="cluster_info" class="col-lg-7">
							<dl class="dl-horizontal">
								<dt>Last Reefill Time:</dt> <dd>  {{$detail['u_last_reefill_time']}}</dd>
								<dt>Membership Type:</dt> <dd>  {{$detail['u_mem_type']}}</dd>
								<dt>Membership From:</dt> <dd>  {{$detail['u_mem_last_updated_date']}}</dd>
								<dt>Stripe Customer Id:</dt> <dd>  {{$detail['u_stripe_customer_id']}}</dd>
								<dt>Stripe Subscription Id:</dt> <dd>  {{$detail['sub_stripe_subscription_id']}}</dd>
								<dt>Trial Up to:</dt> <dd>  {{$detail['sub_trial_ends_at']}}</dd>
							</dl>
						</div>
					</div>
					
					<div class="row">
						<div class="jqGrid_wrapper">
							<table id="table_list_2"></table>
							<div id="pager_list_2"></div>
						</div>
					</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('newJsLoad')
    <!-- jqGrid -->
    <script src="{!! asset('asset/js/plugins/jqGrid/i18n/grid.locale-en.js') !!}"></script>
    <script src="{!! asset('asset/js/plugins/jqGrid/jquery.jqGrid.min.js') !!}"></script>

    <!-- Custom and plugin javascript -->
    <script src="{!! asset('asset/js/inspinia.js') !!}"></script>
    <script src="{!! asset('asset/js/plugins/pace/pace.min.js') !!}"></script>
    
	<!-- Switchery -->
    <script src="{!! asset('asset/plugins/switchery/switchery.js') !!}"></script>
   
   <!-- KD6446 Colorbox for Colorbox purposes -->
    <script src="{!! asset('asset/plugins/colorbox/jquery.colorbox-min.js') !!}" type="text/javascript"></script>
		
	 <!-- Toastr script -->
    <script src="{!! asset('asset/plugins/toastr/toastr.min.js') !!}"></script>
        
    <script src="{!! asset('asset/js/plugins/jquery-ui/jquery-ui.min.js') !!}"></script>
	
	
	
@endsection

@section("customPageJs")
    <script>
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "progressBar": true,
            "preventDuplicates": false,
            "positionClass": "toast-top-right",
            "showDuration": "100",
            "hideDuration": "100",
            "timeOut": "1200",
            "extendedTimeOut": "500",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
          }

        $(document).ready(function () {
  
            // Configuration for jqGrid Example 2
            $("#table_list_2").jqGrid({
               // data: mydata,
                url:'{{route('admin::userReefillHistoryLoadListData')}}?q=1&userId={{ $detail['u_id'] or '' }}',
				datatype: "json",
                //datatype: "local",
                height: 250,
                multiselect: false,
                multikey: "ctrlKey",
                autowidth: true,
                shrinkToFit: true,
                rowNum: 20,
                rowList: [10, 20, 30],
                colNames:['Id','Station Name', 'Station Address','Campus Name','Reefill Date'],
                colModel:[
                    {name:'ref_his_id',index:'ref_his_id', editable: false, width:30, sorttype:"int",align:"center",search:false},
                    {name:'wtr_st_name',index:'wtr_st_name', editable: false, width:100 },
                    {name:'wtr_st_address',index:'wtr_st_address', editable: false, width:120},
                    {name:'cps_name',index:'cps_name', editable: false, width:100},
                    {name:'ref_his_created_date',index:'ref_his_created_date', editable: false, width:120, align:"center"},
                ],
                pager: "#pager_list_2",
                viewrecords: true,
                caption: "User Reefill History List",
                add: false,
                edit: false,
                addtext: 'Add',
                edittext: 'Edit',
                hidegrid: false,
                editurl: "#",
                loadComplete: function(){
                    reloadAfterGrid();
                    localTime();
                }
            });

             // Add selection
            $("#table_list_2").setSelection(4, true);


            // Setup buttons
            $("#table_list_2").jqGrid('navGrid', '#pager_list_2',
                    {edit: false, add: false, del: false, search: false},
                    {height: 200, reloadAfterSubmit: false}
            );
            
            var width = $('.jqGrid_wrapper').width();
            $('#table_list_2').setGridWidth(width);
                
            // Add responsive to jqGrid
            $(window).bind('resize', function () {
                var width = $('.jqGrid_wrapper').width();
                $('#table_list_2').setGridWidth(width);
            });
            
            
            
            function reloadAfterGrid() {
                $(".userInfoBox").colorbox({width : '80%',maxHeight : '90%',minHeight : '80%'});
                
                $('.js-switch-grid-action').each(function(){
                    var switchery = new Switchery(this, {  size: 'small',color: '#1AB394', secondaryColor : '#ED5565' });
                    this.onchange = function() {
                            changeSwitch(this.id);
                      };
                });
            /*    $("#dedata").click(function(){
                    var gr = jQuery("#table_list_2").jqGrid('getGridParam','selrow');
                    if( gr != null ) jQuery("#table_list_2").jqGrid('delGridRow',gr,{reloadAfterSubmit:true});
                    else alert("Please Select Row to delete!");
                }); */
            }
            
            function localTime(){
                $('.timestamp').each(function(){
                        var timestamp = parseInt($(this).html());
                        if(timestamp != '' && !isNaN(timestamp))
                        {
                            var localDate = formatDateLocal('dd-MM-yyyy hh:mm TT',timestamp*1000);
                            $(this).html(localDate);
                        }
                    });
            }
            
            function changeSwitch(id) {
                var switchAction = $('#'+id).attr('rel');
                var dataId       = $('#'+id).val();
                var action       = ($('#'+id).prop('checked')==true) ? '1' : '2';
                
                $.ajax({
                    url: '{{route('admin::userGridDataAction')}}',
                    type: 'POST',
                    data:{id: dataId,oper:switchAction,action:action},
                    async: false,
                    cache: false,
                    timeout: 30000,
                    error: function(){
                        return true;
                    },
                    success: function(data){
                        var obj = $.parseJSON(data);
                        if (obj.success == "true") {
                            toastr.success(obj.message,'Success');
                        }else{
                             toastr.success(obj.message,'Error');
                        }
                    }
                });
                
                return true;
                
            }///

        });

    </script>
@endsection