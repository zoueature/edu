<?php

namespace App\Console\Commands\Chat;

use App\ChatHistory;
use Swoole\WebSocket\Server;
use \Swoole\Table;

class ChatServer
{
    private $ws;


    private function setHandler()
    {
        $this->ws->on('open', function (Server $server, $request) {

            // 保存fd与用户之间的关系
            $userId = $request->get['userId'];
            $userFd = json_decode($server->table->get("userFd", 'value'), true) ?: [];
            $fdUser = json_decode($server->table->get("fdUser", 'value'), true) ?: [];
            $userFd[$userId][] = $request->fd;
            $fdUser[$request->fd] = $userId;
            $server->table->set("userFd", ['value' => json_encode($userFd)]);
            $server->table->set("fdUser", ['value' => json_encode($fdUser)]);
        });
        $this->ws->on('message', function (Server $server, $frame) {
            // 解析消息， 传输到对应fd上
            $data = json_decode($frame->data, true);
            $msg = $data['msg'];
            $userFd = json_decode($server->table->get("userFd", 'value'), true) ?: [];
            $fdUser = json_decode($server->table->get("fdUser", 'value'), true) ?: [];
            $sendUserId = $fdUser[$frame->fd];
            $receiveUserId = $data['userId'];
            $receiveFd = $userFd[$receiveUserId] ?? [];
            $chatHistory = app(ChatHistory::class);
            // 确定身份发送者和接受者的身份

            if (empty($receiveFd)) {
                // 不在线， 不发送
                return;
            }
            foreach ($receiveFd as $fd) {
                $server->push($fd, $msg);
            }
        });
        $this->ws->on('close', function (Server $server, $fd) {
            // 解除绑定
            $this->onClose($server, $fd);
        });
    }

    private function onClose($server, $fd)
    {

        $fdUser = json_decode($server->table->get("fdUser", 'value'), true) ?: [];
        $userId = $fdUser[$fd] ?? 0;
        // 删除对应关系
        unset($fdUser[$fd]);
        if (empty($userId)) {
            return;
        }
        $userFd = json_decode($server->table->get("userFd", 'value'), true) ?: [];

        for ($i = 0; $i < count($userFd[$userId] ?? []); $i++) {
            $mapFd = $userFd[$userId][$i] ?? 0;
            if ($mapFd == $fd) {
                unset($userFd[$userId][$i]);
            }
        }
        $server->table->set("userFd", ['value' => json_encode($userFd)]);
        $server->table->set("fdUser", ['value' => json_encode($fdUser)]);
    }

    public function listen()
    {
        $table = new Table(2048);
        $table->column('value', Table::TYPE_STRING, 2048);
        $table->create();
        $this->ws = new Server('127.0.0.1', '18000');
        $this->ws->table = $table;
        $this->setHandler();
        $this->ws->start();
    }
}