<?php

namespace App\Http\Controllers;

use App\Http\Constant\Auth;
use App\Http\Constant\Errcode;
use App\Http\Service\AuthService;
use App\Student;
use App\Teacher;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;

class AuthController extends Controller
{
    /**
     * @var AuthService $svc
     */
    private $svc;


    public function __construct(AuthService $authService)
    {
        $this->svc = $authService;
    }

    /**
     * 教师注册
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function teacherRegister(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|min:6',
            'name' => 'required'
        ]);

        $data = [
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
            'name' => $request->input('name'),
        ];
        try {
            $user = Teacher::create($data);
        } catch (\Exception $e) {
            return $this->responseJson(Errcode::SERVER_ERROR, [], $e->getMessage());
        }


        $token = $user->createAccessToken(Auth::TOKEN_NAME)->accessToken;

        return $this->responseJson(Errcode::SUCCESS, ['token' => $token, 'user' => $user->toReturn()]);
    }


    /**
     * 登陆
     * @param Request $request
     * @param AuthService $svc
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
            'role' => 'required|in:teacher,student',
        ]);
        $user = $this->svc->checkLoginUser($request->input('username'), $request->input('password'), $request->input('role'));
        if (empty($user)) {
            return $this->responseJson(Errcode::SERVER_ERROR);
        }
        $token = $user->createAccessToken(Auth::TOKEN_NAME)->accessToken;
        return $this->responseJson(Errcode::SUCCESS, ['token' => $token, 'user' => $user->toReturn()]);
    }


    /**
     * logout 退出
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return $this->success();
    }

    /**
     * 获取用户登录信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userInfo(Request $request)
    {
        $user = $request->user();
        return $this->responseJson(Errcode::SUCCESS, ['user' => $user->toReturn()]);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateOauthLoginURL(Request $request)
    {
        $this->validate($request, [
           'loginType' => 'required|in:line'
        ]);
        return $this->responseJson(
            Errcode::SUCCESS,
            [
                'url' => $this->svc->generateAuthURL($request->input('loginType'))
            ]
        );
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function oauthAuth(Request $request)
    {
        $this->validate($request, [
            'loginType' => 'required|in:line',
            'code' => 'required'
        ]);
        $oauthUser = $this->svc->oauthLogin(
            $request->input('loginType'),
            $request->input('code')
        );
        if (empty($oauthUser)) {
            return $this->responseJson(Errcode::SERVER_ERROR);
        }
        $token = $oauthUser->createAccessToken(Auth::TOKEN_NAME)->accessToken;
        return $this->responseJson(Errcode::SUCCESS, ['token' => $token, 'user' => $oauthUser->toReturn()]);
    }


    /**
     * 绑定系统用户和第卅方登录用户
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function bindUser(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
            'role' => 'required|in:teacher,student',
        ]);
        $oauthUser = $request->user();
        $user = $this->svc->checkLoginUser($request->input('username'), $request->input('password'), $request->input('role'));
        if (empty($user)) {
            return $this->responseJson(Errcode::LOGIN_FAIL);
        }
        $ok = $this->svc->bindUser($oauthUser, $user);
        if (!$ok) {
            return $this->responseJson(Errcode::SERVER_ERROR);
        }
        return $this->success();
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBindUserList(Request $request)
    {
        $oauthUser = $request->user();
        $relatedStudents = $oauthUser->relatedStudent ?: [];
        $students = [];
        foreach ($relatedStudents as $relatedStudent) {
            $students[] = $relatedStudent->toReturn();
        }
        $relatedTeachers = $oauthUser->relatedTeacher ?: [];
        $teachers = [];
        foreach ($relatedTeachers as $relatedTeacher) {
            $teachers[] = $relatedTeacher->toReturn();
        }
        return $this->responseJson(
            Errcode::SUCCESS,
            [
                'student' => $students,
                'teacher' => $teachers,
            ]
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function switchToUser(Request $request)
    {
        $this->validate($request, [
            'userId' => 'required',
            'role' => 'required|in:student,teacher'
        ]);
        $oauthUser = $request->user();
        $user = $this->svc->getUserInfo($request->input('role'), $request->input('userId'));
        if (empty($user)) {
            return $this->responseJson(Errcode::SERVER_ERROR);
        }
        if (!$oauthUser->checkBind($user)) {
            return $this->responseJson(Errcode::SERVER_ERROR);
        }

        return $this->responseJson(Errcode::SUCCESS, [
            'token' => $user->createAccessToken(Auth::TOKEN_NAME)->accessToken,
            'user' => $user->toReturn(),
        ]);
    }


    public function getInfoByToken(Request $request)
    {
        $token = $request->input('token');
        return $this->responseJson(Errcode::SUCCESS, $this->svc->checkUserByToken($token, 'student'));
    }

}
