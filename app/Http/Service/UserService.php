<?php
// App\Http\Service/UserService
// author: zoueature
// eamil: zoueature@gmail.com
// ------------------------------------


namespace App\Http\Service;


use App\Http\Constant\Auth;
use App\OauthUser;
use App\Student;
use App\Teacher;

class UserService extends Service
{
    public function getUserInfo($role, $userId)
    {
        switch ($role) {
            case Auth::TEACHER_GUARD:
                $user = Teacher::find($userId);
                break;
            case Auth::STUDENT_GUARD:
                $user = Student::find($userId);
                break;
            case Auth::OAUTH_GUARD:
                $user = OauthUser::find($userId);
                break;
            default:
                return null;
        }
        return $user;
    }

    public function getTeacherByEmail($email) :?Teacher
    {
        return Teacher::where('email', '=', $email)->first();
    }
}