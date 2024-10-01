<?php

namespace Encore\Admin\Auth\Database;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

trait HasPermissions
{
    private function _getRolePermissions(): array
    {
        static $role_permissions = [];
        if (!empty($role_permissions))
            return $role_permissions;

        $cache = config('admin.cache');
        $cache = $cache['enable'] ? Cache::store($cache['store']) : null;
        $cache_key = 'admin_role_permissions';
        if ($cache) {
            $role_permissions = $cache->get($cache_key) ?? [];
            if (!empty($role_permissions))
                return $role_permissions;
        }

        $roles = Role::with('permissions')->get();
        foreach ($roles as $role) {
            $role_permissions[$role->id] = $role['permissions']->toArray();
        }

        if ($cache) {
            $cache->put($cache_key, $role_permissions, 600);
        }

        return $role_permissions;
    }

    private function _getUserPermissions(): array
    {
        static $user_permissions = [];
        if (!empty($user_permissions[$this->id]))
            return $user_permissions[$this->id];

        $cache = config('admin.cache');
        $cache = $cache['enable'] ? Cache::store($cache['store']) : null;
        $cache_key = 'admin_user_permissions';
        if ($cache) {
            $user_permissions = $cache->get($cache_key) ?? [];
            if (!empty($user_permissions[$this->id]))
                return $user_permissions[$this->id];
        }

        $user_permissions[$this->id] = [
            'roles' => $this->roles->toArray(),
            'permissions' => $this->permissions->toArray(),
        ];

        if ($cache) {
            $cache->put($cache_key, $user_permissions, 600);
        }

        return $user_permissions[$this->id];
    }

    /**
     * Get all permissions of user.
     *
     * @return mixed
     */
    public function allPermissions(): Collection
    {
        $permissions = [];

        $role_permissions = $this->_getRolePermissions();
        $user_permissions = $this->_getUserPermissions();
        $role_ids = array_column($user_permissions['roles'], 'id');
        foreach ($role_ids as $role_id) {
            if (!empty($role_permissions[$role_id])) {
                foreach ($role_permissions[$role_id] as $permission) {
                    $permissions[] = new Permission($permission);
                }
            }
        }
        if (!empty($user_permissions['permissions'])) {
            foreach ($user_permissions['permissions'] as $permission) {
                $permissions[] = new Permission($permission);
            }
        }

        return collect($permissions);
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

        static $can = [];
        if (!isset($can[$this->id])) {
            $can[$this->id] = [
                'user_permissions' => array_column($this->_getUserPermissions()['permissions'], 'slug'),
            ];
        }
        if (in_array($ability, $can[$this->id]['user_permissions']))
            return true;

        if (!isset($can[$this->id]['role_permissions'])) {
            $permissions = [];
            $role_ids = array_column($this->_getUserPermissions()['roles'], 'id');
            $role_permissions = $this->_getRolePermissions();
            foreach ($role_ids as $role_id) {
                if (!empty($role_permissions[$role_id]))
                    $permissions = array_merge($permissions, $role_permissions[$role_id]);
            }
            $can[$this->id]['role_permissions'] = array_column($permissions, 'slug');
        }

        return in_array($ability, $can[$this->id]['role_permissions']);
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
        static $is_administrator = [];
        if (!isset($is_administrator[$this->id])) {
            $is_administrator[$this->id] = $this->isRole('administrator');
        }

        return $is_administrator[$this->id];
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
        return in_array($role, $this->getUserRoles());
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
        return !empty(array_intersect($this->getUserRoles(), $roles));
    }

    private function getUserRoles(): array
    {
        static $user_roles = [];
        if (!isset($user_roles[$this->id])) {
            $user_roles[$this->id] = array_column($this->_getUserPermissions()['roles'], 'slug');
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
