<?php

namespace Tet\Filesystem;

class UploadedFile
{
    public $name;
    public $type;
    public $path;
    public $size;

    function __construct($file)
    {
        $this->name = $file["name"];
        $this->type = $file["type"];
        $this->path = $file["tmp_name"];
        $this->size = $file["size"];        
    }
}
