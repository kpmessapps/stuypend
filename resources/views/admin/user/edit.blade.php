@extends('layouts.common')

@section('pageTitle','Update User Detail')

@section('newCssLoad')



<link href="{!! asset('asset/css/bootstrap.min.css') !!}" rel="stylesheet">
<link href="{!! asset('asset/font-awesome/css/font-awesome.css') !!}" rel="stylesheet">
<link href="{!! asset('asset/css/plugins/iCheck/custom.css') !!}" rel="stylesheet">
<link href="{!! asset('asset/css/animate.css') !!}" rel="stylesheet">
<link href="{!! asset('asset/css/plugins/chosen/chosen.css') !!}" rel="stylesheet">
<link href="{!! asset('asset/css/style.css') !!}" rel="stylesheet">
<link rel="stylesheet" media="all" type="text/css" href="http://code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css" />


<link type='text/css'  href="{!! asset('asset/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css') !!}" rel="stylesheet">

<link href="{!! asset('asset/plugins/switchery/switchery.css') !!}" rel="stylesheet" type="text/css" />
<style>
    .animated{
        z-index:0 !important;
    }
</style>
<style>
    #infoPanel {
        float: left;
        margin-left: 10px;
    }
    #infoPanel div {
        margin-bottom: 5px;
    }
</style>
@endsection

@section('mainContainerArea')

<div class="wrapper wrapper-content animated fadeInRight" style="z-index:0 !important;">

    <div class="row">

        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Update User Profile : {{ $detail['u_first_name'] or '' }}</h5>
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

                    <form method="post" class="form-horizontal" action="{{route('admin::doUserEdit', ['id' => $detail['u_id'] ] )}}" name="updateUser"  accept-charset="UTF-8">


                        <div class="form-group">
                            <label class="col-sm-2 control-label">Username</label>
                            <div class="col-sm-6">
                                <input type="text" placeholder="Username" class="form-control required" name="username" id="username" value="{{ $detail['u_username'] or '' }}">
                            </div>
                        </div>
                        
                        <div class="form-group appUserType">
                            <label class="col-sm-2 control-label">Country code</label>
                            <div class="col-sm-6">
                                <input type="text" placeholder="Country code" class="form-control digits" name="countryCode" id="countryCode" value="{{ $detail['u_phone_country_code'] or '' }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Phone Number</label>
                            <div class="col-sm-6">
                                <input type="text" placeholder="Phone Number" class="form-control digits" name="phoneNumber" id="phoneNumber" value="{{ $detail['u_phone'] or '' }}">
                            </div>
                        </div>

                        <div class="form-group appUserType">
                            <label class="col-sm-2 control-label">Is Admin</label>
                            <div class="col-sm-4">
                                <input class='js-switch-grid-action' id='isAdmin' rel='isAdmin' name="isAdmin" type='checkbox' value='1' @if($detail['u_is_admin'] == 1) checked="checked" @endif />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Status</label>
                            <div class="col-sm-4">
                                <input class='js-switch-grid-action' id='status' rel='status' name="status" type='checkbox' value='1'  @if($detail['u_status'] == 1) checked="checked" @endif />
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

<!-- Jquery Validate -->
<script src="{!! asset('asset/js/plugins/validate/jquery.validate.min.js') !!}"></script>


<!-- Chosen -->
<script src="{!! asset('asset/js/plugins/chosen/chosen.jquery.js') !!}"></script>

<!-- iCheck -->
<script src="{!! asset('asset/js/plugins/iCheck/icheck.min.js') !!}"></script>

<!-- Switchery -->
<script src="{!! asset('asset/plugins/switchery/switchery.js') !!}"></script>
<script>
        var startdt = new Date();
</script>


@endsection

@section("customPageJs")

<script>
    function changeStationType(id) {
        var action = ($('#' + id).prop('checked') == true) ? '1' : '2';
        if (action == '1') {
            $('.campusType').show();
        } else {
            $('.campusType').hide();
        }
    }

    $(document).ready(function () {
        $.validator.setDefaults({ignore: ":hidden:not(.chosen-select)"})
        $("form").validate({
            rules: {
                /*			campusId: {
                 required: function (){ return $('#isStudent').prop('checked'); },
                 },
                 */		firstName: {
                    required: true,
                }
            },
            messages: {
                /*      campusId: {
                 required: 'Please, Select Campus',
                 },
                 */	name: {
                    required: 'Enter First Name',
                }
            } /*,
             errorPlacement: function(error, element) {
             //Custom position
             if (element.attr("name") == "campusId" ) {
             $("#campusId_chosen").after(error);
             }
             // Default position: if no match is met (other fields)
             else {
             element.after(error);
             }
             } */

        });

        $('form').keypress(function (event) {

            if (event.keyCode == 13) {
                event.preventDefault();
            }
        });
        $('.i-checks').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green',
        });

        $('.js-switch-grid-action').each(function () {
            var switchery = new Switchery(this, {size: 'small', color: '#1AB394', secondaryColor: '#ED5565'});
            this.onchange = function () {
                //if()
                if (this.id == 'isStudent') {
                    changeStationType(this.id)
                }
                //changeSwitch(this.id);
            };
        });
        //	changeStationType('isStudent');


        var config = {
            '.chosen-select': {width: "100%"},
            '.chosen-select-deselect': {allow_single_deselect: true},
            '.chosen-select-no-single': {disable_search_threshold: 10},
            '.chosen-select-no-results': {no_results_text: 'Oops, nothing found!'},
            '.chosen-select-width': {width: "95%"}
        }
        for (var selector in config) {
            $(selector).chosen(config[selector]);
        }

    });




</script>
@endsection