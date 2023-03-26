<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SchoolApply extends Model
{
    protected $table = "school_apply";

    public function school()
    {
        return $this->hasOne(School::class, 'id', 'school_id');
    }
}
