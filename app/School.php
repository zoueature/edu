<?php

namespace App;

use App\Http\Constant\Auth;
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

    public function toReturn()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'country' => $this->country,
            'province' => $this->province,
            'city' => $this->city,
            'address' => $this->address,
        ];
    }
}
