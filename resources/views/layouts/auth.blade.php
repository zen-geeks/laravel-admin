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

    <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/AdminLTE/dist/css/adminlte.min.css") }}">
    <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/laravel-admin/laravel-admin.css") }}">
    <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/AdminLTE/plugins/fontawesome-free/css/all.min.css") }}">
    @section('styles')
    @show
</head>
<body data-bs-theme="light">
<div class="auth-2fa">
    @yield('content')
</div>

<script src="{{ admin_asset("vendor/laravel-admin/AdminLTE/plugins/jquery/jquery.min.js")}}"></script>
<script src="{{ admin_asset("vendor/laravel-admin/AdminLTE/plugins/bootstrap5/bootstrap.min.js")}}"></script>
@section('scripts')
@show
</body>
</html>
