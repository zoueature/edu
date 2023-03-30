<?php

namespace App\Admin\Actions\Chat;

use App\ChatHistory;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class SenderMessage extends RowAction
{
    public $name = '发送消息';

    public function handle(Model $model, Request $request)
    {
        // $model ...
        $msg = app(ChatHistory::class);
        $msg->sender_role = 'admin';
        $msg->sender_id = $request->user()->id;
        $msg->msg = $request->get('msg');
        $msg->receiver_role = $model->role();
        $msg->receiver_id = $model->id;
        if (!$msg->save()) {
            return $this->response()->error('发送消息失败.')->refresh();

        }


        return $this->response()->success('Success message.')->refresh();
    }

    public function form()
    {
        $this->textarea('msg', '消息内容')->rules('required');
    }

}