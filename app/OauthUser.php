<?php

namespace App;

use App\Http\Constant\Auth;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class OauthUser extends Authenticatable
{
    use Notifiable, HasApiTokens;

    protected $table = 'oauth_user';

    public function relatedStudent()
    {
        return $this->hasManyThrough(Student::class, OauthUserBindStudent::class, 'oauth_id', 'id', 'id', 'student_id');
    }

    public function relatedTeacher()
    {
        return $this->hasManyThrough(Teacher::class, OauthUserBindTeacher::class, 'oauth_id', 'id', 'id', 'teacher_id');
    }

    public function checkBind($user): bool
    {
        return $user->bindOauthModel()->where('oauth_id', '=', $this->id)
                ->where($user->foreignKey(), '=', $user->id)
                ->count() > 0;
    }


    public function toReturn(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name ?? '',
            'role' => Auth::OAUTH_GUARD,
        ];
    }

    public function findForPassport($oauthUserId)
    {
        return $this->where('oauth_user_id', '=', $oauthUserId)->first();
    }

    public function createAccessToken($name)
    {
        return $this->createToken($name, [Auth::OAUTH_SCOPE]);
    }
}
