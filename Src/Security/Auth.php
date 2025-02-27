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

    public static function setTokenSecret(string $secret = ""): void
    {
        self::$tokenSecret = $secret;
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

    static function proccessBearerToken(string $token): void
    {
        $coder = new Coder(self::$tokenSecret);
        if (!$coder->validate($token)) throw new \Exception("Token not valid");
        $tokenData = $coder->decode($token);
        if ($tokenData->isNotBefore()) throw new \Exception("Token is not before");
        if ($tokenData->isExpired()) throw new \Exception("Token is expired");
    }

    private static function proccessBasicToken(string $token, $callback): void
    {
        $tokenData = base64_decode($token);
        $tokenData = explode(":", $tokenData);
        if (count($tokenData) != 2) throw new \Exception("Unknown token type (#2)", 400);;
        $login = $tokenData[0];
        $password = $tokenData[1];

        if (!$callback($login, $password)) throw new \Exception("callback error");
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
        if (!$coder->validate($token)) throw new \Exception("Token not valid");
        $decodedToken = $coder->decode($td->token);
        $td->bearerHeader = $decodedToken->header;
        $td->bearerPayload = $decodedToken->payload;
        return $td;
    }
}
