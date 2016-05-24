<?php

namespace Tinycar\App;

class Config
{
    private static $data = array();


    /**
     * Add all properties to current current data
     * @param array $properties new properties as key-value pairs
     */
    public static function addAll(array $properties)
    {
        self::$data = array_merge(self::$data, $properties);
    }


    /**
     * Get specified property value
     * @param string $name target property name
     * @return mixed|null property value or null on failure
     */
    public static function get($name)
    {
        return (array_key_exists($name, self::$data) ?
            self::$data[$name] : null
        );
    }


    /**
     * Get specified property value as a path
     * @param string $name target property name
     * @param string [$postfix] string to append to path
     * @return mixed|null property value or null on failure
     */
    public static function getPath($name, $postfix = '')
    {
        $path = self::get($name);
        return is_string($path) ? $path.$postfix : null;
    }


    /**
     * Set specified property value
     * @param string $name target property name
     * @param string $value new property value
     */
    public static function set($name, $value)
    {
        self::$data[$name] = $value;
    }
}
