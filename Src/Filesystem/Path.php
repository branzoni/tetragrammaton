<?php

namespace Tet\Filesystem;

use Tet\HTTP\Server;

class Path
{
    protected string $path;

	public function __construct($path)
    {
        $this->path = realpath($path);
        if(!$this->path) $this->path = $path;
    }

	public function __toString()
    {
        return $this->path;
    }

    /*
     * путь к родительской папке файла
    */
	public function getDirname(): string
    {
        return pathinfo($this->path, PATHINFO_DIRNAME);        
    }

    /*
     * имя файла без расширения
    */
	public function getFilename(): string
    {
        return pathinfo($this->path, PATHINFO_FILENAME);
    }

    /*
     * имя файла с расширением
    */
	public function getBasename(): string
    {
        return basename($this->path);
        return pathinfo($this->path, PATHINFO_BASENAME);
    }

	public function getSegments(): array
    {
        $tmp = $this->path;
        if ($tmp[0] = "/") $tmp = substr($tmp, 1);
        return explode("/", $tmp);
    }

	public function getSegmentCount(): int
    {
        return count($this->getSegments());
    }

	public function getLocalPath(): string
    {
        return $this->getPath(true);
    }

	public function getRemotePath(): string
    {
        return $this->getPath(false);
    }

	public function getRelativePath(): string
    {
        $tmp = $this->path;
        $tmp = str_replace("\\", "/", $tmp);
        $tmp = str_replace([Server::getRoot(), Server::getRoot(false)], "", $tmp);
        return $tmp;
    }

	public function getRealPath(): string
    {
        return realpath($this->path);
    }

	private function getPath(bool $local = true): string
    {
        $tmp = $this->getRelativePath();
        $tmp = Server::getRoot($local) . $tmp;
        return $tmp;
    }

	public function isRemote(): bool
    {
        // возвращает результат проверки, является ли формат пути URL'ом:
        /**
         * проблема:
         * - нужно определять в каком виде получен путь,
         *  чтобы понимать как его использовать с файловой системой
         *  или запросами к другим серверам
        */
        $string = mb_strtolower($this->path);
        if ($this->isURL()) return true;
        if (preg_match('/^(localhost)/', $string) === 1) return true;
        if (preg_match('/\/.*/', $string) === 1) return true; //подходит только для файлов и страниц, где в адресе явно прописан документ вплоть до расширения
        return false;
    }

	public function isURL():bool
    {
        $string = mb_strtolower($this->path);
        return (preg_match('/^(https|http|ftp|file)\:\/\//', $string) === 1);
    }

	public function isLocal(): bool
    {
        // возвращает результат проверки, является ли формат пути внутренним:
         //nо есть написан он в формате 
        return !$this->isURL();
    }

	public function isWindowsPath():bool
    {
        return (preg_match('/^([a-z]|[A-Z]):\\\/', $this->path) === 1);
    }

	public function isExists(): bool
    {   
        // сюда должен попадать только реальный путь (роуты не подходят)     
        return (file_exists($this->path));
    }


	public function unlink(): void
    {
        if (!$this->isExists()) return;
        unlink($this->path);
    }

	public function delete(): void
    {
        $this->unlink();
    }

	public function rename(string $destination): void
    {
        if(!Filesystem::createDirectory((new Path($destination))->getDirname())) return;
        rename($this->path, $destination);
    }

	public function move(string $destination): void
    {
        $this->rename($destination);
    }

	public function copy(string $destination): void
    {
        $file = new File($destination);
        if (!Filesystem::createDirectory($file->getDirname())) return;
        // скачиваем файл по ссылке и сохраняем по указанному пути

        // считываем содержимое исходного файла
        $source = fopen($this->path, "rb");
        if (!$source) return false;

        // открываем на запись целевой файл
        $destination = fopen($destination, "wb");
        if (!$destination) return false;

        // пишем данные блоками
        while (!feof($source)) {
            fwrite($destination, fread($source, 4096));
        }

        fclose($destination);
        fclose($source);
    }
}
