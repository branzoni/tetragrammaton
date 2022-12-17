<?php

namespace Tetra;
use Tetra\FileSystemObject;

class File extends FileSystemObject{
    function extention(){        
        return pathinfo($this->path, PATHINFO_EXTENSION);
    }
}