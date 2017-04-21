<!DOCTYPE html>
<html>
    <head>
        <title>GTE</title>
        <style type="text/css">
            body{
                font-family: sans-serif;
            }
        </style>
    </head>
    <body>
        <div style="width:600px;margin:0 auto;background-color:#ffffff;">
            <div style="text-align:center;">
                <img src="{!! asset('asset/logo/app_email_logo.png') !!}" />
            </div>   
            <div style="margin:30px 0 0 0;">
                <p style="margin: 20px 0px;">Hello!</p>
                <p style="margin: 20px 0px;">You must be thirsty! Reset your password by clicking the link below on your smartphone.
                <br/><br/><strong><a href="{{ $resetPasswordEmailLink or '' }}">Reset password</a></strong>
                <br/><br/>If you did not request a password reset, please disregard this message.
                </p>Happy hydrating!
                <br>The GTE Team
                <br><a href="http://www.gte.com">http://www.gte.com</a>
            <div>
        </div>    
    </body>
</html>