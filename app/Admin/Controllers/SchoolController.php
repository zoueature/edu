<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\ReviewApply\Accept;
use App\Admin\Actions\ReviewApply\Refuse;
use App\Admin\Actions\School\Forbidden;
use App\Admin\Actions\School\Recover;
use App\School;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SchoolController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'School';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new School());

        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('country', __('Country'));
        $grid->column('province', __('Province'));
        $grid->column('city', __('City'));
        $grid->column('address', __('Address'));
        $grid->column('status', __('Status'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->actions(function ($actions) {
            $actions->add(new Forbidden());
            $actions->add(new Recover());
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
        $show = new Show(School::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('country', __('Country'));
        $show->field('province', __('Province'));
        $show->field('city', __('City'));
        $show->field('address', __('Address'));
        $show->field('status', __('Status'));
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
        $form = new Form(new School());

        $form->text('name', __('Name'));
        $form->text('country', __('Country'));
        $form->text('province', __('Province'));
        $form->text('city', __('City'));
        $form->text('address', __('Address'));
        $form->switch('status', __('Status'));

        return $form;
    }
}
