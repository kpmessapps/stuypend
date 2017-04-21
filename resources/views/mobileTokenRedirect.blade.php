<!DOCTYPE html>
<html>
    <head>
        <title>GTE App</title>
    </head>
    <body>
        <div class="container">
            <div class="content">
                <div class="title">GTE : Redirecting...</div>
            </div>
        </div>
        <script type="text/javascript">
            /**
            * Determine the mobile operating system.
            * This function returns one of 'iOS', 'Android', 'Windows Phone', or 'unknown'.
            *
            * @returns  {String}
            */
           function getMobileOperatingSystem() {
             var userAgent = navigator.userAgent || navigator.vendor || window.opera;
           
                 // Windows Phone must come first because its UA also contains "Android"
               if (/windows phone/i.test(userAgent)) {
                   window.location.href="http://www.gte.com";
                   return true;
               }
           
               if (/android/i.test(userAgent)) {
                    if("{{ $tokenType }}" == "emailVerification"){
                        window.location.href = "gte://ve?tkn={{ $emailToken or ''}}";
                         setTimeout(function(){
                            window.location.href="http://www.gte.png.com";
                        },4000);
                        return "false";
                    }else if("{{ $tokenType }}" == "resetPassword"){
                        window.location.href = "gte://fp?tkn={{ $emailToken or ''}}";
                         setTimeout(function(){
                            window.location.href="http://www.gte.com";
                        },4000);
                        return "false";
                    }
               }
           
               // iOS detection from: http://stackoverflow.com/a/9039885/177710
               if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
                    if("{{ $tokenType }}" == "emailVerification"){
                        window.location.href = "gte://ve?tkn={{ $emailToken or ''}}";
                         setTimeout(function(){
                            window.location.href="http://www.gte.com";
                        },4000);
                        return "false";
                    }else if("{{ $tokenType }}" == "resetPassword"){
                        window.location.href = "gte://fp?tkn={{ $emailToken or ''}}";
                        setTimeout(function(){
                            window.location.href="http://www.gte.com";
                        },4000);
                        return "false";
                    }
               }
                window.location.href="http://www.gte.com";
               return "false";
           }
           getMobileOperatingSystem();
        </script>
    </body>
</html>
