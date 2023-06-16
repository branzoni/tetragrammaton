<?php

namespace Tet\Security;

class TokenData
{
    public string $type;
    public $token;
    public $login;
    public $password;
    public $bearerHeader;
    public $bearerPayload;
}