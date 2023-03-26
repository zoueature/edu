<?php

namespace App\Admin\Actions\ReviewApply;

use App\Http\Constant\School;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Refuse extends RowAction
{
    public $name = '拒绝';

    public function handle(Model $model)
    {
        // $model ...
        if ($model->status != School::SCHOOL_STATUS_REVIEW) {
            return $this->response()->error('只能审核待审核的申请.')->refresh();
        }
        $model->status = School::REVIEW_STATUS_REFUSE;
        $school = $model->school;
        $school->status = School::SCHOOL_STATUS_REFUSE;
        DB::transaction(function () use ($model, $school) {
            $model->save();
            $school->save();
        });
        return $this->response()->success('Success message.')->refresh();
    }

}