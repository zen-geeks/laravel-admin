<?php

namespace Encore\Admin\Services;

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Auth\Database\Role;
use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function getAdminByRole(string|array $user_role_slug): array
    {
        $cache = $this->getCache();
        $cache_key = 'admin_user_roles';
        $list = $cache?->get($cache_key);
        if (!$list) {
            $admin_role_users = DB::table(config('admin.database.role_users_table'))->select(['role_id', 'user_id'])->get()->toArray();
            $roles = Role::pluck('slug', 'id')->toArray();
            $users = $this->getAdminList();
            foreach ($admin_role_users as $user) {
                $role_slug = $roles[$user->role_id] ?? null;
                if (!empty($role_slug) && !empty($users[$user->user_id]))
                    $list[$role_slug][$user->user_id] = $users[$user->user_id];
            }
            $cache->put($cache_key, $list, 3600);
        }

        if (is_array($user_role_slug)) {
            $result = [];
            foreach ($user_role_slug as $slug) {
                if (!empty($list[$slug])) {
                    foreach ($list[$slug] as $user_id => $user) {
                        $result[$user_id] = $user;
                    }
                }
            }
            return $result;
        }

        return $list[$user_role_slug] ?? [];
    }

    private function getCache(): ?Repository
    {
        return config('admin.cache')['enable'] ? Cache::store(config('admin.cache')['store']) : null;
    }

    private function getAdminList(?int $id = null): ?array
    {
        $cache = $this->getCache();
        $cache_key = 'admin_user_list';
        $list = $cache?->get($cache_key);
        if (!$list) {
            $users = Administrator::select(['id', 'username', 'name'])->get();
            foreach ($users as $user) {
                $list[$user->id] = $user->toArray();
            }
            $cache->put($cache_key, $list, 3600);
        }

        if (is_null($id))
            return $list;
        return $list[$id] ?? null;
    }

    public function getRoles(): array
    {
        $cache = $this->getCache();
        $cache_key = 'admin_roles';
        $list = $cache?->get($cache_key);
        if (!$list) {
            $roles = Role::get()->toArray();

            foreach ($roles as $role) {
                $list[$role['id']] = [
                    'name' => $role['name'],
                    'slug' => $role['slug'],
                ];
            }

            $cache->put($cache_key, $list, 3600);
        }

        return $list ?? [];
    }
}