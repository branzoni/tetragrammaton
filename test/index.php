<?php

use Tetra\Tetra;

use function Tetra\HTTP\Client\b;

include("../src/tet.php");

$tet = new Tetra;
//echo $tet->about();

$tet->client()->request()->header("qqq")->set("qqqq2");
echo $tet->client()->request()->header("qqq");
