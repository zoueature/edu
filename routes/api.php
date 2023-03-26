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


// Common user route
Route::group(['middleware' => 'auth:student,teacher'], function () {
    Route::get('/user/info', 'AuthController@userInfo');
    Route::post('/logout', 'AuthController@logout');
});

// Teacher route
Route::group([
    'prefix' => 'teacher',
    'middleware' => 'auth:teacher',
], function () {
    Route::post('/school/apply', 'SchoolController@applySchool');
    Route::get('/school/apply', 'AdminController@getSchoolApplyList');
    Route::get('/follower', 'TeacherController@getFollowStudentList');
    Route::get('/admin/school', 'TeacherController@getAdminSchoolList');
    Route::get('/admin/student', 'TeacherController@getAdminSchoolStudentList');
    Route::post('/student', 'AdminController@createNewStudent');
});


// Student route
Route::group([
    'prefix' => 'student',
    'middleware' => 'auth:student',
], function () {
    Route::get('/teacher', 'StudentController@getSameSchoolTeachers');
    Route::post('/teacher/follow', 'StudentController@followTeacher');
    Route::post('/teacher/unfollow', 'StudentController@unfollowTeacher');
});