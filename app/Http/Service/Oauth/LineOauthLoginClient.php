<?php

namespace App\Http\Service\Oauth;

use App\Http\Constant\Auth;
use App\OauthUser;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class LineOauthLoginClient implements OauthLoginClient
{
    const LINE_LOGIN_URL = 'https://access.line.me/oauth2/v2.1/authorize';
    const LINE_OAUTH = 'https://api.line.me/oauth2/v2.1/token';
    const LINE_PROFILE_URL = 'https://api.line.me/v2/profile';
    const REDIRECT_URL = 'http://edu.eayang.com/oauth/auth?type=line';


    /**
     * @return string
     */
    public function generateAuthURL() :string
    {
        $params = [
            'response_type' => 'code',
            'client_id' => env('LINE_CLIENT_ID'),
            'redirect_uri' => self::REDIRECT_URL,
            'state' => uniqid(),
            'scope' => 'profile',
        ];
        return self::LINE_LOGIN_URL . '?' . http_build_query($params);
    }

    /**
     * 第三方登录
     * @param $code
     * @return \Illuminate\Foundation\Application|mixed
     */
    public function oauthAuth($code)
    {
        try {
            $params = [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => self::REDIRECT_URL,
                'client_id' => env('LINE_CLIENT_ID'),
                'client_secret' => env('LINE_SECRET'),
            ];
            $client = new Client();
            $response = $client->post(self::LINE_OAUTH, [
                'form_params' => $params
            ]);
            $body = $response->getBody();
            $data = json_decode($body, true);
            $accessToken = $data['access_token'];
            $response = $client->get(self::LINE_PROFILE_URL, [
                'header' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ]
            ]);
            $profile = json_decode($response, true);
            return $profile['userId'];
        } catch (\Exception $e) {
            Log::error('login error ' . $e->getMessage());
            return 0;
        }

    }

    public function loginType() :string
    {
        return Auth::LOGIN_TYPE_LINE;
    }
}