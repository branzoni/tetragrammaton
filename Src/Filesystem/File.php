<?php

namespace Tet\Filesystem;

class File extends Path
{
	public function getExtention()
    {
        return pathinfo($this->path, PATHINFO_EXTENSION);
    }

	public function getSize()
    {
        return filesize($this->path);
    }

	public function getContent()
    {
        return file_get_contents($this->path);
    }

	public function getHash()
    {
        return md5_file($this->path);
    }

	public function __toString()
    {

        return $this->getContent();
    }
}
