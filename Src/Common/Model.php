<?php

namespace Tet\Common;

use Tet\Database\MySQL;
use Tet\Filesystem\Filesystem;

// класс для работы с моделями (внезапно)))

class Model
{
    private MySQL $mySQL;
    private Filesystem $filesystem;
    

    function __construct(Fasade $fasade)
    {
        $this->mySQL = $fasade->mySQL();
    }

    function mySQL():MySQL
    {
        return $this->mySQL;
    }

    function utils():Utils
    {
        return new Utils;
    }

    function filesystem():Filesystem
    {
        return $this->filesystem;
    }
}