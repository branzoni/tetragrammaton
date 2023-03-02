<?php

namespace Tet\Common;

class Controller
{
    private  Fasade $fasade;
    
    function __construct(Fasade $fasade)
    {
        $this->fasade = $fasade;
        
    }

    function fasade():Fasade
    {
        return $this->fasade;
    }
}