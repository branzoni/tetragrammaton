<?php

namespace Tet\Security;

use Tet\HTTP\ServerRequest;
use Tet\Security\JWT\Coder;
use Tet\Security\JWT\Token;

class Auth
{
    private static string $tokenSecret;

    public static function check($callback = null, string $type = "Bearer"): bool
    {
        $tokenData = self::getTokenData();
        if (!$tokenData) return false;
        if ($tokenData->type != $type) return false;
        if (self::isBasic($tokenData)) return self::proccessBasicToken($tokenData->token, $callback);
        if (self::isBearer($tokenData)) return self::proccessBearerToken($tokenData->token);
        return true;
    }

    public static function setTokenSecret(string $secret = ""): bool
    {
        self::$tokenSecret = $secret;
        return true;
    }

    public static function createBearerToken(array $payload): string
    {
        $token = new Token(["alg" => "HS256", "typ" => "JWT"], $payload);
        $coder = new Coder(self::$tokenSecret);
        return $coder->encode($token);
    }

	public static function createBasicToken($login, $password): string
	{
		return "Basic " . base64_encode("{$login}:{$password}");
	}

    private static function isBearer(object $tokenData): bool
    {
        return $tokenData->type == "Bearer";
    }

    private static function isBasic(object $tokenData): bool
    {
        return $tokenData->type == "Basic";
    }

    static function getTokenData(): ?TokenData
    {
        $authHeader = (new ServerRequest)->getHeaders()->get("Authorization");
        $tmp = explode(" ", $authHeader);
        if (
			count($tmp) != 2 ||
			!$tmp[0] ||
			!$tmp[1]
		) throw new \Exception("Unknown token format", 400);


        $td = new TokenData;
        [$td->type, $td->token] = $tmp;


        if ($td->type == "Basic") return self::decodeBasicToken($td);
        if ($td->type == "Bearer") return self::decodeBearerToken($td);
		throw new \Exception("Unknown token type", 400);
    }

    static function proccessBearerToken(string $token): bool
    {
        $coder = new Coder(self::$tokenSecret);
        if (!$coder->validate($token)) return false;
        $tokenData = $coder->decode($token);
        if ($tokenData->isNotBefore()) return false;
        if ($tokenData->isExpired()) return false;
        return true;
    }

    private static function proccessBasicToken(string $token, $callback): bool
    {
        $tokenData = base64_decode($token);
        $tokenData = explode(":", $tokenData);
        if (count($tokenData) != 2) return false;
        $login = $tokenData[0];
        $password = $tokenData[1];

        if (!$callback($login, $password)) return false;

        return true;
    }

    static function decodeBasicToken(TokenData $td): ?TokenData
    {
        $tmp = base64_decode($td->token);
        $tmp = explode(":", $tmp);
        if (count($tmp) != 2) throw new \Exception("Bad basic token: " . base64_decode($td->token), 400);;
        [$td->login, $td->password]  = $tmp;
        return $td;
    }

    static function decodeBearerToken(TokenData $td): TokenData
    {
        $coder = new Coder(self::$tokenSecret);
        if (!$coder->validate($td->token)) return false;
        $decodedToken = $coder->decode($td->token);
        $td->bearerHeader = $decodedToken->header;
        $td->bearerPayload = $decodedToken->payload;
        return $td;
    }
}
