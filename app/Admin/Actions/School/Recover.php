<?php

namespace App\Admin\Actions\School;

use App\Http\Constant\School;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class Recover extends RowAction
{
    public $name = '恢复';

    public function handle(Model $model)
    {
        if ($model->status != School::SCHOOL_STATUS_FORBIDDEN) {
            return $this->response()->error('只能恢复封禁状态的学校.')->refresh();
        }
        $model->status = School::SCHOOL_STATUS_NORMAL;
        $model->save();
        return $this->response()->success('Success message.')->refresh();
    }

}