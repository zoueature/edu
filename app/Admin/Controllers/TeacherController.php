<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Chat\SenderMessage;
use App\Admin\Actions\Chat\SendLineMessage;
use App\Admin\Actions\User\Forbidden;
use App\Admin\Actions\User\Recover;
use App\Teacher;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TeacherController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Teacher';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Teacher());

        $grid->column('id', __('Id'));
        $grid->column('email', __('Email'));
        $grid->column('password', __('Password'));
        $grid->column('name', __('Name'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->actions(function ($actions) {
            $actions->add(new Recover());
            $actions->add(new Forbidden());
            $actions->add(new SenderMessage());
            $actions->add(new SendLineMessage());

        });
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
        $show = new Show(Teacher::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('email', __('Email'));
        $show->field('password', __('Password'));
        $show->field('name', __('Name'));
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
        $form = new Form(new Teacher());

        $form->email('email', __('Email'));
        $form->password('password', __('Password'));
        $form->text('name', __('Name'));

        return $form;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return mixed
     */
    public function store()
    {
        $form = $this->form();
        $form->input('password', bcrypt(request()->input('password')));
        return $form->store();
    }
}
