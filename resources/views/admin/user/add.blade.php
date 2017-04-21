@extends('layouts.common')

@section('pageTitle','Add Back office user')

@section('newCssLoad')



<link href="{!! asset('asset/css/bootstrap.min.css') !!}" rel="stylesheet">
<link href="{!! asset('asset/font-awesome/css/font-awesome.css') !!}" rel="stylesheet">
<link href="{!! asset('asset/css/plugins/iCheck/custom.css') !!}" rel="stylesheet">
<link href="{!! asset('asset/css/animate.css') !!}" rel="stylesheet">
<link href="{!! asset('asset/css/style.css') !!}" rel="stylesheet">

<link href="{!! asset('asset/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css') !!}" rel="stylesheet">

<link href="{!! asset('asset/plugins/switchery/switchery.css') !!}" rel="stylesheet" type="text/css" />

<link href="{!! asset('asset/plugins/bootstrap-duallistbox-master/bootstrap-duallistbox.min.css') !!}"  rel="stylesheet" type="text/css" >
@endsection

@section('mainContainerArea')

<div class="wrapper wrapper-content animated fadeInRight">

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Add New User</h5>
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

                    <form method="post" class="form-horizontal" action="{{route('admin::doUserAdd')}}">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Username</label>
                            <div class="col-sm-6">
                                <input type="text" placeholder="Username" class="form-control required" name="username" id="username">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Password</label>
                            <div class="col-sm-6">
                                <input type="password" placeholder="Password" class="form-control required" name="password" id="password">
                            </div>
                        </div>

                        <div class="form-group appUserType">
                            <label class="col-sm-2 control-label">Country code</label>
                            <div class="col-sm-6">
                                <input type="text" placeholder="Country code" class="form-control digits" name="countryCode" id="countryCode">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Phone Number</label>
                            <div class="col-sm-6">
                                <input type="text" placeholder="Phone Number" class="form-control digits" name="phoneNumber" id="phoneNumber">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Followers</label>
                            <div class="col-sm-10">
                                <select multiple="multiple" size="10"  class="form-control dualList"  name="followers[]" >
                                    @foreach ($userList as $value)
                                    <option value="{{ $value->u_id }}">{{ $value->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group appUserType">
                            <label class="col-sm-2 control-label">Is Admin</label>
                            <div class="col-sm-4">
                                <input class='js-switch-grid-action' id='isAdmin' rel='isAdmin' name="isAdmin" type='checkbox' checked="" value='1' />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Status</label>
                            <div class="col-sm-4">
                                <input class='js-switch-grid-action' id='status' rel='status' name="status" type='checkbox' checked="checked" value='1' />
                            </div>
                        </div>

                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <div class="col-sm-4 col-sm-offset-2">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <!--    <button class="btn btn-white" type="submit">Cancel</button> -->
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

<!-- Custom and plugin javascript -->
<script src="{!! asset('asset/js/inspinia.js') !!}"></script>
<script src="{!! asset('asset/js/plugins/pace/pace.min.js') !!}"></script>

<!-- Jquery Validate -->
<script src="{!! asset('asset/js/plugins/validate/jquery.validate.min.js') !!}"></script>


<!-- iCheck -->
<script src="{!! asset('asset/js/plugins/iCheck/icheck.min.js') !!}"></script>

<!-- Switchery -->
<script src="{!! asset('asset/plugins/switchery/switchery.js') !!}"></script>

<!-- duallistbox -->
<script src="{!! asset('asset/plugins/bootstrap-duallistbox-master/jquery.bootstrap-duallistbox.min.js') !!}"></script>
@endsection

@section("customPageJs")
<script>
$(document).ready(function () {
    $.validator.setDefaults({ignore: ":hidden:not(.chosen-select)"})
    $("form").validate({
        errorPlacement: function (error, element) {
            //Custom position
            element.after(error);
        }

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
            
        };
    });
    
    var demo2 = $('.dualList').bootstrapDualListbox({
                    nonSelectedListLabel: 'Non-selected users',
                    selectedListLabel: 'Selected user',
                    preserveSelectionOnMove: 'moved',
                    moveOnSelect: true,
                    nonSelectedFilter: '',
                    selectorMinimalHeight : 250
              });
});
</script>
@endsection