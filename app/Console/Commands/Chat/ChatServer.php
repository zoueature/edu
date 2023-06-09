<?php

namespace App\Console\Commands\Chat;

use App\ChatHistory;
use App\Http\Constant\Auth;
use App\Http\Constant\Common;
use App\Http\Service\AuthService;
use App\Student;
use App\Teacher;
use http\Env\Request;
use Illuminate\Support\Facades\Log;
use Swoole\WebSocket\Server;
use \Swoole\Table;

class ChatServer
{
    private $ws;

    private $auth;

    private $connectUser;

    public function __construct(AuthService $authService)
    {
        $this->auth = $authService;
    }

    private function getUserByToken($token)
    {
        try {
            foreach (Auth::SYSTEM_USER_GUARDS_SCOPES as $guard => $scope) {
                $user = $this->auth->checkUserByToken($token, $guard, $scope);
                if (!empty($user)) {
                    return $user;
                }
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
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
//            $userId = $request->get['userId'] ?? 0;
//            if (empty($userId) || empty($this->getTalkUserInfo($user, $userId))) {
//                // 未找到对话的用户数据
//                $server->close($request->fd);
//                return;
//            }
//            $role = $request->get['role'] ?? '';
//            if (empty($role)) {
//                $server->close($request->fd);
//                return;
//            }
//            if ($userId == $user->id && $user->role() == $role) {
//                // 禁止自己跟自己聊天
//                $server->close($request->fd);
//                return;
//            }
            $this->connectUser = $user;


            // 需求未明确需要检查关注关系

            // 保存fd与用户之间的关系
            $this->addFd($server, $user->role(), $user->id, $request->fd);
        });

        $this->ws->on('message', function (Server $server, $frame) {
            // 解析消息， 传输到对应fd上
            $data = json_decode($frame->data, true);
            $event = $data['event'] ?? '';
            if ($event == 'heartbeat') {
                // 心跳检测
                return;
            }
            $talkUserId = $data['userId'] ?? 0;
            $talkUserRole = $data['role'] ?? '';
            switch ($event) {
                case 'in':
                    $this->joinRoom($server, $talkUserRole, $talkUserId, $frame->fd);
                    break;
                case 'out':
                    $this->quitRoom($server, $frame->fd);
                    break;
                case 'chat':
                    $msg = $data['msg'];
                    if (empty($talkUserId) || empty($talkUserRole)) {
                        //
                        return;
                    }
            	    $senderUserId = $data['senderId'] ?? 0;
            	    $senderRole = $data['senderRole'] ?? '';
                    $roomFd = $this->getUserRoomFd($server, $senderRole, $senderUserId);
                    $chatHistory = app(ChatHistory::class);
                    $chatHistory->sender_role = $senderRole;
                    $chatHistory->sender_id = $senderUserId;
                    $chatHistory->receiver_role = $talkUserRole;
                    $chatHistory->receiver_id = $talkUserId;
                    $chatHistory->msg = $msg;
                    $chatHistory->is_read = Common::FALSE;
                    $fds = $this->getFD($server, $talkUserId, $talkUserRole);
                    foreach ($fds as $fd) {
                        if (in_array($fd, $roomFd)) {
                            $chatHistory->is_read = Common::TRUE;
                        }
                        $server->push($fd, json_encode([
                            'msg' => $msg,
                            'sender' => [
                                 'id' => $senderUserId,
				 'role' => $senderRole,
				 'name' => $data['senderName'] ?? '用户',
			    ],
                            'readed' => $chatHistory->is_read,
                        ]));
                    }
                    $chatHistory->save();
                    break;
                case 'heartbeat':
                default:
            }
        });
        $this->ws->on('close', function (Server $server, $fd) {
            // 解除绑定
            $this->removeFd($server, $fd);
            $this->quitRoom($server, $fd);
        });
    }

    private function getFD($server, $userId, $role)
    {
        $userFd = $this->getUserFD($server);
        return $userFd["$role-$userId"] ?? [];
    }

    private function getUserFD($server)
    {
        return json_decode($server->table->get("userFd", 'value'), true) ?: [];
    }

    private function getFDUser($server)
    {
        return json_decode($server->table->get("fdUser", 'value'), true) ?: [];
    }

    private function getRoomFd($server)
    {
        return json_decode($server->table->get("roomFd", 'value'), true) ?: [];
    }

    private function getFdRoom($server)
    {
        return json_decode($server->table->get("fdRoom", 'value'), true) ?: [];
    }

    private function getUserRoomFd($server, $role, $userId)
    {
        $old = $this->getRoomFd($server);
        $id = "$role-$userId";
        return $old[$id] ?? [];
    }

    // 在聊天界面的fd， 直接发送消息到界面
    private function joinRoom($server, $role, $userId, $fd)
    {
        $old = $this->getRoomFd($server);
        $id = "$role-$userId";
        $old[$id][] = $fd;
        $fdRoom = $this->getFdRoom($server);
        $fdRoom[$fd] = $id;
        $server->table->set('roomFd', ['value' => json_encode($old)]);
        $server->table->set('fdRoom', ['value' => json_encode($fdRoom)]);
    }

    private function quitRoom($server, $fd)
    {
        $fdRoom = $this->getFdRoom($server);
        $id = $fdRoom[$fd] ?? '';
        if (empty($id)) {
            return;
        }
        $old = $this->getRoomFd($server);
        if (empty($old[$id] ?? [])) {
            return;
        }
        foreach ($old[$id] as $index => $val) {
            if ($val == $fd) {
                unset($old[$id][$index]);
            }
        }
        $server->table->set('roomFd', ['value' => json_encode($old)]);
        unset($fdRoom[$fd]);
        $server->table->set('fdRoom', ['value' => json_encode($fdRoom)]);

    }

    private function addFd($server, $role, $userId, $fd)
    {
        $userFd = $this->getUserFD($server);
        $userFds = $userFd["$role-$userId"] ?? [];
        $userFds[] = $fd;
	$userFd["$role-$userId"] = $userFds;
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
            $userFds = $userFd[$user] ?? [];
            if (!empty($userFds)) {
                foreach ($userFd[$user] as $index => $storeFd) {
                    if ($fd == $storeFd) {
                        unset($userFd[$user][$index]);
                    }
                }
            }
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
