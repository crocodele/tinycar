<?php

    namespace Tinycar\Core\Xml;

    use Tinycar\App\Config;
    use Tinycar\Core\Exception;


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
         * Load XML from filed
         * @param string $file system path to file
         * @return object Tinycar\Core\Xml\Data instance
         * @throws Tinycar\Core\Exception
         */
        public static function loadFromFile($file)
        {
            // Manifest file is missing
            if (!file_exists($file))
                throw new Exception('xml_file_missing');

            // Create new XML document instance
            $xml = new \DOMDocument();
            $xml->preserveWhiteSpace = false;

            // Unable to read/parse XML
            if ($xml->load($file) === false)
                throw new Exception('xml_data_invalid');

            // Get as instance
            return new Data($xml);
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
                $result[strval($attribute->name)] = $attribute->value;

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
         * Get specified value native type, whatever it might be
         * @param string $path target path
         * @return mixed|null node value or null on failure
         */
        public function getNative($path)
        {
            // Get value as a string
            $value = $this->getString($path);

            // Not a string, failed
            if (!is_string($value))
                return null;

            // Numeric value
            if (is_numeric($value))
                return floatval($value);

            // Boolean value
            if (strcasecmp($value, 'true') === 0 || strcasecmp($value, 'false') === 0)
                return (strcasecmp($value, 'true') === 0);

            // Just a string
            return $value;
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

            $result = $node->firstChild;

            if (!is_object($result))
                return null;

            return $result->nodeValue;
        }
    }