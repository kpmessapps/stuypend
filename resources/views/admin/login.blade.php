<!DOCTYPE html>
<html>

    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title><?= env('ADMIN_PROJECT_NAME');?> | Login</title>

        <link href="{!! asset('asset/css/bootstrap.min.css') !!}" rel="stylesheet">
        <link href="{!! asset('asset/font-awesome/css/font-awesome.css') !!}" rel="stylesheet">

        <link href="{!! asset('asset/css/animate.css') !!}" rel="stylesheet">
        <link href="{!! asset('asset/css/style.css') !!}" rel="stylesheet">

    </head>

    <body class="gray-bg">

        <div class="middle-box text-center loginscreen  animated fadeInDown">
            <div>
                <div>

                    <h1 class="logo-name"><?= env('ADMIN_PROJECT_SHORT_NAME');?></h1>

                </div>
                <h3>Welcome to <?= env('ADMIN_PROJECT_NAME');?></h3>
                <p>
                    <!--Continually expanded and constantly improved Inspinia Admin Them (IN+)-->
                </p>
                <p>Login in. To see it in action.</p>
                <form class="m-t" role="form" action="{{route('admin::loginAuth')}}" method="post">
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="Username" required="" name="username">
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" placeholder="Password" required="" name="password">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </div>
                    <button type="submit" class="btn btn-primary block full-width m-b">Login</button>

                    @if (session('errorMessage'))
                    <div class="alert alert-danger">
                        {{ session('errorMessage') }}
                    </div>
                    @endif

                    @if (count($errors))
                    <div class="alert alert-danger">
                        <ul>
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                </form>
                <p class="m-t"> <small><?= env('ADMIN_PROJECT_NAME');?> &copy; 2017-2020</small> </p>
            </div>
        </div>

        <!-- Mainly scripts -->
        <script src="{!! asset('asset/js/jquery-2.1.1.js') !!}"></script>
        <script src="{!! asset('asset/js/bootstrap.min.js') !!}"></script>

    </body>

</html>