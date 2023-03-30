<?php
// App\Http\Constant/Auth
// author: zoueature
// eamil: zoueature@gmail.com
// ------------------------------------


namespace App\Http\Constant;


class Auth
{
    const TOKEN_NAME = 'Edu System';
    const TEACHER_GUARD = 'teacher';
    const TEACHER_SCOPE = 'teacher-api';
    const STUDENT_GUARD = 'student';
    const STUDENT_SCOPE = 'student-api';
    const OAUTH_GUARD = 'oauth';
    const OAUTH_SCOPE = 'oauth-api';
    const SYSTEM_USER_GUARDS = [self::TEACHER_GUARD, self::STUDENT_GUARD];
    const SYSTEM_USER_GUARDS_SCOPES = [self::TEACHER_GUARD => [self::TEACHER_SCOPE], self::STUDENT_GUARD => [self::STUDENT_SCOPE]];

    const USER_STATUS_NORMAL = 0;
    const USER_STATUS_FORBIDDEN = 1;

    const LOGIN_TYPE_LINE = 'line';
}