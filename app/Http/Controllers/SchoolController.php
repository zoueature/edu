<?php

namespace App\Http\Controllers;

use App\Http\Constant\Errcode;
use App\Http\Service\SchoolService;
use App\Http\Service\StudentService;
use App\School;
use App\SchoolApply;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    /**
     * @var StudentService $studentSvc
     */
    private $studentSvc;

    /**
     * @var SchoolService $schoolSvc
     */
    private $schoolSvc;

    public function __construct(StudentService $studentService, SchoolService $schoolService)
    {
        $this->studentSvc = $studentService;
        $this->schoolSvc = $schoolService;
    }

    /**
     * 申请开通学校
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function applySchool(Request $request)
    {
        $this->validate($request, [
           'name' => 'required',
           'country' => 'required',
           'province' => 'required',
           'city' => 'required',
           'address' => 'required',
        ]);
        $teacher = $request->user();
        $school = app(School::class);
        $school->name = $request->input('name');
        $school->country = $request->input('country');
        $school->province = $request->input('province');
        $school->city = $request->input('city');
        $school->address = $request->input('address');
        $school->status = \App\Http\Constant\School::SCHOOL_STATUS_REVIEW;
        $ok = $this->schoolSvc->applySchool($teacher, $school);
        if (!$ok) {
            return $this->responseJson(Errcode::SERVER_ERROR);
        }
        return $this->success();
    }
}
