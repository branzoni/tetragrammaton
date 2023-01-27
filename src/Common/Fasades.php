<?php

namespace Tet;

use Tet\DbInterface;
use Tet\HTTP\Client;
use Tet\HTTP\Server;
use Tet\Mail;
use Tet\Utils;

class Fasades
{
    protected Params $params;
    protected FileSystem $fiesystem;
    protected Server $server;
    protected Client $client;
    protected DbInterface $db;
    protected Utils $utils;
    protected Mail $mail;

    function getParams(): Params
    {
        if (!isset($this->params)) $this->params = new Params;
        return $this->params;
    }

    function getFilesystem(): FileSystem
    {

        if (!isset($this->fiesystem)) $this->fiesystem = new FileSystem;
        return $this->fiesystem;
    }

    function getServer(): Server
    {

        if (!isset($this->server)) $this->server = new Server;
        return $this->server;
    }

    function getClient(): Client
    {

        if (!isset($this->client)) $this->client = new Client;
        return $this->client;
    }

    function getDb(DbInterface $db): DbInterface
    {
        if (!isset($this->db)) $this->db = $db;
        return $this->db;
    }

    function getUtils(): Utils
    {
        if (!isset($this->utils)) $this->utils = new Utils;
        return $this->utils;
    }

    function getMail(): Mail
    {
        if (!isset($this->mail)) $this->mail = new Mail;
        return $this->mail;
    }
}