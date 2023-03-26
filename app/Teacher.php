<?php

namespace App;

use App\Http\Constant\Auth;
use App\Http\Constant\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;


class Teacher extends Authenticatable
{
    use Notifiable, HasApiTokens;

    protected $table = "teacher";

    protected $fillable = ['email', 'password', 'name'];
    protected $guarded = ['email', 'password'];

    public function schools() :HasMany
    {
        return $this->hasMany(School::class, "id", "school_id");
    }


    public function isAdminInSchool($schoolId): bool
    {

        return SchoolTeacher::where('teacher_id','=', $this->id)
            ->where('school_id', '=', $schoolId)
            ->where('role', '=', Role::SCHOOL_ROLE_ADMIN)
            ->count() > 0;
    }

    public function findForPassport($email)
    {
        return $this->where('email', '=', $email)->first();
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
            'avatar' => $this->avatar ?? '',
            'role' => Auth::TEACHER_GUARD,
            'isAdmin' => false,
        ];
    }
}
