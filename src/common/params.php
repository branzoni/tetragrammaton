<?php

namespace Tet;

use Exception;

/**
 * Обеспечивает единообразную работу с ассоциированными массивами:
 * - установка
 * - получение
 * - проверка наличия ключа
 * - количество ключей
 * @author Sergey Afanasyev <sergey.v.afanasyev@gmail.com>
 */
class Params
{
    protected array $data;

    /**
     *  Заполняет массив свойсв данными, переданными в параметре
     */
    function load(array $data): Bool
    {
        $this->data = $data;
        return true;
    }

    function set(string $propname, $value): bool
    {
        $this->data[$propname] = $value;
        return true;
    }

    function get(string $propname)
    {
        if (!$this->isSet($propname)) throw new Exception("unknown property name '$propname'");
        return $this->data[$propname];
    }

    function has(string $propname): bool
    {
        return $this->isExists($propname);
    }

    /**
     *  Проверяет наличие в массиве элемента с указанным именем
     */
    function isSet(string $propname): bool
    {
        return isset($this->data[$propname]);
    }

    function isExists(string $propname): bool
    {
        return array_key_exists($propname, $this->data);
    }

    /**Функция проверяет наличие параметра и что его значение находится в списке, если один из пунктов не выполняется, возвращает false*/
    function isKnown(string $propname, array $knownValues): bool
    {
        if (!$this->isSet($propname)) return false;
        if (array_search($this->$propname, $knownValues) === false)  return false;
        return true;
    }

    /**
     *  Возвращает количество свойств в массиве свойств
     */
    function getCount(): int
    {
        return count($this->data ?? []);
    }

    function getKeys(): array
    {
        return array_keys($this->data ?? []);
    }

    function toJSON(): string
    {
        return json_encode($this->data ?? []);
    }

    function toArray(): array
    {
        return $this->data ?? [];
    }
}
