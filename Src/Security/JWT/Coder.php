<?php

namespace Tet\Security\JWT;

class Coder
{
    private $secret;

    function __construct($secret)
    {
        $this->secret = $secret;
    }

    function encode(Token $token): string
    {
        $header = $this->encodeBase64Url(json_encode($token->header));
        $payload = $this->encodeBase64Url(json_encode($token->payload));
        $signature = $this->encodeBase64Url(hash_hmac('sha256', $header . "." . $payload, $this->secret, true));
        return "$header.$payload.$signature";
    }

    function decode(string $token): Token
    {
        $parts = explode(".", $token);

        $token = new Token;
        $token->header = json_decode($this->decodeBase64Url($parts[0]), true);
        $token->payload = json_decode($this->decodeBase64Url($parts[1]), true);
        return $token;
    }

    function validate(string $token): bool
    {
        $decoded = $this->decode($token);
        $encoded = $this->encode($decoded);
        return ($token === $encoded);
    }

    private function encodeBase64Url(string $string): string
    {
        return str_replace(["+", "/", "="], ["-", "_", ""], base64_encode($string));
    }

    private function decodeBase64Url(string $string): string
    {
        return base64_decode(str_replace(["-", "_"], ["+", "/"], $string));
    }
}
