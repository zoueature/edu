<?php
// App\Http\Constant/Errcode
// author: zoueature
// eamil: zoueature@gmail.com
// ------------------------------------


namespace App\Http\Constant;


class Errcode
{
    const SUCCESS = 0;
    // Common
    const BAD_REQUEST = 100400;
    const SERVER_ERROR = 100500;
    const LOGIN_FAIL = 100501;

    // Student
    const STUDENT_NOT_FOUND = 100;

    // Teacher
    const TEACHER_NOT_FOUND = 200;
    const TEACHER_NOT_ALLOW = 201;
    const TEACHER_CODE_NOT_VALID = 202;
    const CREATE_NEW_TEACHER_ERROR = 203;

    //School
    const SCHOOL_NOT_FOUND = 300;

    //Follow
    const FOLLOW_TEACHER_FAIL = 400;
    const UNFOLLOW_TEACHER_FAIL = 401;
}