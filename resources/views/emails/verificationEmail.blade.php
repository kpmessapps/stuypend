<!DOCTYPE html>
<html>
    <head>
        <title>Carpo</title>
        <style type="text/css">
            body{
                font-family: sans-serif;
            }
        </style>
    </head>
    <body>
        <div style="width:600px;margin:0 auto;background-color:#ffffff;">
            <div style="text-align:center;">
                <img src="{!! asset('asset/logo/сarpo_final_logo.png') !!}" />
            </div>   
            <div style="margin:30px 0 0 0;">
                <p style="margin: 20px 0px;">Hello!</p>
                <p style="margin: 20px 0px;">Verify your email by clicking the link below on your smartphone.
                <br/><br/><strong><a href="{{ $verificationEmailLink or '' }}">Verify Email</a></strong>
                <br/><br/>If you did not request a password reset, please disregard this message.
                </p>
                <br>The Carpo Team
                <br><a href="http://www.ucarpo.com">http://www.ucarpo.com</a>
            <div>
        </div>    
    </body>
</html>