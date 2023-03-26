<?php

namespace App\Admin\Actions\User;

use App\Http\Constant\Auth;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class Recover extends RowAction
{
    public $name = '恢复';

    public function handle(Model $model)
    {
        if ($model->status != Auth::USER_STATUS_FORBIDDEN) {
            return $this->response()->error('只能恢复封禁状态的用户.')->refresh();
        }
        $model->status = Auth::USER_STATUS_NORMAL;
        $model->save();
        return $this->response()->success('Success message.')->refresh();
    }

}