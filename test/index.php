<?php

use Tetra\Tetra;


include("../src/tet.php");

$app = new Tetra;

$app->app_closure =  "hi";
echo $app->app_closure->ggg;
$app->app_run();


function hi(tetra $tetra){    
    echo 5/0;
    //return "hello, world!";
}
