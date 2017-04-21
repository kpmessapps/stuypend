<!DOCTYPE html>
<html>
<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('pageTitle')</title>
 
    
     @yield('newCssLoad')
     
</head>

<body>
    <div id="wrapper">
        @include('layouts.sidebar')

        <div id="page-wrapper" class="gray-bg">
         @include('layouts.header')


        <div class="wrapper wrapper-content animated fadeInRight">
        
        @yield('mainContainerArea')


        </div>


        @include('layouts.footer')

        </div>
    </div>

    <!-- Mainly scripts -->
    <script src="{!! asset('asset/js/jquery-2.1.1.js') !!}"></script>
    <script src="{!! asset('asset/js/bootstrap.min.js') !!}"></script>
    <script src="{!! asset('asset/js/plugins/metisMenu/jquery.metisMenu.js') !!}"></script>
    <script src="{!! asset('asset/js/plugins/slimscroll/jquery.slimscroll.min.js') !!}"></script>
    
    <!-- Peity -->
    <script src="{!! asset('asset/js/plugins/peity/jquery.peity.min.js') !!}"></script>
    <script src="{!! asset('asset/js/demo/peity-demo.js') !!}"></script>

    

    @yield('newJsLoad')
    
    @yield('customPageJs')
    
</body>

<!-- Mirrored from webapplayers.com/inspinia_admin-v2.3/dashboard_4_1.html by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 01 Sep 2015 13:12:30 GMT -->
</html>
