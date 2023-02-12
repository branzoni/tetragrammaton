<?php

namespace Tet\Common;

class CollectionReadOnly
{
    protected array $values = [];

    function __construct(array $values = [])
    {
            $this->values = $values;
    }

    function get(string $name)
    {
        if (!$this->has($name)) return null;
        return $this->values[$name];
    }

    function getCount(): int
    {
        return count($this->values);
    }

    function getKeys(): array
    {
        return array_keys($this->values ?? []);
    }

    function toJSON(): string
    {
        return json_encode($this->values ?? []);
    }

    function toArray(): array
    {
        return $this->values ?? [];
    }

    function has(string $name): bool
    {
        return array_key_exists($name, $this->values);
    }

    // /**
    //  *  Проверяет наличие в массиве элемента с указанным именем
    //  */
    // function isSet(string $name): bool
    // {
    //     return isset($this->values[$name]);
    // }

    /**Функция проверяет наличие параметра и что его значение находится в списке, если один из пунктов не выполняется, возвращает false*/
    // function isOneOf(string $name, array $names): bool
    // {
    //     return array_search($this->$name, $names) !== false;
    // }

    /**
     * $callback($key, $value, $count, $index);
    */
    function forEach($callback)
    {
        $count = $this->getCount();
        $index = 0;
        foreach ($this->values as $key => $value) {
            $index += 1;
            $callback($key, $value, $count, $index);
        }
    }
}