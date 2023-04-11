<?php

namespace App\Http\Controllers;

use App\Http\Constant\Auth;
use App\Http\Constant\Errcode;
use App\Http\Service\ChatService;
use App\Student;
use App\Teacher;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function getChatHistory(Request $request, ChatService $service)
    {
        $this->validate($request, [
            'userId' => 'required',
            'role' => 'required|in:student,teacher,admin'
        ]);
        $user = $request->user();
        $userId = $request->input('userId');
        $role = $request->input('role');
        $chatList = $service->getChatHistory($user, $role, $userId);
        $result = [];
        foreach ($chatList as $chat) {
            $result[] = [
                'type' => ($chat->sender_role == $user->role() && $chat->sender_id = $user->id) ? 'to' : 'from',
                'msg' => $chat->msg,
                'time' => $chat->created_at,
            ];
        }
        return $this->responseJson(Errcode::SUCCESS, $result);
    }

    public function getUnreadMessage(Request $request, ChatService $service)
    {
        $user = $request->user();
        $unreadList = $service->getUnReadMsgList($user);
        $result = [];
        foreach ($unreadList as $unreadMsg) {
            if ($unreadMsg->sender_role === Auth::STUDENT_GUARD) {
                $sender = Student::find($unreadMsg->sender_id)->toReturn();
            } elseif ($unreadMsg->sender_role === Auth::TEACHER_GUARD) {
                $sender = Teacher::find($unreadMsg->sender_id)->toReturn();
            } else {
                $sender = [
                    'id' => $unreadMsg->sender_id,
                    'role' => $unreadMsg->sender_role,
                    'name' => 'System Admin',
                ];
            }

            $result[] = [
                'num' => $unreadMsg->num,
                'sender' => $sender,
            ];
        }
        return $this->responseJson(Errcode::SUCCESS, $result);
    }

    public function readMessage(Request $request, ChatService $service)
    {
        $this->validate($request, [
            'senderRole' => 'required|in:teacher,student,admin',
            'senderId' => 'required',
        ]);
        $user = $request->user();
        $ok = $service->setMessageRead($user, $request->input('senderRole'), $request->input('senderId'));
        if (!$ok) {
            return $this->responseJson(Errcode::SERVER_ERROR);
        }
        return $this->responseJson(Errcode::SUCCESS);

    }

    public function sendLineMessage(Request $request, ChatService $chatService)
    {
        $this->validate($request, [
            'userId' => 'required',
            'role' => 'required|in:student,teacher',
            'msg' => 'required',
        ]);
        try {
            $chatService->sendLineMessage($request->input('role'), $request->input('userId'), $request->input('msg'));
            return $this->success();
        } catch (\Exception $e) {
            return $this->responseJson(Errcode::SERVER_ERROR, [], $e->getMessage());
        }
    }
}
