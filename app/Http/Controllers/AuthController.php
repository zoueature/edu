<?php

namespace App\Http\Controllers;

use App\Exceptions\LoginFailException;
use App\Http\Constant\Auth;
use App\Http\Constant\Errcode;
use App\Http\Requests\LoginRequest;
use App\Http\Service\AuthService;
use App\Http\Services\LineOauthService;
use App\Student;
use App\Teacher;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class AuthController extends Controller
{


    public function register(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|min:3',
            'password' => 'required|min:6',
        ]);

        $user = Student::create([
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'school_id' => 2,
            'name' => '',
            'age' => 22,
            'grade' => 12,
            'class' => 12,
        ]);

        $token = $user->createToken('Edu System')->accessToken;

       $this->responseJson(Errcode::SUCCESS, ['token' => $token]);
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


        $token = $user->createToken(Auth::TOKEN_NAME)->accessToken;

        return $this->responseJson(Errcode::SUCCESS, ['token' => $token, 'user' => $user->toReturn()]);
    }


    /**
     * 登陆
     * @param Request $request
     * @param AuthService $svc
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request, AuthService $svc)
    {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
            'role' => 'required|in:teacher,student',
        ]);
        $user = $svc->checkLoginUser($request->input('username'), $request->input('password'), $request->input('role'));
        if (empty($user)) {
            return $this->responseJson(Errcode::SERVER_ERROR);
        }
        $token = $user->createToken(Auth::TOKEN_NAME)->accessToken;
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

}
