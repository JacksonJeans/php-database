<?php
namespace JacksonJeans;

/**
 * Collection - Klasse
 * 
 * @category    Class
 * @package     GIDUTEX
 * @author      Julian Tietz <julian.tietz@gidutex.de>
 * @license     Julian Tietz <julian.tietz@gidutex.de>
 * @link        https://gidutex.de
 * @version     1.0
 */
class Collection implements \ArrayAccess
{
    /**
     * abstract Methode von ArrayAccess
     */
    public function offsetSet($offset, $value) 
    {
        $this->$offset = $value;
    }

    /**
     * Gibt die Collection als JSON string wieder
     */
    public function toJSON()
    {
        return json_encode($this->toArray(), JSON_NUMERIC_CHECK);
    }

    /**
     * Gibt die Collection als Array wieder
     */
    public function toArray()
    {
        $array = [];
        foreach ($this as  $Object) {
            $array[] = (array) $Object;
        }
        return $array;
    }

    /**
     * Gibt alle Elemente als List (eindimensionales Array) wieder
     */
    public function lists($field)
    {
        $list = [];
        foreach ($this as  $item) {
            $list[] = $item->{$field};
        }
        return $list;
    }

    /**
     * Erhalte das erste Element
     */
    public function first($offset = 0)
    {
        return isset($this->$offset) ? $this->$offset : null;
    }

    /**
     * Erhalte das letzte Element
     */
    public function last($offset = null)
    {
        $offset = count($this->toArray()) - 1;
        return isset($this->$offset) ? $this->$offset : null;
    }

    /**
     * abstract Methode von ArrayAccess
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    /**
     * abstract Methode von ArrayAccess
     */
    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }

    /**
     * abstract Methode von ArrayAccess
     */
    public function offsetGet($offset)
    {
        return isset($this->$offset) ? $this->$offset : null;
    }

    /**
     * Gibt ein Element des Objektes zurück, falls vorhanden
     * @param string|int $key 
     * - Index
     * @return mixed|null 
     */
    public function item($key)
    {
        return isset($this->$key) ? $this->$key : null;
    }

    public function __toString()
    {
        header("Content-Type: application/json;charset=utf-8");
        return  $this->toJSON();
    }
}
