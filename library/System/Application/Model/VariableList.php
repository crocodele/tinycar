<?php

namespace Tinycar\System\Application\Model;

use Tinycar\System\Application\Model\Variable;

class VariableList
{
    private static $parselist = array();

    private $variables = array();
    private $variable_amount = 0;
    private $variable_values = array();


    /**
     * Initiate class
     * @param Variable[] $variables list of variables in list
     */
    public function __construct(array $variables)
    {
        $this->variables = $variables;
        $this->variable_amount = count($variables);
    }


    /**
     * Load new variable list instance from specified string
     * @param string $source source string to study
     * @return VariableList
     */
    public static function loadByString($source)
    {
        // Invalid value
        if (!is_string($source))
            return new self(array());

        // String has not variables
        if (strlen($source) === 0 || strpos($source, '$') === false)
            return new self(array(Variable::loadString($source)));

        // We have already parsed this source string during
        // and can now use the same parsing result again
        if (array_key_exists($source, self::$parselist))
            return new self(Variable::loadList(self::$parselist[$source]));

        $list = array();

        // Copy source
        $data = $source;

        // Separate into variables and static strings
        do
        {
            // Find next variable
            preg_match("'([\!]{0,1})\\$([a-z0-9\_]{2,})\.([a-z0-9\_]{2,})'m",
                $data,
                $match
            );

            // We must have a match
            if (count($match) > 0)
            {
                // Get prefix string
                $index = strpos($data, $match[0]);
                $prefix = $index > 0 ? substr($data, 0, $index) : '';

                // Add static prefix string
                if (strlen($prefix) > 0)
                    $list[] = array($prefix);

                // Add dynmiac variable
                $list[] = array($match[2], $match[3], ($match[1] === '!'));

                // Update data string
                $data = substr($data, strlen($prefix) + strlen($match[0]));
            }

        } while (count($match) > 0);

        // Add remaining string to list
        if (strlen($data) > 0)
            $list[] = array($data);

        // Remember parsing result
        self::$parselist[$source] = $list;

        // Create list from data
        return new self(Variable::loadList($list));
    }


    /**
     * Get all variables
     * @return Variables[]
     */
    public function getVariables()
    {
        return $this->variables;
    }


    /**
     * Get variable list as a single value
     * @return mixed|null single value or null on failrue
     */
    public function getAsValue()
    {
        // We have no list items, null is the proper value
        if ($this->variable_amount === 0)
            return null;

        // We have only a single value, return it as it is
        if ($this->variable_amount === 1)
            return $this->variables[0]->getValue();

        $result = '';

        // Concatenate variables into a string
        foreach ($this->variables as $var)
            $result .= $var->getValue();

        return $result;
    }
}