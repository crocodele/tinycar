<?php

namespace Tinycar\System\Application\Storage;

use Tinycar\Core\Http\Params;
use Tinycar\System\Application\Model;

class Record extends Params
{


    /**
     * Load record instance from custom data
     * @param object array $data custom source data
     * @return object Tinycar\System\Application\Storage\Record instance
     */
    public static function loadFromCustomData(array $data)
    {
        return new self($data);
    }


    /**
     * Load record instance from row data
     * @param object $model Tinycar\System\Application\Model instance
     * @param array $data list of custom values
     * @param array $names property names to limit to
     * @return object Tinycar\System\Application\Storage\Record instance
     */
    public static function loadFromModelData(Model $model, array $data, array $names)
    {
        $result = array();

        // Default values for specified properties
        $properties = (count($names) > 0 ?
            $model->getPropertiesByNames($names) :
            $model->getProperties()
        );

        // Default values for all model properties
        foreach ($properties as $p)
            $result[$p->getName()] = $p->getDefaultValue();

        // Add native properties
        foreach ($data[0] as $name => $value)
        {
            // Undesired property
            if (!array_key_exists($name, $result))
                continue;

            // Try to get property by name
            $p = $model->getNativePropertyByName($name);

            // Set custom value
            if (is_object($p))
                $result[$name] = $p->getAsValue($value);
        }

        // Get as native data structure
        $native = self::getAsNativeData($data);

        // Make sure we have values for all model properties
        foreach ($native as $name => $value)
        {
            // Undesired property
            if (!array_key_exists($name, $result))
                continue;

            // Try to get property by name
            $p = $model->getCustomPropertyByName($name);

            // Set custom value
            if (is_object($p))
                $result[$name] = $p->getAsValue($value);
        }

        // Create new instance
        return new self($result);
    }


    /**
     * Get specified raw data rows as native data structure
     * @param array $source source data to study
     * @return array native data
     */
    private static function getAsNativeData(array $data)
    {
        $result = array();

        foreach ($data as $item)
        {
            // Simple root value level
            if (strpos($item['name'], '.') === false)
            {
                $result[$item['name']] = $item['value'];
                continue;
            }

            // Assign reference
            $ref = &$result;

            // Path parts
            $path = explode('.', $item['name']);

            // Go trough path
            for ($i = 0, $j = count($path); $i < $j; ++$i)
            {
                // Resolve key with proper type
                $key = is_numeric($path[$i]) ? intval($path[$i]) : $path[$i];

                // Create level once
                if (!array_key_exists($key, $ref) || !is_array($ref[$key]))
                    $ref[$key] = array();

                // Move reference deper
                $ref = &$ref[$key];

                // Assign value on last key
                if ($i === $j - 1)
                {
                    $ref = $item['value'];
                    unset($ref);
                }
            }
        }

        return $result;
    }


    /**
     * @see Tinycar\Core\Http\Params::getAll()
     */
    public function getAllData()
    {
        return $this->getAll();
    }
}
