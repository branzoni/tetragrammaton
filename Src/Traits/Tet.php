<?php

namespace Tet\Traits;

use Tet\Tet as TetTet;

trait Tet
{
    private static function tet(): TetTet
    {
        global $tet;
        return  $tet;
    }
}
