<?php

namespace Tinycar\System\Application\Model;

class Variable
{
    /**
     * @var string
     */
    private $group;

    /**
     * @var bool
     */
    private $negate;

    /**
     * @var string
     */
    private $property;

    /**
     * @var string
     */
    private $type;

    /**
     * @var mixed
     */
    private $value;


    /**
     * Initiate class
     * @param string $group variable group name
     * @param string $type variable type
     * @param string $property property name
     * @param string $value variable value
     * @param bool [$negate] variable is negative
     */
    public function __construct($group, $type, $property, $value, $negate)
    {
        $this->group = $group;
        $this->type = $type;
        $this->property = $property;
        $this->value = $value;
        $this->negate = $negate;
    }


    /**
     * Load list of variable instances from list of arrays in
     * specific, construction formats
     * @param array[] $list list of variable items
     * @return Variable[]
     */
    public static function loadList(array $list)
    {
        $result = array();

        foreach ($list as $item)
        {
            $result[] = (count($item) === 3 ?
                self::loadVariable($item[0], $item[1], $item[2]) :
                self::loadString($item[0])
            );
        }

        return $result;
    }


    /**
     * Load static string variable
     * @param string $value string value
     * @return Variable
     */
    public static function loadString($value)
    {
        return new self('string', null, null, $value, null);
    }


    /**
     * Load dynamic variable
     * @param string $type variable type
     * @param string $property property name
     * @param bool [$negate] variable is negative
     * @return Variable
     */
    public static function loadVariable($type, $property, $negate = false)
    {
        return new self('variable', $type, $property, null, $negate);
    }


    /**
     * Get variable group name
     * @return string group name
     */
    public function getGroup()
    {
        return $this->group;
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
     * Get variable value
     * @return mixed variabel value
     */
    public function getValue()
    {
        return $this->value;
    }


    /**
     * Check to see if this variable is for a negative value
     * @return bool is negative value
     */
    public function isNegative()
    {
        return $this->negate;
    }


    /**
     * Check if this is a dynamic variable
     * @return bool is variable
     */
    public function isVariable()
    {
        return ($this->group === 'variable');
    }


    /**
     * Set value for this variable
     * @param mixed $value variable value
     */
    public function setValue($value)
    {
        // Negate value
        if ($this->isVariable() && $this->isNegative() && is_bool($value))
            $value = ($value === false);

        // Set fixed value
        $this->value = $value;
    }
}