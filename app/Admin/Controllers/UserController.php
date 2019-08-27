<?php

namespace App\Admin\Controllers;

use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UserController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'users';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User);

        $grid->column('id', __('Id'));
        $grid->column('email', __('Email'));
        $grid->column('phone', __('Phone'));
        $grid->column('openid', __('Openid'));
        $grid->column('github_id', __('Github id'));
        $grid->column('username', __('Username'));
        $grid->column('password', __('Password'));
        $grid->column('nickname', __('Nickname'));
        $grid->column('avatar', __('Avatar'))->image('', 50, 50);
        $grid->column('sex', __('Sex'))->display(function ($released) {
        	return isset(config('system.sex')[$released]) ? config('system.sex')[$released] : '';
		});
        $grid->column('sign', __('Sign'));
        $grid->column('birthday', __('Birthday'));
        $grid->column('province', __('Province'));
        $grid->column('city', __('City'));
        $grid->column('district', __('District'));
        $grid->column('country', __('Country'));
        $grid->column('profession', __('Profession'))->display(function ($released) {
			return isset(config('system.profession')[$released]) ? config('system.profession')[$released] : '';
		});
        $grid->column('online', __('Online'));
        $grid->column('enable', __('Enable'));
        $grid->column('last_login_ip', __('Last login ip'));
        $grid->column('last_login_time', __('Last login time'));
        $grid->column('remember_token', __('Remember token'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

		$grid->paginate(10);

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(User::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('email', __('Email'));
        $show->field('phone', __('Phone'));
        $show->field('openid', __('Openid'));
        $show->field('github_id', __('Github id'));
        $show->field('username', __('Username'));
        $show->field('password', __('Password'));
        $show->field('nickname', __('Nickname'));
        $show->field('avatar', __('Avatar'));
        $show->field('sex', __('Sex'));
        $show->field('sign', __('Sign'));
        $show->field('birthday', __('Birthday'));
        $show->field('province', __('Province'));
        $show->field('city', __('City'));
        $show->field('district', __('District'));
        $show->field('country', __('Country'));
        $show->field('profession', __('Profession'));
        $show->field('online', __('Online'));
        $show->field('enable', __('Enable'));
        $show->field('last_login_ip', __('Last login ip'));
        $show->field('last_login_time', __('Last login time'));
        $show->field('remember_token', __('Remember token'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new User);

        $form->email('email', __('Email'));
        $form->mobile('phone', __('Phone'));
        $form->text('openid', __('Openid'));
        $form->text('github_id', __('Github id'));
        $form->text('username', __('Username'));
        $form->password('password', __('Password'));
        $form->text('nickname', __('Nickname'));
        $form->image('avatar', __('Avatar'));
        $form->switch('sex', __('Sex'))->default(1);
        $form->text('sign', __('Sign'));
        $form->datetime('birthday', __('Birthday'))->default(date('Y-m-d H:i:s'));
        $form->number('province', __('Province'));
        $form->number('city', __('City'));
        $form->number('district', __('District'));
        $form->number('country', __('Country'));
        $form->number('profession', __('Profession'));
        $form->text('online', __('Online'))->default('offline');
        $form->switch('enable', __('Enable'))->default(1);
        $form->text('last_login_ip', __('Last login ip'));
        $form->datetime('last_login_time', __('Last login time'))->default(date('Y-m-d H:i:s'));
        $form->text('remember_token', __('Remember token'));

        return $form;
    }
}
