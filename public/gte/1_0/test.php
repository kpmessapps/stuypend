<?php 
$x_device_id = '';
$x_api_key = 'gte20170402';

if($_SERVER['HTTP_HOST'] == "gte.mess"){
  $domain = "http://gte.mess";
}else{
  $domain = "http://gte.mess.io";  
}

// pratham dulara pratham vandito gajananana gan naaya vighna vinashak gunijanak balak
$apiArr = array(
        'signup_POST'              => '/webservices/signup',
        'login_POST'               => '/webservices/auth/login',
        'logout_POST'              => '/webservices/auth/logout',
        'forgotPassword_GET'       => '/webservices/auth/resetpwd',
        'resetPassword_POST'       => '/webservices/auth/resetpwd',
        'getUserProfile_GET'       => '/webservices/profile',
        'updateUserProfile_POST'   => '/webservices/profile',
        'changePassword_POST'      => '/webservices/change/pwd',
        'appConfigData_GET'        => '/webservices/app/config/data',
        'registerPhoneNumber_POST' => '/webservices/user/phone/register',
        'verifyPhoneToken_POST'    => '/webservices/user/phone/verify',
        'createJob_POST'           => '/webservices/job/create',
        'getExecutiveClientList_GET'        => '/webservices/executive/client/list',
        'getJobList_GET'           => '/webservices/job/list',
        'sendJobRequest_POST'      => '/webservices/job/{jobId}/request',
        'moderateJobRequest_POST'  => '/webservices/{moderateBy}/moderate/contractor/{contractorId}/job/{jobId}',
        'getJobContractorList_GET'           => '/webservices/job/{jobId}/contractor/{statusType}/list',
        'deleteAccount_POST'  => '/webservices/account/contractor/delete',
//        'getFriendGameCodeDetail_GET'  => '/webservices/game/code/verify',
//        'generateFriendGameCode_POST'  => '/webservices/game/code/generate',
//        'getBusinessAdsDetail_GET' => '/webservices/business/ads/detail',
//        'getBusinessList_GET'      => '/webservices/business/list', 
//        'favoriteUnfavoriteBusiness_POST'  => '/webservices/business/{businessId}/favorite/action', 
//        'getNewGame_GET'           => '/webservices/game', 
//        'submitGame_POST'           => '/webservices/game', 
        
    );
ksort($apiArr);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"    "http://www.w3.org/TR/html4/strict.dtd"    >
<html lang="en">
  <head>
  <title>GTE App > 1.0 > API Manager</title>
  <script src="../js/jquery-ui/jquery-1.7.2.min.js" type="text/javascript"></script>
  <script src="../js/jquery-ui/jquery-ui-1.8.21.custom.min.js" type="text/javascript"></script>
  <script src="../js/validationEngine/jquery.validationEngine.js" type="text/javascript"></script>
  <script src="../js/validationEngine/languages/jquery.validationEngine-en.js" type="text/javascript" charset="utf-8"></script>
  <link rel="stylesheet" href="../css/validationEngine/validationEngine.jquery.css" type="text/css"/>
  <link rel="stylesheet" href="../css/jquery-ui/jquery-ui-1.8.21.custom.css" type="text/css"/>
  <script type="text/javascript">
        $('document').ready(function(){
	    var id = $('#apiChange').val();
            $('.all').hide();
            $('#'+id).show();
            $('#apiChange').change(function(){
                var id = $(this).val();
                $('.all').hide();
                $('#'+id).show();
            });
         });
	
        (function($) {
$.fn.serializefiles = function() {
    var obj = $(this);
    /* ADD FILE TO PARAM AJAX */
    var formData = new FormData();
    $.each($(obj).find("input[type='file']"), function(i, tag) {
        $.each($(tag)[0].files, function(i, file) {
            formData.append(tag.name, file);
        });
    });
    var params = $(obj).serializeArray();
    $.each(params, function (i, val) {
        formData.append(val.name, val.value);
    });
    return formData;
};
})(jQuery);

	function submitForm(apiName)
	{
            $('#results').html('');
            var action = $('#'+apiName+' form').attr('action');
            var jobId = $('#'+apiName).find('input[name=jobId]').val();
            if (jobId != null && jobId != '') {
                  action = action.replace("{jobId}",jobId);
            }
            
            var statusType = $('#'+apiName).find('select[name=statusType]').val();
            if (statusType != null && statusType != '') {
                  action = action.replace("{statusType}",statusType);
            }
            
            var jsonRequestData = $('#'+apiName+' form').serialize();
            
            $.ajax({
                type: $('#'+apiName+' form').attr('method'),
                beforeSend: function(request) {
                    $("#"+apiName+" .hi").each(function(){
                        request.setRequestHeader($(this).attr('name'), $(this).val());
                    });
                    $(".hi").attr("disabled", "disabled");
                },
                url: action,
         //       data: $('#'+apiName+' form').serializefiles() , //jsonRequestData,
                data: jsonRequestData,
                processData: false,
                success: function(serverResponse, textStatus, xhr) {
                    if(apiName == "login_POST" || apiName == "signup_POST" || apiName == "signupWithFacebook_POST" || apiName == "loginWithFacebook_POST" ){
                        $('input[name=X-Session-ID]').val(serverResponse.session);
                    }else if(apiName == "logout_POST"){
                        $('input[name=X-Session-ID]').val('');
                    }
                    var res = "<span style='color:green;'>Response Status Code : "+xhr.status+"</span>\n\n\n<div style='text-align:left;'>"+JSON.stringify(serverResponse,null,4)+"</div>";
                        $('#results').html(res);
                },
                error: function (serverResponse, status, error) {
                        var res = "<span style='color:red;'>Response Status Code : "+serverResponse.status+"</span>\n\n\n<div style='text-align:left;'>"+JSON.stringify(JSON.parse(serverResponse.responseText),null,4)+"</div>";
                        $('#results').html(res);
                },
                complete: function(){
                    $(".hi").removeAttr('disabled');
                }
            }); return false;
	 
	}
    </script>
  <style type="text/css">
td {
	vertical-align: super;
	min-width: 110px;
	padding-bottom: 10px;
}
td input[type="text"] {
	background: none repeat scroll 0 0 #FFFFFF;
	border-color: #CCCCCC #999999 #999999 #CCCCCC;
	border-radius: 3px 3px 3px 3px;
	border-style: solid;
	border-width: 1px;
	box-shadow: 0 1px 0 #E8E8E8;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 10pt;
	min-width: 300px;
	padding: 3px;
}
.submit-button {
	border-top: 1px solid #898bfa;
	background: #1d2042;
	background: -webkit-gradient(linear, left top, left bottom, from(#3e969c), to(#1d2042));
	background: -webkit-linear-gradient(top, #3e969c, #1d2042);
	background: -moz-linear-gradient(top, #3e969c, #1d2042);
	background: -ms-linear-gradient(top, #3e969c, #1d2042);
	background: -o-linear-gradient(top, #3e969c, #1d2042);
	padding: 8px 16px;
	-webkit-border-radius: 6px;
	-moz-border-radius: 6px;
	border-radius: 6px;
	-webkit-box-shadow: rgba(0, 0, 0, 1) 0 1px 0;
	-moz-box-shadow: rgba(0, 0, 0, 1) 0 1px 0;
	box-shadow: rgba(0, 0, 0, 1) 0 1px 0;
	text-shadow: rgba(0, 0, 0, .4) 0 1px 0;
	color: #f2cc6d;
	font-size: 16px;
	font-family: Helvetica, Arial, Sans-Serif;
	text-decoration: none;
	vertical-align: middle;
}
.submit-button:hover {
	background: -webkit-gradient(linear, left top, left bottom, from(#FFFFFF), to(#C1C1C1));
	background: -webkit-linear-gradient(top, #FFFFFF, #C1C1C1);
	background: -moz-linear-gradient(top, #FFFFFF, #C1C1C1);
	background: -ms-linear-gradient(top, #FFFFFF, #C1C1C1);
	background: -o-linear-gradient(top, #FFFFFF, #C1C1C1);
	border-radius: 6px 6px 6px 6px;
	border-top: 1px solid #9B9B9B;
	box-shadow: 0 1px 0 #CCCCCC;
	color: #444444;
	font-family: Helvetica, Arial, Sans-Serif;
	font-size: 16px;
	padding: 8px 16px;
	text-decoration: none;
	text-shadow: none;
	vertical-align: middle;
}
.submit-button:active {
	border-top-color: #005085;
}
</style>
  </head>
  <body>
<!-- Insert your content here -->
<table style="width:100%">
    <tr>
    <td align="center" style="" colspan="2">
	  <a href="http://messapps.com" target="_blank">
		<h1 style="font-size: 49px; font-family: sans-serif; text-decoration: underline; color: purple;">GTE APP TEST PAGE</h1>
	  </a>
	</td>
  </tr>
    <tr>
	  
    <td style="padding: 20px;text-align: center;"> All webservices urls :
        <select name="apiChange" id="apiChange">
		  <?php
		  foreach($apiArr as $key => $value){
			echo '<option value= "'.$key.'">'.$key.' : '.$value.'</option>';
		  }
		  ?>
		</select>
	</td>
  </tr>
    <tr>
    <td align="center" style="padding: 30px 0 0 0;">
        

        
<?php $apiName = "login_POST"; ?>
<div class="all" id="<?php echo $apiName; ?>" style="display: none;">
    <form name="apiform" id="<?php echo $apiName; ?>Form" enctype="multipart/form-data" method="POST" action="<?= $domain.$apiArr[$apiName]; ?>">
        <table>
            <tr>
                <td>X-Device-ID: (Header)</td>
                <td><input type="text" name="X-Device-ID" class="hi" value="<?= $x_device_id; ?>"></td>
            </tr>
            <tr>
                <td>X-API-Key: (Header)</td>
                <td><input type="text" name="X-API-Key" class="hi" value="<?= $x_api_key; ?>"></td>
            </tr>
            <tr>
                <td>email:</td>
                <td><input type="text" name="email" value=""></td>
            </tr>
            <tr>
                <td>password:</td>
                <td><input type="text" name="password" value=""></td>
            </tr>
            <tr>
                <td>pushToken:</td>
                <td><input type="text" name="pushToken" value=""></td>
            </tr>
            <tr>
                <td>deviceType:</td>
                <td>
                    <select name="deviceType" >
                        <option value="ios"> ios : IOS </option>
                    </select>
                </td>
            </tr>
            <tr>
                <td></td>
                <td></td>
            </tr>
        </table>
        <br/>
        <br/>
        <br/>
        <input type="hidden" name="apiName" value="<?php echo $apiName; ?>">
        <a href="javascript:void(0);" class="button submit-button" rel="<?php echo $apiName; ?>" onClick="submitForm(this.rel);"><?php echo $apiName; ?></a>
    </form>
</div> 

<?php $apiName = "forgotPassword_GET"; ?>
<div class="all" id="<?php echo $apiName; ?>" style="display: none;">
    <form name="apiform" id="<?php echo $apiName; ?>Form" enctype="multipart/form-data" method="GET" action="<?= $domain.$apiArr[$apiName]; ?>">
        <table>
            <tr>
                <td>X-Device-ID: (Header)</td>
                <td><input type="text" name="X-Device-ID" class="hi" value="<?= $x_device_id; ?>"></td>
            </tr>
            <tr>
                <td>X-API-Key: (Header)</td>
                <td><input type="text" name="X-API-Key" class="hi" value="<?= $x_api_key; ?>"></td>
            </tr>
            <tr>
                <td>email:</td>
                <td><input type="text" name="email" value=""></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
            </tr>
        </table>
        <br/>
        <br/>
        <br/>
        <input type="hidden" name="apiName" value="<?php echo $apiName; ?>">
        <a href="javascript:void(0);" class="button submit-button" rel="<?php echo $apiName; ?>" onClick="submitForm(this.rel);"><?php echo $apiName; ?></a>
    </form>
</div> 
        
<?php $apiName = "resetPassword_POST"; ?>
<div class="all" id="<?php echo $apiName; ?>" style="display: none;">
    <form name="apiform" id="<?php echo $apiName; ?>Form" enctype="multipart/form-data" method="POST" action="<?= $domain.$apiArr[$apiName]; ?>">
        <table>
            <tr>
                <td>X-Device-ID: (Header)</td>
                <td><input type="text" name="X-Device-ID" class="hi" value="<?= $x_device_id; ?>"></td>
            </tr>
            <tr>
                <td>X-API-Key: (Header)</td>
                <td><input type="text" name="X-API-Key" class="hi" value="<?= $x_api_key; ?>"></td>
            </tr>
            <tr>
                <td>email:</td>
                <td><input type="text" name="email" value=""></td>
            </tr>
            <tr>
                <td>token:</td>
                <td><input type="text" name="token" value=""></td>
            </tr>
            <tr>
                <td>newPassword:</td>
                <td><input type="text" name="newPassword" value=""></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
            </tr>
        </table>
        <br/>
        <br/>
        <br/>
        <input type="hidden" name="apiName" value="<?php echo $apiName; ?>">
        <a href="javascript:void(0);" class="button submit-button" rel="<?php echo $apiName; ?>" onClick="submitForm(this.rel);"><?php echo $apiName; ?></a>
    </form>
</div> 
        
<?php $apiName = "signup_POST"; ?> 
<div class="all" id="<?php echo $apiName; ?>" style="display: none;">
    <form name="apiform" id="<?php echo $apiName; ?>Form" enctype="multipart/form-data" method="POST" action="<?= $domain.$apiArr[$apiName]; ?>">
        <table>
            <tr>
                <td>X-Device-ID:* (Header)</td>
                <td><input type="text" name="X-Device-ID" class="hi" value="<?= $x_device_id; ?>"></td>
            </tr>
            <tr>
                <td>X-API-Key:* (Header)</td>
                <td><input type="text" name="X-API-Key" class="hi" value="<?= $x_api_key; ?>"></td>
            </tr>
            <tr>
                <td>name:* </td>
                <td><input type="text" name="name" value=""></td>
            </tr>
            <tr>
                <td>email:*</td>
                <td><input type="text" name="email" value=""></td>
            </tr>
            <tr>
                <td>password:*</td>
                <td><input type="text" name="password" value=""></td>
            </tr>
            <tr>
                <td>profileImage: (multipart/form-data)</td>
                <td><input type="file" name="profileImage" value=""><br/>image|mimes:jpeg,png,jpg</td>
            </tr>
            <tr>
                <td>pushToken:</td>
                <td><input type="text" name="pushToken" value=""></td>
            </tr>
            <tr>
                <td>deviceType:</td>
                <td>
                    <select name="deviceType" >
                        <option value="ios"> ios : IOS </option>
                    </select>
                </td>
            </tr>
            <tr>
                <td></td>
                <td></td>
            </tr>
        </table>
        <br/>
        <br/>
        <br/>
        <input type="hidden" name="apiName" value="<?php echo $apiName; ?>">
        <a href="javascript:void(0);" class="button submit-button" rel="<?php echo $apiName; ?>" onClick="submitForm(this.rel);"><?php echo $apiName; ?></a>
    </form>
</div> 
      		
<?php $apiName = "logout_POST"; ?>
<div class="all" id="<?php echo $apiName; ?>" style="display: none;">
    <form name="apiform" id="<?php echo $apiName; ?>Form" enctype="multipart/form-data" method="POST" action="<?= $domain.$apiArr[$apiName]; ?>">
        <table>
            <tr>
                <td>X-Device-ID: (Header)</td>
                <td><input type="text" name="X-Device-ID" class="hi" value="<?= $x_device_id; ?>"></td>
            </tr>
            <tr>
                <td>X-API-Key: (Header)</td>
                <td><input type="text" name="X-API-Key" class="hi" value="<?= $x_api_key; ?>"></td>
            </tr>
            <tr>
                <td>X-Session-ID:</td>
                <td><input type="text" name="X-Session-ID" class="hi" value=""></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
            </tr>
        </table>
        <br/>
        <br/>
        <br/>
        <input type="hidden" name="apiName" value="<?php echo $apiName; ?>">
        <a href="javascript:void(0);" class="button submit-button" rel="<?php echo $apiName; ?>" onClick="submitForm(this.rel);"><?php echo $apiName; ?></a>
    </form>
</div> 		

<?php $apiName = "getUserProfile_GET"; ?>
<div class="all" id="<?php echo $apiName; ?>" style="display: none;">
    <form name="apiform" id="<?php echo $apiName; ?>Form" enctype="multipart/form-data" method="GET" action="<?= $domain.$apiArr[$apiName]; ?>">
        <table>
            <tr>
                <td>X-Device-ID: (Header)</td>
                <td><input type="text" name="X-Device-ID" class="hi" value="<?= $x_device_id; ?>"></td>
            </tr>
            <tr>
                <td>X-API-Key: (Header)</td>
                <td><input type="text" name="X-API-Key" class="hi" value="<?= $x_api_key; ?>"></td>
            </tr>
            <tr>
                <td>X-Session-ID: (Header)</td>
                <td><input type="text" name="X-Session-ID" class="hi" value=""></td>
            </tr>
            <tr>
                <td>otherUserId</td>
                <td><input type="text" name="otherUserId" value=""></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
            </tr>
        </table>
        <br/>
        <br/>
        <br/>
        <input type="hidden" name="apiName" value="<?php echo $apiName; ?>">
        <a href="javascript:void(0);" class="button submit-button" rel="<?php echo $apiName; ?>" onClick="submitForm(this.rel);"><?php echo $apiName; ?></a>
    </form>
</div> 		
        
        
<?php $apiName = "updateUserProfile_POST"; ?>
<div class="all" id="<?php echo $apiName; ?>" style="display: none;">
    <form name="apiform" id="<?php echo $apiName; ?>Form" enctype="multipart/form-data" method="POST" action="<?= $domain.$apiArr[$apiName]; ?>">
        <table>
            <tr>
                <td>X-Device-ID: (Header)</td>
                <td><input type="text" name="X-Device-ID" class="hi" value="<?= $x_device_id; ?>"></td>
            </tr>
            <tr>
                <td>X-API-Key: (Header)</td>
                <td><input type="text" name="X-API-Key" class="hi" value="<?= $x_api_key; ?>"></td>
            </tr>
            <tr>
                <td>X-Session-ID: (Header)</td>
                <td><input type="text" name="X-Session-ID" class="hi" value=""></td>
            </tr>
<!--            <tr>
                <td>email</td>
                <td><input type="text" name="email" value=""></td>
            </tr>-->
            <tr>
                <td>profileImage</td>
                <td><input type="file" name="profileImage" value=""></td>
            </tr>
            <tr>
                <td>name</td>
                <td><input type="text" name="name" value=""></td>
            </tr>
            <tr>
                <td>description</td>
                <td><input type="text" name="description" value=""></td>
            </tr>
           
            <tr>
                <td>isNotificationOn:</td>
                <td>
                    <select name="isNotificationOn" >
                        <option value="1"> 1 : On </option>
                        <option value="2"> 0 : Off </option>
                    </select>
                </td>
            </tr>
           <tr>
                <td></td>
                <td></td>
            </tr>
        </table>
        <br/>
        <br/>
        <br/>
        <input type="hidden" name="apiName" value="<?php echo $apiName; ?>">
        <a href="javascript:void(0);" class="button submit-button" rel="<?php echo $apiName; ?>" onClick="submitForm(this.rel);"><?php echo $apiName; ?></a>
    </form>
</div> 		
        
<?php $apiName = "createJob_POST"; ?>
<div class="all" id="<?php echo $apiName; ?>" style="display: none;">
    <form name="apiform" id="<?php echo $apiName; ?>Form" enctype="multipart/form-data" method="POST" action="<?= $domain.$apiArr[$apiName]; ?>">
        <table>
            <tr>
                <td>X-Device-ID: (Header)</td>
                <td><input type="text" name="X-Device-ID" class="hi" value="<?= $x_device_id; ?>"></td>
            </tr>
            <tr>
                <td>X-API-Key: (Header)</td>
                <td><input type="text" name="X-API-Key" class="hi" value="<?= $x_api_key; ?>"></td>
            </tr>
            <tr>
                <td>X-Session-ID: (Header)</td>
                <td><input type="text" name="X-Session-ID" class="hi" value=""></td>
            </tr>
            
            <tr>
                <td>clientUserId</td>
                <td><input type="text" name="clientUserId" value=""></td>
            </tr>
            <tr>
                <td>jobName</td>
                <td><input type="text" name="jobName" value=""></td>
            </tr>
            <tr>
                <td>jobScopeOfWork</td>
                <td><input type="text" name="jobScopeOfWork" value=""></td>
            </tr>
            <tr>
                <td>jobAddress</td>
                <td><input type="text" name="jobAddress" value=""></td>
            </tr>
            <tr>
                <td>jobRate</td>
                <td><input type="text" name="jobRate" value=""></td>
            </tr>
            <tr>
                <td>jobLatitude</td>
                <td><input type="text" name="jobLatitude" value=""></td>
            </tr>
            <tr>
                <td>jobLongitude</td>
                <td><input type="text" name="jobLongitude" value=""></td>
            </tr>
            <tr>
                <td>jobDescription</td>
                <td><input type="text" name="jobDescription" value=""></td>
            </tr>
            <tr>
                <td>jobSkillIdList</td>
                <td><input type="text" name="jobSkillIdList" value=""><br>Comma separate skill id list</td>
            </tr>
            <tr>
                <td>jobHoursOfweek</td>
                <td><input type="text" name="jobHoursOfweek" value=""></td>
            </tr>
            <tr>
                <td>jobPersonsNeeded</td>
                <td><input type="text" name="jobPersonsNeeded" value=""></td>
            </tr>
            <tr>
                <td>jobDescription</td>
                <td><input type="text" name="jobDescription" value=""></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
            </tr>
        </table>
        <br/>
        <br/>
        <br/>
        <input type="hidden" name="apiName" value="<?php echo $apiName; ?>">
        <a href="javascript:void(0);" class="button submit-button" rel="<?php echo $apiName; ?>" onClick="submitForm(this.rel);"><?php echo $apiName; ?></a>
    </form>
</div> 		
        
        
<?php $apiName = "sendJobRequest_POST"; ?>
<div class="all" id="<?php echo $apiName; ?>" style="display: none;">
    <form name="apiform" id="<?php echo $apiName; ?>Form" enctype="multipart/form-data" method="POST" action="<?= $domain.$apiArr[$apiName]; ?>">
        <table>
            <tr>
                <td>X-Device-ID: (Header)</td>
                <td><input type="text" name="X-Device-ID" class="hi" value="<?= $x_device_id; ?>"></td>
            </tr>
            <tr>
                <td>X-API-Key: (Header)</td>
                <td><input type="text" name="X-API-Key" class="hi" value="<?= $x_api_key; ?>"></td>
            </tr>
            <tr>
                <td>X-Session-ID: (Header)</td>
                <td><input type="text" name="X-Session-ID" class="hi" value=""></td>
            </tr>
            <tr>
                <td>jobId: (URI)</td>
                <td><input type="text" name="jobId" value=""></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
            </tr>
        </table>
        <br/>
        <br/>
        <br/>
        <input type="hidden" name="apiName" value="<?php echo $apiName; ?>">
        <a href="javascript:void(0);" class="button submit-button" rel="<?php echo $apiName; ?>" onClick="submitForm(this.rel);"><?php echo $apiName; ?></a>
    </form>
</div> 		
        
<?php $apiName = "moderateJobRequest_POST"; ?>
<div class="all" id="<?php echo $apiName; ?>" style="display: none;">
    <form name="apiform" id="<?php echo $apiName; ?>Form" enctype="multipart/form-data" method="POST" action="<?= $domain.$apiArr[$apiName]; ?>">
        <table>
            <tr>
                <td>X-Device-ID: (Header)</td>
                <td><input type="text" name="X-Device-ID" class="hi" value="<?= $x_device_id; ?>"></td>
            </tr>
            <tr>
                <td>X-API-Key: (Header)</td>
                <td><input type="text" name="X-API-Key" class="hi" value="<?= $x_api_key; ?>"></td>
            </tr>
            <tr>
                <td>X-Session-ID: (Header)</td>
                <td><input type="text" name="X-Session-ID" class="hi" value=""></td>
            </tr>
            <tr>
                <td>moderateBy: (URI)</td>
                <td>
                    <select name="moderateBy" >
                        <option value="client"> client : Client </option>
                        <option value="executive"> executive : Executive </option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>contractorId: (URI)</td>
                <td><input type="text" name="contractorId" value=""></td>
            </tr>
            <tr>
                <td>jobId: (URI)</td>
                <td><input type="text" name="jobId" value=""></td>
            </tr>
            <tr>
                <td>status:</td>
                <td>
                    <select name="status" >
                        <option value="1"> 1 : Approve </option>
                        <option value="2"> 2 : Decline </option>
                    </select>
                </td>
            </tr>
            <tr>
                <td></td>
                <td></td>
            </tr>
        </table>
        <br/>
        <br/>
        <br/>
        <input type="hidden" name="apiName" value="<?php echo $apiName; ?>">
        <a href="javascript:void(0);" class="button submit-button" rel="<?php echo $apiName; ?>" onClick="submitForm(this.rel);"><?php echo $apiName; ?></a>
    </form>
</div> 		
        
<?php $apiName = "deleteAccount_POST"; ?>
<div class="all" id="<?php echo $apiName; ?>" style="display: none;">
    <form name="apiform" id="<?php echo $apiName; ?>Form" enctype="multipart/form-data" method="POST" action="<?= $domain.$apiArr[$apiName]; ?>">
        <table>
            <tr>
                <td>X-Device-ID: (Header)</td>
                <td><input type="text" name="X-Device-ID" class="hi" value="<?= $x_device_id; ?>"></td>
            </tr>
            <tr>
                <td>X-API-Key: (Header)</td>
                <td><input type="text" name="X-API-Key" class="hi" value="<?= $x_api_key; ?>"></td>
            </tr>
            <tr>
                <td>X-Session-ID: (Header)</td>
                <td><input type="text" name="X-Session-ID" class="hi" value=""></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
            </tr>
        </table>
        <br/>
        <br/>
        <br/>
        <input type="hidden" name="apiName" value="<?php echo $apiName; ?>">
        <a href="javascript:void(0);" class="button submit-button" rel="<?php echo $apiName; ?>" onClick="submitForm(this.rel);"><?php echo $apiName; ?></a>
    </form>
</div> 		
        
<?php $apiName = "changePassword_POST"; ?>
<div class="all" id="<?php echo $apiName; ?>" style="display: none;">
    <form name="apiform" id="<?php echo $apiName; ?>Form" enctype="multipart/form-data" method="POST" action="<?= $domain.$apiArr[$apiName]; ?>">
        <table>
            <tr>
                <td>X-Device-ID: (Header)</td>
                <td><input type="text" name="X-Device-ID" class="hi" value="<?= $x_device_id; ?>"></td>
            </tr>
            <tr>
                <td>X-API-Key: (Header)</td>
                <td><input type="text" name="X-API-Key" class="hi" value="<?= $x_api_key; ?>"></td>
            </tr>
            <tr>
                <td>X-Session-ID: (Header)</td>
                <td><input type="text" name="X-Session-ID" class="hi" value=""></td>
            </tr>
            <tr>
                <td>oldPassword</td>
                <td><input type="text" name="oldPassword" value=""></td>
            </tr>
            <tr>
                <td>newPassword</td>
                <td><input type="text" name="newPassword" value=""></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
            </tr>
        </table>
        <br/>
        <br/>
        <br/>
        <input type="hidden" name="apiName" value="<?php echo $apiName; ?>">
        <a href="javascript:void(0);" class="button submit-button" rel="<?php echo $apiName; ?>" onClick="submitForm(this.rel);"><?php echo $apiName; ?></a>
    </form>
</div> 	
        
<?php $apiName = "appConfigData_GET"; ?>
<div class="all" id="<?php echo $apiName; ?>" style="display: none;">
    <form name="apiform" id="<?php echo $apiName; ?>Form" enctype="multipart/form-data" method="GET" action="<?= $domain.$apiArr[$apiName]; ?>">
        <table>
            <tr>
                <td></td>
                <td>Note : This api will list the app all config data here.</td>
            </tr>
            <tr>
                <td>X-Device-ID: (Header)</td>
                <td><input type="text" name="X-Device-ID" class="hi" value="<?= $x_device_id; ?>"></td>
            </tr>
            <tr>
                <td>X-API-Key: (Header)</td>
                <td><input type="text" name="X-API-Key" class="hi" value="<?= $x_api_key; ?>"></td>
            </tr>
            <tr>
                <td>X-Session-ID: (Header)</td>
                <td><input type="text" name="X-Session-ID" class="hi" value=""></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
            </tr>
        </table>
        <br/>
        <br/>
        <br/>
        <input type="hidden" name="apiName" value="<?php echo $apiName; ?>">
        <a href="javascript:void(0);" class="button submit-button" rel="<?php echo $apiName; ?>" onClick="submitForm(this.rel);"><?php echo $apiName; ?></a>
    </form>
</div> 	
        
<?php $apiName = "verifyPhoneToken_POST"; ?>
<div class="all" id="<?php echo $apiName; ?>" style="display: none;">
    <form name="apiform" id="<?php echo $apiName; ?>Form" enctype="multipart/form-data" method="POST" action="<?= $domain.$apiArr[$apiName]; ?>">
        <table>
            <tr>
                <td></td>
                <td>Note : This api will list the app all config data here.</td>
            </tr>
            <tr>
                <td>X-Device-ID: (Header)</td>
                <td><input type="text" name="X-Device-ID" class="hi" value="<?= $x_device_id; ?>"></td>
            </tr>
            <tr>
                <td>X-API-Key: (Header)</td>
                <td><input type="text" name="X-API-Key" class="hi" value="<?= $x_api_key; ?>"></td>
            </tr>
            <tr>
                <td>X-Session-ID: (Header)</td>
                <td><input type="text" name="X-Session-ID" class="hi" value=""></td>
            </tr>
            <tr>
                <td>phoneToken:</td>
                <td><input type="text" name="phoneToken" value=""></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
            </tr>
        </table>
        <br/>
        <br/>
        <br/>
       <input type="hidden" name="apiName" value="<?php echo $apiName; ?>">
        <a href="javascript:void(0);" class="button submit-button" rel="<?php echo $apiName; ?>" onClick="submitForm(this.rel);"><?php echo $apiName; ?></a>
     </form>
</div>         
        
        

<?php $apiName = "registerPhoneNumber_POST"; ?>
<div class="all" id="<?php echo $apiName; ?>" style="display: none;">
    <form name="apiform" id="<?php echo $apiName; ?>Form" enctype="multipart/form-data" method="POST" action="<?= $domain.$apiArr[$apiName]; ?>">
        <table>
            <tr>
                <td></td>
                <td>Note : This api will list the app all config data here.</td>
            </tr>
            <tr>
                <td>X-Device-ID: (Header)</td>
                <td><input type="text" name="X-Device-ID" class="hi" value="<?= $x_device_id; ?>"></td>
            </tr>
            <tr>
                <td>X-API-Key: (Header)</td>
                <td><input type="text" name="X-API-Key" class="hi" value="<?= $x_api_key; ?>"></td>
            </tr>
            <tr>
                <td>X-Session-ID: (Header)</td>
                <td><input type="text" name="X-Session-ID" class="hi" value=""></td>
            </tr>
            <tr>
                <td>phone:</td>
                <td><input type="text" name="phone" value=""></td>
            </tr>
            <tr>
                <td>countryCode:</td>
                <td><input type="text" name="countryCode" value=""></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
            </tr>
        </table>
        <br/>
        <br/>
        <br/>
        <input type="hidden" name="apiName" value="<?php echo $apiName; ?>">
        <a href="javascript:void(0);" class="button submit-button" rel="<?php echo $apiName; ?>" onClick="submitForm(this.rel);"><?php echo $apiName; ?></a>
    </form>
</div>    
        
     
<?php $apiName = "getExecutiveClientList_GET"; ?>
<div class="all" id="<?php echo $apiName; ?>" style="display: none;">
    <form name="apiform" id="<?php echo $apiName; ?>Form" enctype="multipart/form-data" method="GET" action="<?= $domain.$apiArr[$apiName]; ?>">
        <table>
            <tr>
                <td></td>
                <td>Note : This api will use for get executive assigned client list.</td>
            </tr>
            <tr>
                <td>X-Device-ID: (Header)</td>
                <td><input type="text" name="X-Device-ID" class="hi" value="<?= $x_device_id; ?>"></td>
            </tr>
            <tr>
                <td>X-API-Key: (Header)</td>
                <td><input type="text" name="X-API-Key" class="hi" value="<?= $x_api_key; ?>"></td>
            </tr>
            <tr>
                <td>X-Session-ID: (Header)</td>
                <td><input type="text" name="X-Session-ID" class="hi" value=""></td>
            </tr>
            <tr>
                <td>search: </td>
                <td><input type="text" name="search" value=""></td>
            </tr>
            <tr>
                <td>pageNo: </td>
                <td><input type="text" name="pageNo" value=""></td>
            </tr>
            <tr>
                <td>maxId: </td>
                <td><input type="text" name="maxId" value=""><br> if pageNo = 1 than it is blank otherwise <br> it value would be pass from first page response</td>
            </tr>
                  <tr>
                <td></td>
                <td></td>
            </tr>
        </table>
        <br/>
        <br/>
        <br/>
        <input type="hidden" name="apiName" value="<?php echo $apiName; ?>">
        <a href="javascript:void(0);" class="button submit-button" rel="<?php echo $apiName; ?>" onClick="submitForm(this.rel);"><?php echo $apiName; ?></a>
    </form>
</div> 	
        
          
     
<?php $apiName = "getJobList_GET"; ?>
<div class="all" id="<?php echo $apiName; ?>" style="display: none;">
    <form name="apiform" id="<?php echo $apiName; ?>Form" enctype="multipart/form-data" method="GET" action="<?= $domain.$apiArr[$apiName]; ?>">
        <table>
            <tr>
                <td></td>
                <td>Note : This api will use for get job list according to logged in user.</td>
            </tr>
            <tr>
                <td>X-Device-ID: (Header)</td>
                <td><input type="text" name="X-Device-ID" class="hi" value="<?= $x_device_id; ?>"></td>
            </tr>
            <tr>
                <td>X-API-Key: (Header)</td>
                <td><input type="text" name="X-API-Key" class="hi" value="<?= $x_api_key; ?>"></td>
            </tr>
            <tr>
                <td>X-Session-ID: (Header)</td>
                <td><input type="text" name="X-Session-ID" class="hi" value=""></td>
            </tr>
            <tr>
                <td>clientUserId: </td>
                <td><input type="text" name="clientUserId" value=""><br/> If logged in user is executive and this user want to check client's job <br/>that time this client user id will pass. and he can see only those jobs <br/>which is created by him and assigned to this client.</td>
            </tr>
            <tr>
                <td>latitude: </td>
                <td><input type="text" name="latitude" value=""><br> If GEO location is not pass than it will list according to last create come first</td>
            </tr>
            <tr>
                <td>longitude: </td>
                <td><input type="text" name="longitude" value=""></td>
            </tr>
            <tr>
                <td>pageNo: </td>
                <td><input type="text" name="pageNo" value=""></td>
            </tr>
            <tr>
                <td>maxId: </td>
                <td><input type="text" name="maxId" value=""><br> if pageNo = 1 than it is blank otherwise <br> it value would be pass from first page response</td>
            </tr>
            <tr>
                <td>isMyJob:</td>
                <td>
                    <select name="isMyJob" >
                        <option value=""> (blank)  : All </option>
                        <option value="1"> 1 : yes </option>
                    </select>
            <br> pass isMyJob = 1 when login user is subcontractor and want to see <br>his approve or pending for approval job list.
                </td>
            </tr>
            
                  <tr>
                <td></td>
                <td></td>
            </tr>
        </table>
        <br/>
        <br/>
        <br/>
        <input type="hidden" name="apiName" value="<?php echo $apiName; ?>">
        <a href="javascript:void(0);" class="button submit-button" rel="<?php echo $apiName; ?>" onClick="submitForm(this.rel);"><?php echo $apiName; ?></a>
    </form>
</div> 	
        
        
<?php $apiName = "getJobContractorList_GET"; ?>
<div class="all" id="<?php echo $apiName; ?>" style="display: none;">
    <form name="apiform" id="<?php echo $apiName; ?>Form" enctype="multipart/form-data" method="GET" action="<?= $domain.$apiArr[$apiName]; ?>">
        <table>
            <tr>
                <td></td>
                <td>Note : This api will use for get job's contractor list according to them status.</td>
            </tr>
            <tr>
                <td>X-Device-ID: (Header)</td>
                <td><input type="text" name="X-Device-ID" class="hi" value="<?= $x_device_id; ?>"></td>
            </tr>
            <tr>
                <td>X-API-Key: (Header)</td>
                <td><input type="text" name="X-API-Key" class="hi" value="<?= $x_api_key; ?>"></td>
            </tr>
            <tr>
                <td>X-Session-ID: (Header)</td>
                <td><input type="text" name="X-Session-ID" class="hi" value=""></td>
            </tr>
            <tr>
                <td>jobId: (URI) </td>
                <td><input type="text" name="jobId" value=""></td>
            </tr>
            <tr>
                <td>statusType: (URI)</td>
                <td>
                    <select name="statusType" >
                        <option value="hired"> hired : Hired List </option>
                        <option value="applied"> applied : Applied List </option>
                    </select>
            </td>
            </tr><tr>
                <td>pageNo: </td>
                <td><input type="text" name="pageNo" value=""></td>
            </tr>
            <tr>
                <td>maxId: </td>
                <td><input type="text" name="maxId" value=""><br> if pageNo = 1 than it is blank otherwise <br> it value would be pass from first page response</td>
            </tr>
            
            
                  <tr>
                <td></td>
                <td></td>
            </tr>
        </table>
        <br/>
        <br/>
        <br/>
        <input type="hidden" name="apiName" value="<?php echo $apiName; ?>">
        <a href="javascript:void(0);" class="button submit-button" rel="<?php echo $apiName; ?>" onClick="submitForm(this.rel);"><?php echo $apiName; ?></a>
    </form>
</div> 	
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
	  
	  
	  
	  
	  
	
	
	
	
	</td>
  </tr>
  	<tr>
            <td align="left" style="alignment-adjust: central;text-align: center; " colspan="2"><br/><br/><br/>Result :<br/><div style="max-width: 1200px;margin: 0 auto;max-height: 800px;border: 1px solid #000;overflow: auto;"><code><pre id="results"></pre></code></div></td>
  </tr>
  
<!--	<tr>
    <td align="left" style="alignment-adjust: central " colspan="2">Response : statusCode <br> 0 = Server code error or Invalid api request<br>1 = Operation performed successfully.<br> 2 = Invalid requested data Or Api not perform what we have aim to be perform. <br> 9 = Invalid api or data access(If you will get this status then please logout user immediately).</td>
  </tr>-->
  </table>
</body>
</html>