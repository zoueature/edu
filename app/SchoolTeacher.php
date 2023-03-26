<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SchoolTeacher extends Model
{
    //

    protected $table = 'teacher_school';

    public function school()
    {
        return $this->hasOne(School::class, 'id', 'school_id');
    }

}
