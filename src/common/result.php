<?php

namespace Tet;

use stdClass;

/**
 * Класс для стандартного возврата результата работы функции в виде объекта
 */
class Result extends stdClass
{
    public $error = true;
    public $result = false;
    public $description = "";
    public $data = false;
}
