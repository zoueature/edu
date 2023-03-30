<?php

namespace App\Http\Controllers;

use App\Http\Constant\Errcode;
use App\Http\Service\FollowService;
use App\Http\Service\SchoolService;
use App\Http\Service\StudentService;
use App\Http\Service\TeacherService;
use App\Student;
use App\Teacher;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * @var StudentService $studentSvc;
     */
    private $studentSvc;

    /**
     * @var TeacherService $teacherSvc;
     */
    private $teacherSvc;

    /**
     * @var FollowService $followSvc;
     */
    private $followSvc;

    /**
     * @var SchoolService $schoolSvc
     */
    private $schoolSvc;

    public function __construct(StudentService $service, TeacherService $tSvc, FollowService $followService, SchoolService $schoolService)
    {
        $this->studentSvc = $service;
        $this->teacherSvc = $tSvc;
        $this->followSvc = $followService;
        $this->schoolSvc = $schoolService;
    }

    /**
     * followTeacher 关注老师
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function followTeacher(Request $request)
    {
        $this->validate($request, [
           "teacherId" => "required"
        ]);
        $student = $request->user();
        $teacherId = $request->input('teacherId');
        $teacher = $this->teacherSvc->getTeacher($teacherId);
        if (empty($teacher)) {
            return $this->responseJson(Errcode::TEACHER_NOT_FOUND);
        }
        $ok = $this->followSvc->followTeacher($student, $teacher);
        if (!$ok) {
            return $this->responseJson(Errcode::FOLLOW_TEACHER_FAIL);
        }
        return $this->success();
    }


    /**
     * unfollowTeacher 取消关注老师
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unfollowTeacher(Request $request)
    {
        $this->validate($request, [
            "teacherId" => "required"
        ]);
        $student = $request->user();
        $teacherId = $request->input('teacherId');
        $teacher = $this->teacherSvc->getTeacher($teacherId);
        if (empty($teacher)) {
            return $this->responseJson(Errcode::TEACHER_NOT_FOUND);
        }
        $ok = $this->followSvc->unfollowTeacher($student, $teacher);
        if (!$ok) {
            return $this->responseJson(Errcode::UNFOLLOW_TEACHER_FAIL);
        }
        return $this->success();
    }

    /**
     * getSameSchoolTeachers 获取学校教师列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSameSchoolTeachers(Request $request)
    {
        $student = $request->user();
        $school = $student->school;

        $teachers = $school->teachers;
        if (empty($teachers)) {
            return $this->success();
        }
        $teacherIds = [];
        foreach ($teachers as $teacher) {
            $teacherIds[] = $teacher->id;
        }
        $followTeacherMap = $this->studentSvc->getFollowedInSet($student, $teacherIds);
        $result = [];
        foreach ($teachers as $teacher) {
            $tmp = $teacher->toReturn();
            $tmp['school'] = $school->name;
            $tmp['followed'] = isset($followTeacherMap[$teacher->id]);
            $result[] = $tmp;
        }
        return $this->responseJson(Errcode::SUCCESS, $result);
    }

    /**
     * 关注的教师列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function followTeacherList(Request $request)
    {
        $student = $request->user();

        $teachers = $student->followTeachers;
        $result = [];
        foreach ($teachers as $teacher) {
            $tmp = $teacher->toReturn();
            $tmp['followed'] = true;
            $result[] = $tmp;
        }
        return $this->responseJson(Errcode::SUCCESS, $result);
    }
}
