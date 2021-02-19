<?php

namespace JacksonJeans;

/**
 * Item - Klasse
 * 
 * @category    Class
 * @package     GIDUTEX
 * @author      Julian Tietz <julian.tietz@gidutex.de>
 * @license     Julian Tietz <julian.tietz@gidutex.de>
 * @link        https://gidutex.de
 * @version     1.0
 */
class Item
{
    /**
     * Konstruktor 
     * @param Item|array $Item 
     * - Initialisiere das Item Objekt mittels Item oder Array
     * @return Item 
     */
    public function __construct($Item = null)
    {
        if ($Item instanceof Item) {
            foreach ($Item as $var => $element) {
                if (is_object($element)) {
                    $this->__set($var, $this->_recursivConstruct($element));
                } else {
                    $this->__set($var, $element);
                }
            }
        } elseif (is_array($Item)) {
            foreach ($Item as $var => $element) {
                if (is_array($element)) {
                    $this->__set($var, $this->_recursivConstruct($element));
                } else {
                    $this->__set($var, $element);
                }
            }
        }

        return $this;
    }

    /**
     * Initialisiert reukursiv
     * @param Item|array $elements 
     * @return $Item
     */
    private function _recursivConstruct($elements)
    {
        if (($elements instanceof Item) || (is_array($elements))) {
            $result = new Item;
            foreach ($elements as $var => $element) {
                if ((is_object($element)) || (is_array($element))) {
                    $result->$var = new Item($element);
                } else {
                    $result->$var = $element;
                }
            }
            return $result;
        }
    }

    private function _resetObject()
    {
        foreach ($this as $key => $value) {
            unset($this->$key);
        }
    }

    public function toJSON()
    {
        return json_encode($this, JSON_NUMERIC_CHECK);
    }

    public function toArray()
    {
        return (array) $this;
    }

    public function __toString()
    {
        header("Content-Type: application/json;charset=utf-8");
        return $this->toJSON();
    }

    /**
     * Bindet alle Schlüssel des Objektes neu nach $bind
     * @param array $bind 
     * - Ein Array dass das Schema enthält mit in welcher Struktur der Output ist.
     * - e.g array('after' => 'before')
     * @param bool $strict 
     * - Falls true, wird mittels == Operator gearbeitet.
     * @return Item
     */
    public function bind(array $bind, $strict = true)
    {
        $data = $this->toArray();

        $result = new Item;
        foreach ($data as $key => $value) {
            if (is_string($key)) {
                $nKey = array_search($key, $bind, $strict);
                if ($nKey === false) {
                    $nKey = $key;
                }
                if (is_array($value)) {
                    $result->$nKey = $this->_bind($value, $bind, $strict);
                } else {
                    $result->$nKey = $value;
                }
            } elseif (is_array($value)) {
                $result->$key = $this->_bind($value, $bind, $strict);
            } else {
                $result->$key = $value;
            }
        }
        $this->_resetObject();
        $this->__construct($result);

        return $this;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    /**
     * Rekursiv Funktion von bind()
     */
    private function _bind(array $values, array $bind, bool $strict)
    {
        $result = new Item;
        foreach ($values as $key => $value) {
            if (is_string($key)) {
                $nKey = array_search($key, $bind, $strict);
                if ($nKey === false) {
                    $nKey = $key;
                }
                if (is_array($value)) {
                    $result->$nKey = $this->_bind($value, $bind, $strict);
                } else {
                    $result->$nKey = $value;
                }
            } elseif (is_array($value)) {
                $result->$key = $this->_bind($value, $bind, $strict);
            } else {
                $result->$key = $value;
            }
        }

        return $result;
    }
}
