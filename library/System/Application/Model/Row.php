<?php

namespace Tinycar\System\Application\Model;

class Row
{
    private $name;
    private $value;


    /**
     * Initiate class
     * @param string $name target row name
     * @param string $value target row value
     */
    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }


    /**
     * Load row instances for specified data value
     * @param string $name target name
     * @param mixed $value target data
     * @return array list of Tinycar\System\Application\Model\Row instances
     */
    public static function loadForData($name, $value)
    {
        $result = array();

        // We have a simple value
        if (!is_array($value))
        {
            $result[] = new self($name, $value);
            return $result;
        }

        // Handle arrays with recursion
        foreach ($value as $k => $v)
        {
            $result = array_merge($result,
                self::loadForData($name.'.'.$k, $v)
            );
        }

        return $result;
    }


    /**
     * Get row name
     * @return string|null name or null on failure
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get row value
     * @return string|null value or null on failure
     */
    public function getValue()
    {
        return $this->value;
    }
}
