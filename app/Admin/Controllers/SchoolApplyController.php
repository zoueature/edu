<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\ReviewApply\Accept;
use App\Admin\Actions\ReviewApply\Refuse;
use App\SchoolApply;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SchoolApplyController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'SchoolApply';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SchoolApply());

        $grid->column('id', __('Id'));
        $grid->column('apply_teacher_id', __('Apply teacher id'));
        $grid->column('school_id', __('School id'));
        $grid->column('school.name', __('School Name'));
        $grid->column('school.country', __('School Country'));
        $grid->column('school.province', __('School Province'));
        $grid->column('school.city', __('School City'));
        $grid->column('school.address', __('School Address'));
        $grid->column('status', __('Status'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->actions(function ($actions) {
            $actions->add(new Accept());
            $actions->add(new Refuse());
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
        $show = new Show(SchoolApply::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('apply_teacher_id', __('Apply teacher id'));
        $show->field('school_id', __('School id'));
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
        $form = new Form(new SchoolApply());

        $form->number('apply_teacher_id', __('Apply teacher id'));
        $form->number('school_id', __('School id'));
        $form->switch('status', __('Status'));

        return $form;
    }
}
