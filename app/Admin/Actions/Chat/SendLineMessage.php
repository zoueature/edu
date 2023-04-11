<?php

namespace App\Admin\Actions\Chat;

use App\ChatHistory;
use App\Http\Service\ChatService;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class SendLineMessage extends RowAction
{
    public $name = '发送Line消息';

    public function handle(Model $model, Request $request)
    {
        $bindLines = $model->bindLineUser;
        if ($bindLines->isEmpty()) {
            return $this->response()->error('未绑定line用户')->refresh();
        }
        $svc = app(ChatService::class);
        try {
            $svc->sendLineMessage($model->role(), $model->id, $request->input('msg'));
            return $this->response()->success('Success message.')->refresh();
        } catch (\Exception $e) {
            return $this->response()->error($e->getMessage())->refresh();
        }
    }

    public function form()
    {
        $this->textarea('msg', '消息内容')->rules('required');
    }

}