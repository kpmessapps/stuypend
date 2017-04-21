@extends('layouts.common')

@section('pageTitle','User List')

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
</style>
@endsection

@section('mainContainerArea')
<div class="row">
    <div class="col-lg-12">
        <div class="ibox ">
            <div class="ibox-title">
                <h5>User Master</h5>
                <div class="pull-right">
                </div>
                <div style="clear:both;"></div>
            </div>

            <div class="ibox-content">
                <form role="form" class="form-inline">
                    <div class="form-group">
                        <input type="text" placeholder="Enter name" id="name" class="form-control">
                    </div>
                    <div class="form-group">
                        <input type="text" placeholder="Enter Email" id="email" class="form-control">
                    </div>
                    <div class="form-group">
                            <select class="form-control" id="type" name="type">
                                    <option value="">All</option>
                                    <option value="1">Admin Only</option>
                                    <option value="2">User Only</option>
                            </select>
                    </div>
                    <a onclick="return gridReload();" class="btn btn-white" href="javascript:void(0);">Search</a>
                </form>
            </div>

            <div class="ibox-content">
                <!--     <h4>User Master</h4> 
                     <p>
 
                     </p>    -->
                <div class="jqGrid_wrapper">
                    <table id="table_list_2"></table>
                    <div id="pager_list_2"></div>
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
                        function runScript(e) {
                        if (e.keyCode == 13) {
                        gridReload();
                        }
                        }

                        function sendNewClick(link){
                        window.location.href = link;
                        }

                        function gridReload(){
                        var email = jQuery("#email").val();
                        var type = jQuery("#type").val();
                        var name = jQuery("#name").val();
                        jQuery("#table_list_2").jqGrid('setGridParam', {url:"{{route('admin::userLoadListData')}}?type=" + type + "&email=" + email + "&name=" + name , page:1}).trigger("reloadGrid");
                        }
                        $(document).ready(function () {



                        // Configuration for jqGrid Example 2
                        $("#table_list_2").jqGrid({
                        // data: mydata,
                        url:'{{route('admin::userLoadListData')}}?q=1',
                                datatype: "json",
                                //datatype: "local",
                                height: 450,
                                multiselect: false,
                                multikey: "ctrlKey",
                                autowidth: true,
                                shrinkToFit: true,
                                rowNum: 20,
                                rowList: [10, 20, 30],
                                colNames:['Id', 'Name', 'Email','Type', 'Total answers','Register Date','Action'], //, 'Action'
                                colModel:[
                                {name:'u_id', index:'u_id', editable: false, width:30, sorttype:"int", align:"center", search:false},
                                {name:'name', index:'name', editable: false, width:80 },
                                {name:'u_email', index:'u_email', editable: false, width:80 },
                                {name:'type', index:'type', editable: false, width:50 },
                                {name:'u_total_activity_attempted', index:'u_total_activity_attempted', editable: false, sortable:true, width:80 },
                                {name:'u_created_date', index:'u_created_date', editable: false, sortable:true, width:60 },
                                {name:'u_action', index:'u_action', editable: false, sortable:false, width:30 },
                                ],
                                pager: "#pager_list_2",
                                viewrecords: true,
                                caption: "User Master List",
                                add: false,
                                edit: false,
                                addtext: 'Add',
                                edittext: 'Edit',
                                hidegrid: false,
                                editurl: "{{route('admin::userGridDataAction')}}",
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
                        $(".userInfoBox").colorbox({width : '80%', maxHeight : '90%', minHeight : '80%'});
                        $('.js-switch-grid-action').each(function(){
                        var switchery = new Switchery(this, {  size: 'small', color: '#1AB394', secondaryColor : '#ED5565' });
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
                        if (timestamp != '' && !isNaN(timestamp))
                        {
                        var localDate = formatDateLocal('dd-MM-yyyy hh:mm TT', timestamp * 1000);
                        $(this).html(localDate);
                        }
                        });
                        }

                        function changeSwitch(id) {
                        var switchAction = $('#' + id).attr('rel');
                        var dataId = $('#' + id).val();
                        var action = ($('#' + id).prop('checked') == true) ? '1' : '2';
                        $.ajax({
                        url: '{{route('admin::userGridDataAction')}}',
                                type: 'POST',
                                data:{id: dataId, oper:switchAction, action:action},
                                async: false,
                                cache: false,
                                timeout: 30000,
                                error: function(){
                                return true;
                                },
                                success: function(data){
                                var obj = $.parseJSON(data);
                                if (obj.success == "true") {
                                toastr.success(obj.message, 'Success');
                                } else{
                                toastr.success(obj.message, 'Error');
                                }
                                }
                        });
                        return true;
                        }///

                        });

</script>
@endsection