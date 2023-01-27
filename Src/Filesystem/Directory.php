<?php

namespace Tet;

class Directory extends Path
{
    // возвращает массив с путями к файлам из указанной папки и соответствующие маске
    function getFileList($patterns = ["*.*"], bool $recursive = true): array
    {
        $files = [];
        switch (gettype($patterns)) {
            case "array";
                foreach ($patterns as $pattern) {
                    $files = array_merge($files, (new Directory($this->path))->getFileList($pattern, $recursive));
                }
                break;
            case "string":
                // получаем файлы в выбранном каталоге                
                $files = glob($this->path . "/" . $patterns, 0);
                if (!$recursive) return $files;

                // получаем список подпапок
                $sub_folders = $this->getDirectoryList();



                // рекурсивно перебираем подпапки
                foreach ($sub_folders as $folder) {
                    $files = array_merge($files, (new Directory($folder))->getFileList($patterns, $recursive));
                }
                break;
        }

        return $files;
    }

    function getDirectoryList(): array
    {
        return glob($this->path . '/*', GLOB_ONLYDIR | GLOB_NOSORT);
    }
}
