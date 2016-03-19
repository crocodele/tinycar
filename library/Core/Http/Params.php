<?php

    namespace Tinycar\Core\Http;
    
    class Params
    {
        private $data = array();
        
        
        /**
         * Initiate class
         * @parma array [$data] initial data
         */
        public function __construct(array $data = array())
        {
            $this->data = $data;
        }
        
        
        /**
         * Get specified data property value
         * @param string $name target property name
         * @return mixed|null property value or null on failure
         */
        public function get($name)
        {
            return ($this->has($name) ?
                $this->data[$name] : null
            );
        }
        
        
        /**
         * Get all data property values as an array
         * @return array all data
         */
        public function getAll()
        {
        	return $this->data;
        }
        
        
        /**
         * Get specified data property as an array
         * @param string $name target property value
         * @return array property value as an array
         */
        public function getArray($name)
        {
        	$value = $this->get($name);
        	return (is_array($value) ? $value : array());
        }
        
        
        /**
         * Get specified data property as a boolean
         * @param string $name target property value
         * @return bool property value as a boolean
         */
        public function getBool($name)
        {
        	$value = $this->get($name);
        	return ($value === 'true' || $value === true);
        }
        
        
        /**
         * @see Tinycar\Core\Http\Params::get()
         * @deprecated
         */
        public function getData($name)
        {
        	// @note: added for compatability with Record models - remove
        	//        when references have been changed
        	return $this->get($name);
        }
                
        
        /**
         * Get specified data property as an integer
         * @param string $name target property value
         * @return int property value as an array
         */
        public function getInt($name)
        {
        	$value = $this->get($name);
        	
        	if (is_int($value))
        		return $value;
        	
        	if (is_float($value))
        		return intval($value);
        	
        	if (is_string($value) && is_numeric($value))
        		return intval($value);
        		
       		return 0;
        }
        
        
        /**
         * Get specified data property value as a Params instance
         * @param string $name target property name
         * @return object Tinycar\Core\Http\Params instance
         */
        public function getParams($name)
        {
        	return new self($this->getArray($name));
        }
        
        
        /**
         * Get specified data property value as a list
         * of Tinycar\App\Param instances
         * @param string $name target property name
         * @return array list of Tinycar\Core\Http\Params instances 
         */
        public function getParamsList($name)
        {
        	$result = array();
        	
        	foreach ($this->getArray($name) as $item)
        		$result[] = new self($item);
        	        	
        	return $result;
        }
        
        
        /**
         * Check if specified data property exists
         * @param string $name target property name
         * @return bool property exists
         */
        public function has($name)
        {
        	return array_key_exists($name, $this->data);
        }
        
        
        /**
         * Check if specified data proeprty exists and
         * is a non-empty string
         * @param string $name target property name
         * @return bool property exists and is a non-empty string
         */
        public function hasString($name)
        {
        	return (
        		$this->has($name) === true &&
        		is_string($this->data[$name]) &&
        		strlen($this->data[$name]) > 0
        	);
        }
        
        
        /**
         * Set new data property value
         * @param string $name target property name
         * @param mixed $value new property value
         */
        public function set($name, $value)
        {
        	$this->data[$name] = $value;
        }
    }