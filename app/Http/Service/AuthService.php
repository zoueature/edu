<?php
// App\Http\Service/AuthService
// author: zoueature
// eamil: zoueature@gmail.com
// ------------------------------------


namespace App\Http\Service;


use App\Http\Constant\Auth;
use App\Http\Constant\Role;
use App\Http\Service\Oauth\OauthLoginClientFactor;
use App\OauthUser;
use App\Student;
use App\SystemUser;
use App\Teacher;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use http\Env\Request;
use Illuminate\Auth\CreatesUserProviders;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Bridge\AccessTokenRepository;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Guards\TokenGuard;
use Laravel\Passport\Passport;
use Laravel\Passport\TokenRepository;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\ValidationData;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Illuminate\Contracts\Auth\Authenticatable;

class AuthService extends Service
{
    use CreatesUserProviders;
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


    /**
     * @param $loginType
     * @return string
     */
    public function generateAuthURL($loginType) :string
    {
        try {
            $client = OauthLoginClientFactor::getClient($loginType);
            return $client->generateAuthURL();
        } catch (\Exception $e) {
            Log::error('generate login url error ' . $e->getMessage());
            return '';
        }

    }


    /**
     * @param $loginType
     * @param $code
     * @return OauthUser|null
     * @throws \Exception
     */
    public function oauthLogin($loginType, $code) :?OauthUser
    {
        try {
            $client = OauthLoginClientFactor::getClient($loginType);
            $oauthUserId = $client->oauthAuth($code);
            if (empty($oauthUserId)) {
                Log::error('oauth login error : get user id fail' , func_get_args());
                return null;
            }
            $oauthUser = OauthUser::where('login_type', '=', $client->loginType())
                ->where('oauth_user_id', '=', $oauthUserId)
                ->first();
            if (empty($oauthUser)) {
                $oauthUser = app(OauthUser::class);
                $oauthUser->login_type = $client->loginType();
                $oauthUser->oauth_user_id = $oauthUserId;
                $oauthUser->save();
            }
        } catch (\Exception $e) {
            Log::error('oauth login error : ' . $e->getMessage(), func_get_args());
            return null;
        }
        return $oauthUser;
    }

    /**
     * 绑定用户
     * @param OauthUser $oauthUser
     * @param $systemUser
     * @return bool
     */
    public function bindUser(OauthUser $oauthUser,  $systemUser) :bool
    {
        // 检查被绑定的用户是否可以被绑定
        if (!$systemUser->checkCanBind()) {
            throw new \Exception('用户已被绑定');
        }
        // 是否第三方登录用户是否可绑定
        if (!$oauthUser->canBindUser($systemUser)) {
            throw new \Exception('不满足绑定条件');
        }

        // 检查是否已经绑定对应用户
        $bond = $oauthUser->checkBind($systemUser);
        if ($bond) {
            return true;
        }
        // 绑定
        $bindModel = $systemUser->bindOauthModel();
        $bindModel->oauth_id = $oauthUser->id;
        return $bindModel->save();
    }


    /**
     * @param OauthUser $oauthUser
     * @param $systemUser
     * @return bool
     * @throws \Exception
     */
    public function unbindUser(OauthUser $oauthUser,  $systemUser) :bool
    {
        // 解绑
        return $systemUser->bindOauthModel()
            ->where($systemUser->role().'_id', '=', $systemUser->id)
            ->where('oauth_id', '=', $oauthUser->id)
            ->delete();
    }

    /**
     * @param $role
     * @param $id
     * @return void
     */
    public function getUserInfo($role, $id) :?User
    {
        if ($role == Auth::STUDENT_GUARD) {
            return Student::find($id);
        } elseif ($role == Auth::TEACHER_GUARD) {
            return Teacher::find($id);
        }
        return null;
    }

    /**
     * 手工处理token检验， 用于websocket身份认真
     * @param $jwt
     * @param $guard
     * @param Scopes $
     * @return User|null
     * @throws OAuthServerException
     */
    public function checkUserByToken($jwt, $guard, $scopes) :?User
    {
        $publicKey = new CryptKey(
            'file://'.Passport::keyPath('oauth-public.key'),
            null,
            false);

        $token = (new Parser())->parse($jwt);
        if ($token->verify(new Sha256(), $publicKey->getKeyPath()) === false) {
            throw OAuthServerException::accessDenied('Access token could not be verified');
        }
        $data = new ValidationData();
        $data->setCurrentTime(time());
        if ($token->validate($data) === false) {
            throw OAuthServerException::accessDenied('Access token is invalid');
        }
        $app = Facade::getFacadeApplication();
        $accessTokenRepository = $app->make(AccessTokenRepository::class);
        if ($accessTokenRepository->isAccessTokenRevoked($token->getClaim('jti'))) {
            throw OAuthServerException::accessDenied('Access token has been revoked');
        }

        $provider = \Illuminate\Support\Facades\Auth::createUserProvider($guard);
        $user = $provider->retrieveById(
            $token->getClaim('sub')
        );
        if (! $user) {
            return null;
        }
        $tokens = $app->make(TokenRepository::class);
        $accessToken = $tokens->find(
            $token->getClaim('jti')
        );
        $clientId = $token->getClaim('aud');
        $clients = $app->make(ClientRepository::class);
        if ($clients->revoked($clientId)) {
            return null;
        }
        $tokenScopes = $token->getClaim('scopes');
        $tokenScopesMap = [];
        foreach ($tokenScopes as $tokenScope) {
            $tokenScopesMap[$tokenScope] = true;
        }
        foreach ($scopes as $scope) {
            if (!isset($tokenScopesMap[$scope])) {
                return null;
            }
        }
        return $accessToken ? $user->withAccessToken($accessToken) : null;
    }
}