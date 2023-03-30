<?php

namespace App\Http\Service\Oauth;

use App\Http\Constant\Auth;

class OauthLoginClientFactor
{
    public static function getClient(string $loginType) :OauthLoginClient
    {
        switch ($loginType) {
            case Auth::LOGIN_TYPE_LINE:
                $client = new LineOauthLoginClient();
                break;
            default:
                throw new \Exception('login type not found');
        }
        return $client;
    }
}