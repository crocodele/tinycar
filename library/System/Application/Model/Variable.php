<?php

namespace Tinycar\System\Application\Model;

class Variable
{
    private $negate;
    private $property;
    private $type;


    /**
     * Initiate class
     * @param string $type variable type
     * @param string $property property name
     * @param bool $negate variable is negative
     */
    public function __construct($type, $property, $negate)
    {
        $this->type = $type;
        $this->property = $property;
        $this->negate = $negate;
    }


    /**
     * Load new variable instance for specified string
     * @param string $source target source string
     * @return object|null Tinycar\System\Application\Model\Variable
     *                     instance or null on failure
     */
    public static function loadByString($source)
    {
        // Not a valid string
        if (!is_string($source) || strlen($source) < 2)
            return null;

        // Not a variable string
        if (strpos($source, '$') !== 0 && strpos($source, '!$') !== 0)
            return null;

        $negate = false;

        // Detect negate
        if (strpos($source, '!$') === 0)
        {
            $negate = true;
            $source = substr($source, 1);
        }

        // Split type and property
        list($type, $property) = explode('.', $source, 2);

        // Create new instances
        return new self($type, $property, $negate);
    }


    /**
     * Get specified value as this variables value
     * @param mixed $value source value
     * @return mixed source value, negative when neede
     */
    public function getAsValue($value)
    {
        // Negate value
        if ($this->isNegative() && is_bool($value))
            return ($value === false);

        return $value;
    }


    /**
     * Get variable property name
     * @return string property name
     */
    public function getProperty()
    {
        return $this->property;
    }


    /**
     * Get variable type
     * @return string variable type
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * Check to see if this variable is for a negative value
     * @return bool is negative value
     */
    public function isNegative()
    {
        return $this->negate;
    }
}
