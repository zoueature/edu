<?php

namespace App\Http\Service;

use App\ChatHistory;
use App\Http\Constant\Common;
use App\Student;
use App\Teacher;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\DB;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

class ChatService extends Service
{

    /**
     * @var UserService $userService
     */
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    /**
     * 获取聊天记录
     * @param $user
     * @param $talkRole
     * @param $talkId
     * @return mixed
     */
    public function getChatHistory($user, $talkRole, $talkId)
    {
        return ChatHistory::where(function ($query) use ($user, $talkRole, $talkId) {
            $query->where('sender_role', '=', $user->role())
                ->where('sender_id', '=', $user->id)
                ->where('receiver_role', '=', $talkRole)
                ->where('receiver_id', '=', $talkId);
        })->orWhere(function ($query) use ($user, $talkRole, $talkId) {
            $query->where('sender_role', '=', $talkRole)
                ->where('sender_id', '=', $talkId)
                ->where('receiver_role', '=', $user->role())
                ->where('receiver_id', '=', $user->id);
        })->get();
    }

    /**
     * @param $user
     * @return mixed
     */
    public function getUnReadMsgList($user)
    {
        return ChatHistory::select(DB::raw('sender_role, sender_id, count(*) as num'))
            ->where('receiver_role', '=', $user->role())
            ->where('receiver_id', '=', $user->id)
            ->where('is_read', '=', Common::FALSE)
            ->groupBy('sender_role', 'sender_id')
            ->get();
    }

    public function setMessageRead($user, $senderRole, $senderId)
    {
        return ChatHistory::where('receiver_role', '=', $user->role())
            ->where('receiver_id', '=', $user->id)
            ->where('sender_role', '=', $senderRole)
            ->where('sender_id', '=', $senderId)
            ->where('is_read', '=', Common::FALSE)
            ->update(['is_read' => Common::TRUE]);
    }

    /**
     * 发送line消息
     * @param $role
     * @param $userId
     * @param $message
     * @return bool
     * @throws \Exception
     */
    public function sendLineMessage($role, $userId, $message)
    {
        $user = $this->userService->getUserInfo($role, $userId);
        if (empty($user)) {
            throw new \Exception('未找到用户信息');
        }
        $lineUsers = $user->bindLineUser;
        if (empty($lineUsers)) {
            throw new \Exception('未绑定Line用户');
        }
        $lineUser = $lineUsers->first();
        $httpClient = new CurlHTTPClient(env('LINE_MSG_ACCESS_TOKEN'));
        $bot = new LINEBot($httpClient, ['channelSecret' => env('LINE_MSG_CHANNEL_SECRET')]);
        $msgBuilder = new LINEBot\MessageBuilder\TextMessageBuilder($message);
        $response = $bot->pushMessage($lineUser->oauth_user_id, $msgBuilder);
        if (!$response->isSucceeded()) {
            throw new \Exception($response->getHTTPStatus() . ' ' . $response->getRawBody());
        }
    }
}