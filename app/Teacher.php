<?php

namespace App;

use App\Http\Constant\Auth;
use App\Http\Constant\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\Passport;


class Teacher extends Authenticatable
{
    use Notifiable, HasApiTokens;

    protected $table = "teacher";

    protected $fillable = ['email', 'password', 'name'];
    protected $guarded = ['email', 'password'];

    public function schools()
    {
        return $this->hasManyThrough(School::class, SchoolTeacher::class, 'teacher_id', 'id', 'id', 'school_id');
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

    public function bindOauthModel()
    {
        $model = app(OauthUserBindTeacher::class);
        $model->teacher_id = $this->id;
        return $model;
    }

    public function foreignKey(): string
    {
        return 'teacher_id';
    }

    public function createAccessToken($name)
    {
        return $this->createToken($name, ['teacher-api']);
    }

    public function role()
    {
        return Auth::TEACHER_GUARD;
    }

    public function checkFollowed(Teacher $teacher) :bool
    {
        return StudentFollowTeacher::where('student_id', '=', $this->id)
                ->where('teacher_id', '=', $teacher->id)
                ->count() > 0;
    }

    public function checkCanBind() :bool
    {
        return OauthUserBindTeacher::where('teacher_id', '=', $this->id)
                ->count() <= 0;
    }

    public function bindLineUser()
    {
        return $this->hasManyThrough(OauthUser::class, OauthUserBindTeacher::class, 'teacher_id', 'id', 'id', 'oauth_id');
    }
}
