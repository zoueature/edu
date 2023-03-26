<?php
// App\Http\Service/StudentService
// author: zoueature
// eamil: zoueature@gmail.com
// ------------------------------------


namespace App\Http\Service;


use App\School;
use App\Student;
use App\StudentFollowTeacher;
use App\Teacher;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class StudentService extends Service
{
    public function getFollowedInSet(Student $student, array $teacherId) :array
    {
        $followTeachers = StudentFollowTeacher::where('student_id', '=', $student->id)
            ->whereIn('teacher_id', $teacherId)
            ->get();
        if ($followTeachers->isEmpty()) {
            return [];
        }
        $result = [];
        foreach ($followTeachers as $followTeacher) {
            $result[$followTeacher->teacher_id] = true;
        }
        return $result;
    }
}