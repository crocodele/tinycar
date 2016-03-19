<?php 

	namespace Tinycar\Core\Xml;
	
	class Data
	{
		private $context;
		private $xmldoc;
		private $xpath;
		
		
		/**
		 * Initiate class
		 * @param object $xml DOMDocument instance
		 */
		public function __construct(\DOMDocument $xml, \DOMNode $context = null)
		{
			// Create xpath query instance from XML
			$this->xpath = new \DOMXpath($xml);
			
			// Use first node as context if none provided
			if (is_null($context))
				$context = $xml->firstChild;

			// Remember
			$this->xmldoc = $xml;
			$this->context = $context; 
		}
		
		
		/**
		 * Get specified data structure as a Data instance
		 * @param array $attributes node attributes
		 */
		public function getAsNode(array $attributes)
		{
			// Craeate dummy node
			$node = $this->xmldoc->createElement('node');
				
			// Add properties
			foreach ($attributes as $name => $value)
			{
				$attribute = $this->xmldoc->createAttribute($name);
				$property = $this->xmldoc->createTextNode($value);
				$attribute->appendChild($property);
				$node->appendChild($attribute);
			}
				
			return new self($this->xmldoc, $node);
		}
		
		
		/**
		 * Get current XML node as XML string
		 * @return string XML
		 */
		public function getAsXml()
		{
			return $this->xmldoc->saveXml();
		}
		
		
		/**
		 * Get list of attibute names and values from specified node path
		 * @param  string $path target path
		 * @return array list of attributes in key-value pairs
		 */
		public function getAttributes($path)
		{
			// Get target node
			$node = $this->xpath->query($path, $this->context)->item(0);
			
			$result = array();
			
			// No node or attributes available
			if (is_null($node) || is_null($node->attributes))
				return $result;
			
			// Get attribute values
			foreach ($node->attributes as $attribute)
				$result[$attribute->name] = $attribute->value;

			return $result;
		}
		
		
		/**
		 * Get specified value as an integer
		 * @param string $path target path
		 * @return int node value as a number or 0 on failure
		 */
		public function getInt($path)
		{
			$value = $this->getString($path);
			return (is_numeric($value) ? floatval($value) : 0);
		}
		
		
		/**
		 * Get one node as Data instance
		 * @param string $path target path
		 * @return object|null Tinycar\Core\Xml\Data instance or null on failure 
		 */
		public function getNode($path)
		{
			$list = $this->getNodes($path);
			return (count($list) > 0 ? $list[0] : null);
		}
		
		
		/**
		 * Get list of of nodes as Data instances
		 * @param string $path target path
		 * @return array list of Tincyar\Core\Xml\Data instances
		 */
		public function getNodes($path)
		{
			$result = array();
			
			// Get list of nodes
			$list = $this->xpath->query($path, $this->context);

			// Create instances
			foreach ($list as $node)
				$result[] = new self($this->xmldoc, $node);
			
			return $result;
		}
		
		
		/**
		 * Get specified value as a string
		 * @param string [$path] target path
		 * @return string|null node value as text or null on failure
		 */
		public function getString($path = null)
		{
			if (is_null($path))
				return $this->context->firstChild->nodeValue;
				
			$node = $this->xpath->query($path, $this->context);
			
			if (!is_object($node))
				return null;
			
			$node = $node->item(0);
			
			if (!is_object($node))
				return null;
			
			return $node->firstChild->nodeValue;
		}
	}