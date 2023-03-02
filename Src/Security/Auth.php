<?php

namespace Tet\Security;

use Tet\HTTP\ServerRequest;
use Tet\Security\JWT\Coder;
use Tet\Security\JWT\Token;


class TokenData
{
    public string $type;
    public $token;
    public $login;
    public $password;
}


class Auth
{
    private string $tokenSecret;

    public function check($callback = null, string $type = "Bearer"): bool
    {
        $tokenData = $this->getTokenData();
        if (!$tokenData) return false;
        if ($tokenData->type != $type) return false;
        if ($this->isBasic($tokenData)) return $this->proccessBasicToken($tokenData->token, $callback);
        if ($this->isBearer($tokenData)) return $this->proccessBearerToken($tokenData->token);
        return true;
    }

    public function setTokenSecret(string $secret = ""): bool
    {
        $this->tokenSecret = $secret;
        return true;
    }

    public function createToken(array $payload): string
    {
        $token = new Token(["alg" => "HS256", "typ" => "JWT"], $payload);
        $coder = new Coder($this->tokenSecret);
        return $coder->encode($token);
    }

    private function isBearer(object $tokenData): bool
    {
        return $tokenData->type == "Bearer";
    }

    private function isBasic(object $tokenData): bool
    {
        return $tokenData->type == "Basic";
    }

    function getTokenData(): ?TokenData
    {
        $authHeader = (new ServerRequest)->getHeaders()->get("Authorization");        
        if (!$authHeader) return null;

        $tmp = explode(" ", $authHeader);
        if (count($tmp) != 2) return null;
        if (!$tmp[0]) return null;
        if (!$tmp[1]) return null;


        $td = new TokenData;
        [$td->type, $td->token] = $tmp;
        if($td->type == "Basic") $td = $this->decodeBasicToken($td);        
        return $td;
    }

    private function proccessBearerToken(string $token): bool
    {
        $coder = new Coder($this->tokenSecret);
        if (!$coder->validate($token)) return false;
        $tokenData = $coder->decode($token);
        if ($tokenData->isNotBefore()) return false;
        if ($tokenData->isExpired()) return false;
        return true;
    }

    private function proccessBasicToken(string $token, $callback): bool
    {
        $tokenData = base64_decode($token);
        $tokenData = explode(":", $tokenData);
        if (count($tokenData) != 2) return false;
        $login = $tokenData[0];
        $password = $tokenData[1];

        if (!$callback($login, $password)) return false;

        return true;
    }

    function decodeBasicToken(TokenData $td):TokenData
    {
        $tmp = base64_decode($td->token);
        $tmp = explode(":", $tmp);
        if (count($tmp) != 2) return null;
        [$td->login, $td->password]  = $tmp;        
        return $td;
    }
}
