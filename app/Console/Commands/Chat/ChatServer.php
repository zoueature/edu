<?php

namespace App\Console\Commands\Chat;

use App\ChatHistory;
use App\Http\Constant\Auth;
use App\Http\Service\AuthService;
use App\Student;
use App\Teacher;
use http\Env\Request;
use Swoole\WebSocket\Server;
use \Swoole\Table;

class ChatServer
{
    private $ws;

    private $auth;

    private $userId;

    public function __construct(AuthService $authService)
    {
        $this->auth = $authService;
    }

    private function getUserByToken($token)
    {
        foreach (Auth::SYSTEM_USER_GUARDS_SCOPES as $guard => $scope) {
            $user = $this->auth->checkUserByToken($token, $guard, $scope);
            if (!empty($user)) {
                return $user;
            }
        }
        return null;
    }

    public function getTalkUserInfo($senderUser, $talkUserId)
    {
        if ($senderUser->role() === Auth::STUDENT_GUARD) {
            return Teacher::find($talkUserId);
        } elseif ($senderUser->role() === Auth::TEACHER_GUARD) {
            return Student::find($talkUserId);
        }
        return null;
    }

    private function setHandler()
    {
        $this->ws->on('open', function (Server $server, $request) {
            $token = $request->get['token'];
            $user = $this->getUserByToken($token);
            if (empty($user)) {
                // 身份认证失败
                $server->close($request->fd);
                return;
            }
            $userId = $request->get['userId'] ?? 0;
            if (empty($userId) || empty($this->getTalkUserInfo($user, $userId))) {
                // 未找到对话的用户数据
                $server->close($request->fd);
                return;
            }
            if ($userId == $user->id) {
                // 禁止自己跟自己聊天
                $server->close($request->fd);
                return;
            }
            $this->userId = $userId;


            // 需求未明确需要检查关注关系

            // 保存fd与用户之间的关系
            $userFd = json_decode($server->table->get("userFd", 'value'), true) ?: [];
            $fdUser = json_decode($server->table->get("fdUser", 'value'), true) ?: [];

            $userFd[$user->id] = $request->fd;
            $fdUser[$request->fd] = $user->id;
            $server->table->set("userFd", ['value' => json_encode($userFd)]);
            $server->table->set("fdUser", ['value' => json_encode($fdUser)]);
            echo "{$user->id} - $userId";
        });

        $this->ws->on('message', function (Server $server, $frame) {
            // 解析消息， 传输到对应fd上
            $data = json_decode($frame->data, true);
            $event = $data['event'] ?? '';
            if ($event == 'hearbeat') {
                // 心跳检测
                return;
            }
            if ($event == 'chat') {
                $msg = $data['msg'];
                $userFd = json_decode($server->table->get("userFd", 'value'), true) ?: [];

                $receiveFd = $userFd[$this->userId] ?? 0;

                if (empty($receiveFd)) {
                    // 不在线， 不发送
                    return;

                }
                $server->push($receiveFd, $msg);
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
        unset($userFd[$userId]);

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