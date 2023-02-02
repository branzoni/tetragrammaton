<?php

namespace Tet\Common;

use Tet\HTTP\Client;
use Tet\HTTP\Server;
use Tet\Mail\Mailer;
use Tet\Database\MySQL;
use Tet\Filesystem\Filesystem;

class Fasade
{
    protected Collection $params;
    protected MySQL $mySQL;

    function params(): Collection
    {
        if (!isset($this->params)) $this->params = new Collection;
        return $this->params;
    }

    function mySQL(): MySQL
    {
        if (!isset($this->mySQL)) $this->mySQL = new MySQL;
        return $this->mySQL;
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
