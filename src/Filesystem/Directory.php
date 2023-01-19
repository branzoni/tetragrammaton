<?php

namespace Tet;

class Directory extends Path
{
    // возвращает массив с путями к файлам из указанной папки и соответствующие маске
    function getFileList(array $patterns = ["*.*"]): array
    {
        $files = [];
        foreach ($patterns as $pattern) {
            $files = array_merge($files, $this->glob_recursive($this->path . $pattern));
        }
        return $files;
    }

    //###############################################################

    // получаем файлы в выбранной папке, включая подпапки
    private function glob_recursive($pattern)
    {
        // получаем файлы в выбранном каталоге
        $tmp = glob($pattern, 0);

        // получаем список подпапок
        $sub_folders = glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT);

        // рекурсивно перебираем подпапки
        foreach ($sub_folders as $folder) {
            $tmp = array_merge($tmp, $this->glob_recursive($folder . '/' . basename($pattern)));
        }

        // отдаем результат
        return $tmp;
    }
}
