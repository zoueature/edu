<?php

namespace App\Admin\Actions\ReviewApply;

use App\Http\Constant\Role;
use App\Http\Constant\School;
use App\SchoolTeacher;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Accept extends RowAction
{
    public $name = '同意';

    public function handle(Model $model)
    {
        if ($model->status != School::SCHOOL_STATUS_REVIEW) {
            return $this->response()->error('只能审核待审核的申请.')->refresh();
        }
        $model->status = School::REVIEW_STATUS_ACCEPT;
        $school = $model->school;
        $school->status = School::SCHOOL_STATUS_NORMAL;
        $schoolTeacher = app(SchoolTeacher::class);
        $schoolTeacher->teacher_id = $model->apply_teacher_id;
        $schoolTeacher->school_id = $model->school_id;
        $schoolTeacher->role = Role::SCHOOL_ROLE_ADMIN;
        DB::transaction(function () use ($model, $school, $schoolTeacher) {
            $model->save();
            $school->save();
            $schoolTeacher->save();
        });
        return $this->response()->success('Success message.')->refresh();
    }

}