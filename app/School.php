<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $table = "school";

    public function teachers()
    {
        return $this->hasManyThrough(Teacher::class, SchoolTeacher::class, 'school_id', 'id', 'id', 'teacher_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'school_id', 'id');

    }
}
