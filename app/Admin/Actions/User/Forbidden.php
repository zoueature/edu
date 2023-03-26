<?php

namespace App\Admin\Actions\User;

use App\Http\Constant\Auth;
use App\Http\Constant\School;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class Forbidden extends RowAction
{
    public $name = '封禁';

    public function handle(Model $model)
    {
        if ($model->status != Auth::USER_STATUS_NORMAL) {
            return $this->response()->error('只能封禁正常状态的用户.')->refresh();
        }
        $model->status = Auth::USER_STATUS_FORBIDDEN;
        $model->save();
        return $this->response()->success('Success message.')->refresh();
    }

}