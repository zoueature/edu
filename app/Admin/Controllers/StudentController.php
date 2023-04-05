<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Chat\SenderMessage;
use App\Admin\Actions\ReviewApply\Accept;
use App\Admin\Actions\ReviewApply\Refuse;
use App\Admin\Actions\User\Forbidden;
use App\Admin\Actions\User\Recover;
use App\Student;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class StudentController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Student';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Student());

        $grid->column('id', __('Id'));
        $grid->column('school_id', __('School id'));
        $grid->column('username', __('Username'));
        $grid->column('password', __('Password'));
        $grid->column('name', __('Name'));
        $grid->column('age', __('Age'));
        $grid->column('grade', __('Grade'));
        $grid->column('class', __('Class'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->actions(function ($actions) {
            $actions->add(new Recover());
            $actions->add(new Forbidden());
            $actions->add(new SenderMessage());
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
        $show = new Show(Student::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('school_id', __('School id'));
        $show->field('username', __('Username'));
        $show->field('password', __('Password'));
        $show->field('name', __('Name'));
        $show->field('age', __('Age'));
        $show->field('grade', __('Grade'));
        $show->field('class', __('Class'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($byCreate = false)
    {
        $form = new Form(new Student());

        $form->number('school_id', __('School id'));
        $form->text('username', __('Username'));
        $form->password('password', __('Password'));
        $form->text('name', __('Name'));
        $form->text('age', __('Age'));
        $form->text('grade', __('Grade'));
        $form->text('class', __('Class'));

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
