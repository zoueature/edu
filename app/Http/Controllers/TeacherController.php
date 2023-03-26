<?php

namespace App\Http\Controllers;

use App\Http\Constant\Errcode;
use App\Http\Service\FollowService;
use App\Http\Service\SchoolService;
use App\Http\Service\StudentService;
use App\Http\Service\TeacherService;
use Illuminate\Http\Request;

class TeacherController extends Controller
{

    /**
     * @var TeacherService $teacherSvc;
     */
    private $teacherSvc;

    /**
     * @var FollowService $followSvc;
     */
    private $followSvc;

    /**
     * @var SchoolService $schoolSvc;
     */
    private $schoolSvc;


    public function __construct( TeacherService $tSvc, FollowService $followService, SchoolService $schoolService)
    {
        $this->teacherSvc = $tSvc;
        $this->followSvc = $followService;
        $this->schoolSvc = $schoolService;
    }


    /**
     * getFollowStudentList 获取关注的学生列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFollowStudentList(Request $request)
    {
        $teacher = $request->user();
        $students = $this->followSvc->getFollowedStudents($teacher);
        if (empty($students)) {
            return $this->success();
        }
        $result = [];
        foreach ($students as $student) {
            $result[] = [
                'id' => $student->id,
                'name' => $student->name,
                'grade' => $student->grade,
                'class' => $student->class,
                'age' => $student->age,
                'school' => $student->school,
            ];
        }
        return $this->responseJson(Errcode::SUCCESS, $result);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAdminSchoolList(Request $request)
    {
        $teacher = $request->user();
        $list = $this->schoolSvc->getAdminSchoolList($teacher);

        return $this->responseJson(Errcode::SUCCESS, $list);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAdminSchoolStudentList(Request $request)
    {
        $teacher = $request->user();
        $adminSchools = $this->schoolSvc->getAdminSchoolList($teacher);
        $students = [];
        foreach ($adminSchools as $adminSchool) {
            $schoolStudents = $adminSchool->students;
            if (empty($schoolStudents)) {
                continue;
            }
            foreach ($schoolStudents as $student) {
                $students[] = [
                    'student' => $student->toReturn(),
                    'school' => $adminSchool,
                ];
            }
        }
        return $this->responseJson(Errcode::SUCCESS, $students);
    }

}
