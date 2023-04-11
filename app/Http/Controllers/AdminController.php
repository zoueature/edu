<?php

namespace App\Http\Controllers;

use App\Http\Constant\Errcode;
use App\Http\Service\AdminService;
use App\Http\Service\SchoolService;
use App\Http\Service\UserService;
use App\Student;
use Illuminate\Http\Request;

class AdminController extends Controller
{

    /**
     * @var AdminService $adminService;
     */
    private $adminService;

    /**
     * @var SchoolService $schoolSvc;
     */
    private $schoolSvc;

    /**
     * @var UserService $userSvc
     */
    private $userSvc;
    
    public function __construct(AdminService $adminService, SchoolService $schoolService, UserService $service)
    {
        $this->adminService = $adminService;    
        $this->schoolSvc = $schoolService;
        $this->userSvc = $service;

    }

    /**
     * inviteTeacher 邀请用户成为老师
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public  function inviteTeacher(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'schoolId' => 'required',
        ]);
        $teacher = $request->user();
        if (!$teacher->isAdminInSchool($request->input('schoolId'))) {
            return $this->responseJson(Errcode::TEACHER_NOT_ALLOW);
        }
        $email = $request->input('email');
        $teacher = $this->userSvc->getTeacherByEmail($email);
        if (!empty($teacher)) {
            return $this->responseJson(Errcode::BAD_REQUEST, [], '邮箱已被注册');
        }
        // 发送邮件
        $ok = $this->adminService->sendInviteEmail($email, $request->input('schoolId'));
        if (!$ok) {
            return $this->responseJson(Errcode::SERVER_ERROR, [], '邀请邮件发送失败');
        }
        return $this->success();
    }


    /**
     * 检查邀请码并创建老师
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public  function checkToCreateTeacher(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'code' => 'required',
            'schoolId' => 'required',
        ]);
        $email = $request->input('email');
        $code = $request->input('code');
        if (!$this->adminService->checkCodeValid($email, $code)) {
            return $this->responseJson(Errcode::TEACHER_CODE_NOT_VALID);
        }
        $schoolId = $request->input('schoolId');
        $ok = $this->adminService->createNewTeacher($email, $code, $schoolId);
        if (!$ok) {
            return $this->responseJson(Errcode::CREATE_NEW_TEACHER_ERROR);
        }
        return $this->success();
    }

    /**
     * createNewStudent 新建学生用户
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createNewStudent(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
            'schoolId' => 'required',
            'grade' => 'required',
            'class' => 'required',
            'name' => 'required',
        ]);
        // 管理员身份检查
        $teacher = $request->user();
        $schoolId = $request->input('schoolId');

        if (!$teacher->isAdminInSchool($schoolId)) {
            return $this->responseJson(Errcode::TEACHER_NOT_ALLOW);
        }
        $student = app(Student::class);
        $student->username = $request->input('username');
        $student->password = bcrypt($request->input('password'));
        $student->school_id = $request->input('schoolId');
        $student->grade = $request->input('grade');
        $student->class = $request->input('class');
        $student->name = $request->input('name');
        $student->save();
        return $this->responseJson(Errcode::SUCCESS, $student->toReturn());
    }

    /**
     * 学校申请列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSchoolApplyList(Request $request)
    {
        $teacher = $request->user();
        $applies = $this->schoolSvc->getSchoolApplyList($teacher);
        $result = [];
        foreach ($applies as $apply) {
            $request[] = [
                'id' => $apply->id,
                'status' => $apply->status,
                'school' => $apply->school,
            ];
        }
        return $this->responseJson(Errcode::SUCCESS, $applies);
    }
}
