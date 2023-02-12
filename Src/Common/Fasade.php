<?php

namespace Tet\Common;

use Tet\HTTP\Client;
use Tet\HTTP\Server;
use Tet\Mail\Mailer;
use Tet\Database\MySQL;
use Tet\Filesystem\Filesystem;
use Tet\Security\Auth;

class Fasade
{
    protected Collection $params;
    protected MySQL $mySQL;
    protected Auth $auth;
    protected Log $log;

    function auth():Auth
    {
        return $this->auth ?? $this->auth = new Auth;
    }

    function log():Log
    {
        return $this->log ?? $this->log = new Log;
    }

    function params(): Collection
    {
        return $this->params ??  $this->params = new Collection;
    }

    function mySQL(): MySQL
    {
        return $this->mySQL ?? $this->mySQL = new MySQL;
    }

    function filesystem(): Filesystem
    {
        return new Filesystem;
    }

    function server(): Server
    {
        return new Server;
    }

    function client(): Client
    {
        return new Client;
    }

    function utils(): Utils
    {
        return new Utils;
    }

    function mailer(): Mailer
    {
        return new Mailer;
    }
}
