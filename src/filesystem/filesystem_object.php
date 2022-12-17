<?php

namespace Tetra;

use Tetra\Server;

class FileSystemObject
{
    protected $path;

    function __construct($path)
    {
        $this->path = $path;
    }

    function dirname()
    {
        $tmp = pathinfo($this->path, PATHINFO_DIRNAME);
        return $tmp;
    }

    function filename()
    {
        return pathinfo($this->path, PATHINFO_FILENAME);
    }

    function basename()
    {        
        return pathinfo($this->path, PATHINFO_BASENAME);
    }

    function is_exists()
    {
        return file_exists($this->path);
    }

    function is_remote()
    {
        $string = mb_strtolower($this->path);
        if (preg_match('/^(https|http|ftp)\:\/\//', $string) === 1) return true;
        if (preg_match('/^(localhost)/', $string) === 1) return true;
        if (preg_match('/\/.*/', $string) === 1) return true; //подходит только для файлов и страниц, где в адресе явно прописан документ вплоть до расширения
        return false;
    }

    function is_local()
    {
        $string = mb_strtolower($this->path);
        if (preg_match('/^[a-z]:\\\/', $string) === 1) return true;
        if (preg_match('/.*\\\/', $string) === 1) return true;
    }

    function local_path()
    {
        return $this->path(true);
    }

    function remote_path()
    {
        return $this->path(false);
    }

    function relative_path()
    {
        $server = new Server;
        $tmp = $this->path;
        $tmp = str_replace("\\", "/", $tmp);
        $tmp = str_replace([$server->root(), $server->root(false)], "", $tmp);
        return $tmp;
    }

    function unlink()
    {
        if (!$this->is_exists()) return true;
        return @unlink($this->path);
    }

    function rename($to){
        return @rename($this->path, $to);
    }

    private function path($local = true)
    {
        $tmp = $this->relative_path();
        $tmp = (new Server)->root($local) . $tmp;
        return $tmp;
    }
}
