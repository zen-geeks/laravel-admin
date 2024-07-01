<?php

namespace Encore\Admin\Auth\Database;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

trait HasPermissions
{
    /**
     * Get all permissions of user.
     *
     * @return mixed
     */
    public function allPermissions(): Collection
    {
        $cache = config('admin.cache');
        $cache = $cache['enable'] ? Cache::store($cache['store']) : null;
        if (!$cache)
            return $this->_allPermissions();

        $cache_key = 'admin_permissions_all_'.$this->id;
        $permissions = $cache->get($cache_key);
        if (!$permissions) {
            $permissions = $this->_allPermissions();
            $cache->put($cache_key, $permissions, 600);
        }

        return $permissions;
    }

    /**
     * Get all permissions of user.
     *
     * @return mixed
     */
    private function _allPermissions(): Collection
    {
        return $this->roles()->with('permissions')->get()->pluck('permissions')->flatten()->merge($this->permissions);
    }

    /**
     * Check if user has permission.
     *
     * @param $ability
     * @param array $arguments
     *
     * @return bool
     */
    public function can($ability, $arguments = []): bool
    {
        if (empty($ability)) {
            return true;
        }

        if ($this->isAdministrator()) {
            return true;
        }

        $cache = config('admin.cache');
        $cache = $cache['enable'] ? Cache::store($cache['store']) : null;

        static $user_permissions = [];
        if (!isset($user_permissions[$this->id])) {
            if (!$cache) {
                $user_permissions[$this->id] = $this->permissions->pluck('slug');
            } else {
                $permissions_key = 'admin_permissions_can_' . $this->id;
                $user_permissions[$this->id] = $cache->get($permissions_key);
                if (!$user_permissions[$this->id]) {
                    $user_permissions[$this->id] = $this->permissions->pluck('slug');
                    $cache->put($permissions_key, $user_permissions[$this->id], 600);
                }
            }
        }
        if ($user_permissions[$this->id]->contains($ability)) {
            return true;
        }

        static $user_roles = [];
        if (!isset($user_roles[$this->id])) {
            if (!$cache) {
                $user_roles[$this->id] = $this->roles->pluck('permissions')->flatten()->pluck('slug');
            } else {
                $roles_key = 'admin_roles_permissions_' . $this->id;
                $user_roles[$this->id] = $cache->get($roles_key);
                if (!$user_roles[$this->id]) {
                    $user_roles[$this->id] = $this->roles->pluck('permissions')->flatten()->pluck('slug');
                    $cache->put($roles_key, $user_roles[$this->id], 600);
                }
            }
        }
        return $user_roles[$this->id]->contains($ability);
    }

    /**
     * Check if user has no permission.
     *
     * @param $permission
     *
     * @return bool
     */
    public function cannot(string $permission): bool
    {
        return !$this->can($permission);
    }

    /**
     * Check if user is administrator.
     *
     * @return mixed
     */
    public function isAdministrator(): bool
    {
        static $res = [];
        if (!isset($res[$this->id]))
            $res[$this->id] = $this->isRole('administrator');
        return $res[$this->id];
    }

    /**
     * Check if user is $role.
     *
     * @param string $role
     *
     * @return mixed
     */
    public function isRole(string $role): bool
    {
        $user_roles = $this->getUserRoles();
        return $user_roles->contains($role);
    }

    /**
     * Check if user in $roles.
     *
     * @param array $roles
     *
     * @return mixed
     */
    public function inRoles(array $roles = []): bool
    {
        $user_roles = $this->getUserRoles();
        return $user_roles->intersect($roles)->isNotEmpty();
    }

    private function getUserRoles()
    {
        static $user_roles = [];
        if (!isset($user_roles[$this->id])) {
            $cache = config('admin.cache');
            $cache = $cache['enable'] ? Cache::store($cache['store']) : null;
            if (!$cache) {
                $user_roles[$this->id] = $this->roles->pluck('slug');
            } else {
                $cache_key = 'admin_roles__' . $this->id;
                $user_roles[$this->id] = $cache->get($cache_key);
                if (!$user_roles[$this->id]) {
                    $user_roles[$this->id] = $this->roles->pluck('slug');
                    $cache->put($cache_key, $user_roles[$this->id], 600);
                }
            }
        }
        return $user_roles[$this->id];
    }

    /**
     * If visible for roles.
     *
     * @param $roles
     *
     * @return bool
     */
    public function visible(array $roles = []): bool
    {
        if (empty($roles)) {
            return true;
        }

        $roles = array_column($roles, 'slug');

        return $this->inRoles($roles) || $this->isAdministrator();
    }

    /**
     * Detach models from the relationship.
     *
     * @return void
     */
    protected static function bootHasPermissions()
    {
        static::deleting(function ($model) {
            $model->roles()->detach();

            $model->permissions()->detach();
        });
    }
}
