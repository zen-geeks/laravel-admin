<?php

namespace Encore\Admin\Middleware;

use Closure;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Google2FAMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (in_array($request->route()->getName(), ['admin.login', 'admin.login-post', 'admin.logout', 'admin.2fa-form', 'admin.2fa-set'])) {
            return $next($request);
        }

        $user = Admin::user();
        if (!$user) {
            return $next($request);
        }

        // принудительная повторная авторизация
        if ($user->is_need_relogin) {
            $user->is_need_relogin = 0;
            $user->remember_token = null;
            $user->google2fa_remember_token = null;
            $user->save();

            Auth::logout();
            $request->session()->invalidate();
            return redirect(config('admin.route.prefix'));
        }

        // 2FA не включена
        if (!$user->is_google2fa) {
            return $next($request);
        }

        // 2FA включена - проверки

        // Код не установлен
        if (empty($user->google2fa_secret)) {
            return redirect()->route('admin.2fa-set');
        }

        // 2FA пройдена
        if ($request->session()->has('2fa_admin')) {
            return $next($request);
        }

        // Восстановление авторизации по remember_token, который прошёл 2FA
        if (!empty($user->remember_token) && $user->remember_token === $user->google2fa_remember_token) {
            $request->session()->put('2fa_admin', 'valid');
            return $next($request);
        }

        return redirect()->route('admin.2fa-form');
    }
}
