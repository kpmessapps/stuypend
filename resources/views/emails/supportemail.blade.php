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
                <p style="margin: 20px 0px;"><strong>Email :</strong>  {{ $email or '' }}
                <br/><strong>Subject :</strong> {{ $subject or '' }}
                <br/><strong>Body :</strong> {{ $body or '' }}
                <br/><br/>
                </p>Happy hydrating!
                <br>The Carpo Team
                <br><a href="http://www.ucarpo.com">http://www.ucarpo.com</a>
            <div>
        </div>    
    </body>
</html>