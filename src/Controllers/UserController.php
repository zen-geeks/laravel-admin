<?php

namespace Encore\Admin\Controllers;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Services\UserService;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Hash;

class UserController extends AdminController
{
    /**
     * {@inheritdoc}
     */
    protected function title()
    {
        return trans('admin.administrator');
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $userModel = config('admin.database.users_model');

        $grid = new Grid(new $userModel());

        $grid->column('id', 'ID')->sortable();
        $grid->column('username', trans('admin.username'));
        $grid->column('name', trans('admin.name'));
        $grid->column('roles', trans('admin.roles'))->pluck('name')->label();
        $grid->column('is_blocked', trans('admin.is_blocked'))->bool();
        $grid->column('is_need_relogin', trans('admin.is_need_relogin'))->bool();
        $grid->column('is_google2fa', trans('admin.is_google2fa'))->bool();
        $grid->column('failed_auths', trans('admin.failed_auths'))->hide();
        $grid->column('created_at', trans('admin.created_at'));
        $grid->column('updated_at', trans('admin.updated_at'));

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            if ($actions->getKey() == 1) {
                $actions->disableDelete();
            }
        });

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function (Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
        });

        /** @var UserService $user_service */
        $user_service = app(UserService::class);
        $grid->filter(function($filter) use ($user_service) {
            $filter->column(1/2, function ($filter) use ($user_service) {
                $filter->like('username', trans('admin.username'));
                $filter->like('name', trans('admin.name'));
                $filter->where(function ($query) use ($user_service) {
                    $query->whereIn('id', array_keys($user_service->getAdminByRole($this->input)));
                }, 'Role')->select(array_column($user_service->getRoles(),'name','slug'));
            });
            $filter->column(1/2, function ($filter) {
                $filter->bool('is_blocked', true, trans('admin.is_blocked'));
                $filter->bool('is_google2fa', true, trans('admin.is_google2fa'));
            });
        });

        $grid->export(function ($export) {
            $export->originalValue(['is_blocked', 'is_need_relogin', 'is_google2fa']);
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        $userModel = config('admin.database.users_model');

        $show = new Show($userModel::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('username', trans('admin.username'));
        $show->field('name', trans('admin.name'));
        $show->field('roles', trans('admin.roles'))->as(function ($roles) {
            return $roles->pluck('name');
        })->label();
        $show->field('permissions', trans('admin.permissions'))->as(function ($permission) {
            return $permission->pluck('name');
        })->label();
        $show->field('is_blocked', trans('admin.is_blocked'))->check();
        $show->field('is_need_relogin', trans('admin.is_need_relogin'))->check();
        $show->field('is_google2fa', trans('admin.is_google2fa'))->check();
        $show->field('google2fa_secret', trans('admin.google2fa_secret'));
        $show->field('failed_auths', trans('admin.failed_auths'));
        $show->field('created_at', trans('admin.created_at'));
        $show->field('updated_at', trans('admin.updated_at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {
        $userModel = config('admin.database.users_model');
        $permissionModel = config('admin.database.permissions_model');
        $roleModel = config('admin.database.roles_model');

        $form = new Form(new $userModel());

        $userTable = config('admin.database.users_table');
        $connection = config('admin.database.connection');

        $form->display('id', 'ID');
        $form->text('username', trans('admin.username'))
            ->creationRules(['required', "unique:{$connection}.{$userTable}"])
            ->updateRules(['required', "unique:{$connection}.{$userTable},username,{{id}}"]);

        $form->text('name', trans('admin.name'))->rules('required');
        $form->image('avatar', trans('admin.avatar'));
        $form->password('password', trans('admin.password'))->rules('required|confirmed');
        $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required')
            ->default(function ($form) {
                return $form->model()->password;
            });

        $form->ignore(['password_confirmation']);

        $form->multipleSelect('roles', trans('admin.roles'))->options($roleModel::all()->pluck('name', 'id'));
        $form->multipleSelect('permissions', trans('admin.permissions'))->options($permissionModel::all()->pluck('name', 'id'));

        $form->switch('is_blocked', trans('admin.is_blocked'));
        $form->switch('is_need_relogin', trans('admin.is_need_relogin'));
        $form->switch('is_google2fa', trans('admin.is_google2fa'));
        $form->text('google2fa_secret', trans('admin.google2fa_secret'));
        $form->number('failed_auths', trans('admin.failed_auths'))->default(0);

        $form->display('created_at', trans('admin.created_at'));
        $form->display('updated_at', trans('admin.updated_at'));

        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
        });

        $form->saved(function (Form $form) {
            Admin::clearCache();
        });

        return $form;
    }
}
