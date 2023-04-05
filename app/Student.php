<?php

namespace App;

use App\Http\Constant\Auth;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\Passport;


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

    public function bindOauthModel()
    {
        $model = app(OauthUserBindStudent::class);
        $model->student_id = $this->id;
        return $model;
    }

    public function foreignKey(): string
    {
        return 'student_id';
    }

    public function createAccessToken($name)
    {
        return $this->createToken($name, ['student-api']);
    }

    public function role()
    {
        return Auth::STUDENT_GUARD;
    }

    public function checkFollowed(Student $student) :bool
    {
        return StudentFollowTeacher::where('student_id', '=', $student->id)
            ->where('teacher_id', '=', $this->id)
            ->count() > 0;
    }

    public function followTeachers()
    {
        return $this->hasManyThrough(Teacher::class, StudentFollowTeacher::class, 'student_id', 'id', 'id', 'teacher_id');
    }

    public function checkCanBind() :bool
    {
        return OauthUserBindStudent::where('student_id', '=', $this->id)
                ->count() <= 0;
    }
}
