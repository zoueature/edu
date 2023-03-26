<?php
// App\Http\Service/FollowService
// author: zoueature
// eamil: zoueature@gmail.com
// ------------------------------------


namespace App\Http\Service;


use App\Student;
use App\StudentFollowTeacher;
use App\Teacher;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class FollowService extends Service
{
    /**
     * 关注老师
     * @param Student $student
     * @param Teacher $teacher
     * @return bool
     */
    public function followTeacher(Student $student, Teacher $teacher) :bool
    {
        $follow = $this->getStudentTeacherFollow($student->id, $teacher->id);
        if (empty($follow)) {
            $follow = new StudentFollowTeacher;
            $follow->student_id = $student->id;
            $follow->teacher_id = $teacher->id;
            $result = $follow->save();
        } else {
            $result = $follow->restore();
        }
        return $result;
    }

    /**
     * 取消关注老师
     * @param $studentUserId
     * @param $teacherUserId
     * @return bool
     */
    public function unfollowTeacher(Student $student, Teacher $teacher) :bool
    {
        $follow = $this->getStudentTeacherFollow($student->id, $teacher->id);
        if (empty($follow)) {
            return true;
        } else {
            try {
                return $follow->delete();
            } catch (\Exception $e) {
                Log::error("unfollow teacher error " . $e->getMessage(), [$teacher, $student]);
                return false;
            }
        }
    }

    private function getStudentTeacherFollow($studentUserId, $teacherUserId) :?StudentFollowTeacher
    {
        try {
            return  StudentFollowTeacher::withTrashed()->where('student_id', '=', $studentUserId)
                ->where('teacher_id', '=', $teacherUserId)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            Log::warning("follow relation not found " .$e->getMessage(), func_get_args());
            return  null;
        }
    }

    /**
     * 获取关注的学生列表
     * @param Teacher $teacher
     * @return array
     */
    public function getFollowedStudents(Teacher $teacher) :array
    {
        $follows = StudentFollowTeacher::where('teacher_id', $teacher->id)->get();
        $students = [];
        foreach ($follows as $follow) {
            $students[] = $follow->student;
        }
        return $students;
    }
}