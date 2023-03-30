<?php

namespace App\Http\Service\Oauth;

use App\OauthUser;

interface OauthLoginClient
{
    public function generateAuthURL() :string;
    public function oauthAuth($code);
    public function loginType() :string;
}