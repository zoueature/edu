<?php
// App\Http\Constant/ErrMsg
// author: zoueature
// eamil: zoueature@gmail.com
// ------------------------------------


namespace App\Http\Constant;


class ErrMsg
{
    const MSG = [
        Errcode::SUCCESS => "Success",

        //Student
        Errcode::STUDENT_NOT_FOUND => "Student not found",

        // Common
        Errcode::BAD_REQUEST => "Bad Request",
        Errcode::SERVER_ERROR => "Server error",
        Errcode::LOGIN_FAIL => "Not Login",

        // Teacher
        Errcode::TEACHER_NOT_FOUND => 'Teacher not found',
        Errcode::TEACHER_NOT_ALLOW => 'Not admin',
        Errcode::TEACHER_CODE_NOT_VALID => 'Invite code is invalid',
        Errcode::CREATE_NEW_TEACHER_ERROR => 'Create new teacher error',

        //School
        Errcode::SCHOOL_NOT_FOUND => 'School not found',

        //Follow
        Errcode::FOLLOW_TEACHER_FAIL => 'Follow teacher fail',
        Errcode::UNFOLLOW_TEACHER_FAIL => 'Unfollow teacher fail',
    ];
}