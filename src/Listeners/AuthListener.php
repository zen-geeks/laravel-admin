<?php

namespace Encore\Admin\Listeners;

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthListener
{
    public function handle(Authenticated|Failed $event): void
    {
        if ($event instanceof Authenticated && !empty($event->user->is_blocked)) {
            $this->logout($event->user, true);
            abort(403, trans('admin.deny'));
        }

        $auth_limit = config('admin.auth.failed_auth_limit');
        if ($auth_limit && $event->user) {
            if ($event instanceof Authenticated) {
                if ($event->user->failed_auths >= $auth_limit) {
                    $this->logout($event->user);
                    abort(403, trans('admin.deny'));
                } elseif ($event->user->failed_auths) {
                    $event->user->failed_auths = 0;
                    $event->user->save();
                }
            } elseif ($event instanceof Failed && $event->user->failed_auths < 65535) {
                $event->user->failed_auths++;
                $event->user->save();
            }
        }
    }

    private function logout(Administrator $user, $change_password = false): void
    {
        $user->remember_token = null;
        $user->google2fa_remember_token = null;
        if ($change_password)
            $user->password = Hash::make(Str::random(32));
        $user->save();

        Admin::guard()->logout();
        request()->session()->invalidate();
    }
}
