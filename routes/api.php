<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('login', 'AuthController@login');
Route::post('register', 'AuthController@teacherRegister');
Route::get('/oauth/login', 'AuthController@generateOauthLoginURL');
Route::get('/oauth/auth', 'AuthController@oauthAuth');
Route::get('/test', 'AuthController@getInfoByToken' );


Route::group(['middleware' => 'auth:teacher'], function () {
});


// Common user route
Route::group(['middleware' => 'auth:student,teacher'], function () {
    Route::get('/user/info', 'AuthController@userInfo');
    Route::post('/logout', 'AuthController@logout');
});

Route::get('/teacher/join', 'AdminController@checkToCreateTeacher');
// Teacher route
Route::group([
    'prefix' => 'teacher',
    'middleware' => ['auth:teacher', 'scope:teacher-api'],
], function () {
    Route::post('/school/apply', 'SchoolController@applySchool');
    Route::get('/school/apply', 'AdminController@getSchoolApplyList');
    Route::get('/follower', 'TeacherController@getFollowStudentList');
    Route::get('/admin/school', 'TeacherController@getAdminSchoolList');
    Route::get('/admin/student', 'TeacherController@getAdminSchoolStudentList');
    Route::get('/student', 'TeacherController@getSameSchoolStudentList');
    Route::post('/student', 'AdminController@createNewStudent');
    Route::post('/invite', 'AdminController@inviteTeacher');
    Route::get('/chat/history', 'ChatController@getChatHistory');
    Route::get('/unread/message', 'ChatController@getUnreadMessage');
    Route::post('/read/message', 'ChatController@readMessage');

});


// Student route
Route::group([
    'prefix' => 'student',
    'middleware' => ['auth:student', 'scope:student-api'],
], function () {
    Route::get('/teacher', 'StudentController@getSameSchoolTeachers');
    Route::post('/teacher/follow', 'StudentController@followTeacher');
    Route::post('/teacher/unfollow', 'StudentController@unfollowTeacher');
    Route::get('/follow/teacher', 'StudentController@followTeacherList');
    Route::get('/chat/history', 'ChatController@getChatHistory');
    Route::get('/unread/message', 'ChatController@getUnreadMessage');
    Route::post('/read/message', 'ChatController@readMessage');
});


// Oauth user route
Route::group([
    'prefix' => 'oauth',
    'middleware' => ['auth:oauth', 'scope:oauth-api'],
], function () {
    Route::post('/bind/user', 'AuthController@bindUser');
    Route::get('/bind/user', 'AuthController@getBindUserList');
    Route::post('/user/switch', 'AuthController@switchToUser');
});