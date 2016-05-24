<?php

namespace Tinycar\System\Application\Model;

use Tinycar\Core\Xml\Data;

class Property
{
    private $name;
    private $native;
    private $xdata;

    private static $property_types = array(
        'bool'   => array('native' => 'bool'),
        'epoch'  => array('native' => 'int'),
        'int'    => array('native' => 'int'),
        'list'   => array('native' => 'array'),
        'string' => array('native' => 'string'),
    );


    /**
     * Initiate clas
     * @param string $name target property name
     * @param bool $native is property native
     * @param object $xdata Tinycar\Core\Xml\Data instance
     */
    public function __construct($name, $native, Data $xdata)
    {
        $this->name = $name;
        $this->native = $native;
        $this->xdata = $xdata;
    }


    /**
     * Get specified value as a value for this property
     * @param mixed $value target value to study
     * @return mixed altered value
     */
    public function getAsValue($value)
    {
        if (is_null($value))
            return null;

        // Get native type
        $type = $this->getNativeType();

        // Integer
        if ($type === 'int')
            return (int) $value;

        // Array
        if ($type === 'array')
            return is_array($value) ? $value : array();

        // Boolean
        if ($type === 'bool')
        {
            if (is_bool($value))
                return $value;

            if (is_string($value))
                return (strcasecmp($value, 'true') === 0);

            return false;
        }

        return $value;
    }


    /**
     * Get specified value as a value for this property,
     * intended for storage
     * @param mixed $value target value to study
     * @return mixed altered value
     */
    public function getAsValueForStorage($value)
    {
        // Get specified value
        $value = $this->getAsValue($value);

        // Boolean values
        if (is_bool($value))
            return ($value === true ? 'true' : 'false');

        return $value;
    }


    /**
     * Get suitable default value for this property
     * @return mixed default value
     */
    public function getDefaultValue()
    {
        switch($this->getType())
        {
            case 'list':
                return array();
        }

        return null;
    }


    /**
     * Get property name
     * @return string name
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * Get native property type
     * @return string native type
     */
    public function getNativeType()
    {
        $type = $this->getType();
        $data = self::$property_types[$type];

        return $data['native'];
    }


    /**
     * Get property type
     * @return string type
     */
    public function getType()
    {
        $type = $this->xdata->getString('@type');

        // Return only valid values
        if (array_key_exists($type, self::$property_types))
            return $type;

        // default to string
        return 'string';
    }


    /**
     * Get type formatting rule name
     * @return string format rule name
     */
    public function getTypeFormat()
    {
        if ($this->getType() === 'epoch')
            return 'datetime';

        return 'string';
    }


    /**
     * Check if specified value is an empty value for this property
     * @param mixed $value target value to study
     * @return bool is an empty value value
     */
    public function isEmptyValue($value)
    {
        $type = $this->getType();

        switch ($type)
        {
            case 'string':
                return (is_null($value) || strlen($value) === 0);

            case 'int':
                return (is_null($value) || $value === '');

            case 'epoch':
                return (is_null($value) || $value === '0' || $value === 0);

            case 'bool':
                return is_null($value);
        }

        return false;
    }


    /**
     * Check it this a native property
     * @return bool is native property
     */
    public function isNative()
    {
        return ($this->native === true);
    }


    /**
     * Check if this property is required
     * @return bool is required
     */
    public function isRequired()
    {
        return ($this->xdata->getString('@required') === 'true');
    }


    /**
     * Check if specified value is valid for this property
     * @param mixed $value target value to study
     * @return bool is valid value
     */
    public function isValidValue($value)
    {
        switch ($this->getType())
        {
            case 'bool':
                return (
                    (is_null($value) || is_bool($value)) ||
                    (is_string($value) && (strcasecmp($value, 'true') === 0 || strcasecmp($value, 'false') === 0))
                );

            case 'epoch':
                return (is_null($value) || is_int($value) || is_numeric($value));

            case 'list':
                return is_array($value);

            case 'int':
                return (is_int($value) || is_numeric($value));

            case 'string':
                return (is_null($value) || is_string($value));
        }

        return false;
    }
}
