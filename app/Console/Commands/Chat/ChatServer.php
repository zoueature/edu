<?php

namespace App\Console\Commands\Chat;

use App\ChatHistory;
use App\Http\Constant\Auth;
use App\Http\Constant\Common;
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

    private $talUserId;

    private $talkRole;

    private $connectUser;

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
            $role = $request->get['role'] ?? '';
            if (empty($role)) {
                $server->close($request->fd);
                return;
            }
            if ($userId == $user->id && $user->role() == $role) {
                // 禁止自己跟自己聊天
                $server->close($request->fd);
                return;
            }
            $this->talUserId = $userId;
            $this->talkRole = $role;
            $this->connectUser = $user;


            // 需求未明确需要检查关注关系

            // 保存fd与用户之间的关系
            $this->addFd($server, $user->role(), $user->id, $request->fd);
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
                $chatHistory = app(ChatHistory::class);
                $chatHistory->sender_role = $this->connectUser->role();
                $chatHistory->sender_id = $this->connectUser->id;
                $chatHistory->receiver_role = $this->connectUser->role() === Auth::TEACHER_GUARD ? Auth::STUDENT_GUARD : Auth::TEACHER_GUARD;
                $chatHistory->receiver_id = $this->talUserId;
                $chatHistory->msg = $msg;

                $fd = $this->getFD($server, $this->talUserId, $this->talkRole);

                $chatHistory->is_read = empty($fd) ? Common::FALSE : Common::TRUE;
                $chatHistory->save();
                if (empty($fd)) {
                    // 不在线， 不发送
                    return;

                }
                $server->push($fd, $msg);
            }
        });
        $this->ws->on('close', function (Server $server, $fd) {
            // 解除绑定
            $this->removeFd($server, $fd);
        });
    }

    private function getFD($server, $userId, $role)
    {
        $userFd = $this->getUserFD($server);
        return $userFd["$role-$userId"] ?? 0;
    }

    private function getUserFD($server)
    {
        return json_decode($server->table->get("userFd", 'value'), true) ?: [];
    }

    private function getFDUser($server)
    {
        return json_decode($server->table->get("fdUser", 'value'), true) ?: [];
    }

    private function addFd($server, $role, $userId, $fd)
    {
        $userFd = $this->getUserFD($server);
        $userFd["$role-$userId"] = $fd;
        $fdUser = $this->getFDUser($server);
        $fdUser[$fd] = "$role-$userId";
        $server->table->set("userFd", ['value' => json_encode($userFd)]);
        $server->table->set("fdUser", ['value' => json_encode($fdUser)]);
    }

    private function removeFd($server, $fd)
    {
        $fdUser = $this->getFDUser($server);
        $user = $fdUser[$fd] ?? '';
        if (!empty($user)) {
            $userFd = $this->getUserFD($server);
            unset($userFd[$user]);
            $server->table->set("userFd", ['value' => json_encode($userFd)]);
        }
        unset($fdUser[$fd]);
        $server->table->set("fdUser", ['value' => json_encode($fdUser)]);
    }

    public function listen()
    {
        $table = new Table(2048);
        $table->column('value', Table::TYPE_STRING, 2048);
        $table->create();
        $this->ws = new Server('0.0.0.0', '18000');
        $this->ws->table = $table;
        $this->setHandler();
        $this->ws->start();
    }
}