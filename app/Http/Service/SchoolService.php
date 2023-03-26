<?php
// App\Http\Service/SchoolService
// author: zoueature
// eamil: zoueature@gmail.com
// ------------------------------------


namespace App\Http\Service;


use App\Http\Constant\Role;
use App\School;
use App\SchoolApply;
use App\SchoolTeacher;
use App\Teacher;
use App\Http\Constant\School as SchoolConstant;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class SchoolService extends Service
{
    public function applySchool(Teacher $teacher, School $school) :bool
    {
        $apply = \app(SchoolApply::class);
        $apply->apply_teacher_id = $teacher->id;
        $apply->status = SchoolConstant::REVIEW_STATUS_WAIT_REVIEW;
        try {
            DB::transaction(function () use ($school, $apply){
                $school->save();
                $apply->school_id = $school->id;
                $apply->save();
            });
        } catch (\Exception $e) {
            Log::warning($e->getMessage(), [$teacher, $school]);
            return false;
        }
        return true;
    }

    public function getSchoolApplyList(Teacher $teacher)
    {
        $applies = SchoolApply::where("apply_teacher_id", '=', $teacher->id)->get();
        return $applies;
    }

    /**
     * @param Teacher $teacher
     * @return array
     */
    public function  getAdminSchoolList(Teacher $teacher, bool $filterForbidden = true) :array
    {
        $schoolTeachers = SchoolTeacher::where("teacher_id", '=', $teacher->id)
            ->where('role', '=', Role::SCHOOL_ROLE_ADMIN)
            ->get();
        $result = [];
        if ($schoolTeachers->isEmpty()) {
            return $result;
        }
        foreach ($schoolTeachers as $schoolTeacher) {
           $school = $schoolTeacher->school;
           if ($filterForbidden && $school->status === \App\Http\Constant\School::SCHOOL_STATUS_FORBIDDEN) {
               continue;
           }
           $result[] = $school;
        }
        return $result;
    }
}