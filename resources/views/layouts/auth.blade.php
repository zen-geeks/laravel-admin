<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{config('admin.title')}} | {{trans('admin.ext.2fa.title_short')}}</title>
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">

    @if(!is_null($favicon = Admin::favicon()))
        <link rel="icon" href="{{$favicon}}">
    @endif

    <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/laravel-admin/laravel-admin.css") }}">
    <!-- Bootstrap 3.4.1 -->
    <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/AdminLTE/bootstrap/css/bootstrap.min.css") }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/font-awesome/css/font-awesome.min.css") }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/AdminLTE/dist/css/AdminLTE.min.css") }}">
    @section('styles')
    @show
</head>
<body>
<div class="auth-2fa">
    @yield('content')
</div>
<!-- jQuery 2.2.4 -->
<script src="{{ admin_asset("vendor/laravel-admin/AdminLTE/plugins/jQuery/jQuery-2.2.4.min.js")}}"></script>
<!-- Bootstrap 3.4.1 -->
<script src="{{ admin_asset("vendor/laravel-admin/AdminLTE/bootstrap/js/bootstrap.min.js")}}"></script>
@section('scripts')
@show
</body>
</html>
