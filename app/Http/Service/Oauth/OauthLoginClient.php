<?php

namespace App\Http\Service\Oauth;

interface OauthLoginClient
{
    public function generateAuthURL() :string;
    public function oauthAuth($code);
    public function loginType() :string;


}