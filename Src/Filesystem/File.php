<?php

namespace Tet\Filesystem;

class File extends Path
{
    function getExtention()
    {
        return pathinfo($this->path, PATHINFO_EXTENSION);
    }

    function getSize()
    {
        return filesize($this->path);
    }

    function getContent()
    {

        return file_get_contents($this->path);
    }

    function __toString()
    {

        return $this->getContent();
    }
}
