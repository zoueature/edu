<?php
// App\Http\Service/AuthService
// author: zoueature
// eamil: zoueature@gmail.com
// ------------------------------------


namespace App\Http\Service;


use App\Http\Constant\Role;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AuthService extends Service
{
    /**
     * The hasher implementation.
     *
     * @var \Illuminate\Contracts\Hashing\Hasher
     */
    protected $hasher;

    /**
     * Create a new repository instance.
     *
     * @param  \Illuminate\Contracts\Hashing\Hasher  $hasher
     * @return void
     */
    public function __construct(Hasher $hasher)
    {
        $this->hasher = $hasher;
    }

    public function checkLoginUser($identify, $password, $provider) :?User
    {
        $provider = config("auth.guards.$provider.provider");

        if (is_null($model = config('auth.providers.'.$provider.'.model'))) {
            Log::erroe("Unable to determine authentication model from configuration.");
            return null;
        }

        if (method_exists($model, 'findForPassport')) {
            $user = (new $model)->findForPassport($identify);
        } else {
            $user = (new $model)->where('email', $identify)->first();
        }


        if (! $user) {
            return null;
        } elseif (method_exists($user, 'validateForPassportPasswordGrant')) {
            if (! $user->validateForPassportPasswordGrant($password)) {
                return null;
            }
        } elseif (! $this->hasher->check($password, $user->getAuthPassword())) {
            return null;
        }
        if (!$user->isNormal()) {
            // 被封禁
            return null;
        }
        return $user;
    }
}