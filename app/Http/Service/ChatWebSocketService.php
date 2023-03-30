<?php

namespace App\Http\Service;

use Illuminate\Http\Request;
use Swoole\Websocket\Frame;
use SwooleTW\Http\Websocket\HandlerContract;

class ChatWebSocketService implements HandlerContract
{

    public function onOpen($fd, Request $request)
    {
//        var_dump($request->user());
//        var_dump($request->input('api_token'));
        // TODO: Implement onOpen() method.
    }

    public function onMessage(Frame $frame)
    {
        // TODO: Implement onMessage() method.
    }

    public function onClose($fd, $reactorId)
    {
        // TODO: Implement onClose() method.
    }
}