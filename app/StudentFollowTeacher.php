<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentFollowTeacher extends Model
{
    use SoftDeletes;

    protected $table = "student_follow_teacher";

    protected $dates = ['deleted_at'];

    public function teacher()
    {
        return $this->hasOne(Teacher::class, 'id', 'teacher_id');
    }

    public function student()
    {
        return $this->hasOne(Student::class, 'id', 'student_id');

    }

}
