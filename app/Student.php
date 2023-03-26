<?php

namespace App;

use App\Http\Constant\Auth;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;


class Student extends Authenticatable
{
    use Notifiable, HasApiTokens;

    protected $table = "student";

    protected $fillable = ['username', 'password', 'school_id', 'name', 'age', 'grade', 'class'];
    protected $guarded = ['username', 'password'];


    public function school()
    {
        return $this->hasOne(School::class, "id", "school_id");
    }

    public function findForPassport($username)
    {
        return $this->where('username', '=', $username)->first();
    }


    public function isNormal()
    {
        return $this->status === 0;
    }

    public function toReturn(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'grade' => $this->grade,
            'class' => $this->class,
            'avatar' => $this->avatar ?? '',
            'role' => Auth::STUDENT_GUARD,
        ];
    }
}
