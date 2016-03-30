<?php

	namespace Tinycar\App;

	use Tinycar\Core\Exception;
	use Tinycar\Core\Xml\Data;

	class Locale
	{
		private $name;
		private $xdata;


		/**
		 * Initiate class
		 * @param object string $name target locale name
		 * @param object $xdata Tinycar\Core\Xml\Data instance
		 */
		public function __construct($name, Data $xdata)
		{
			// Remember
			$this->name = $name;
			$this->xdata = $xdata;
		}


		/**
		 * Try to load specified locale instace from manifest
		 * @param object $xdata manifest Tinycar\Core\Xml\Data instance
		 * @param string $name target locale name
		 * @return object Tinycar\App\Locale instance
		 */
		public static function loadFromManifest(Data $xdata, $name)
		{
			// Get target data from manifest
			$node = ($name === 'default' ?
				$xdata->getNode('locale') :
				$xdata->getNode("locale[@name='$name']")
			);

			// No locale found, return a dummy version
			if (is_null($node))
			{
				$xml = new \DOMDocument();
				$xml->loadXml('<locale></locale>');

				return new self($name, new Data($xml));
			}

			return new self($name, $node);
		}


		/**
		 * Try to load shared system locale
		 * @param string $name target locale name
		 * @return object|null Tinycar\App\Locale instance or null on failure
		 * @throw Tinycar\Core\Exception
		 */
		public static function loadFromSystem($name)
		{
			// System path to target locale file
			$path = Config::getPath('SYSTEM_PATH',
				'/locale/'.$name.'.xml'
			);

			// No such locale available, fail gracefully
			if (!file_exists($path))
				return null;

			// Create new XML document instance
			$xml = new \DOMDocument();
			$xml->preserveWhiteSpace = false;

			// Unable to read/parse XML
			if ($xml->load($path) === false)
			{
				throw new Exception('locale_file_invalid', array(
					'name' => $name,
				));
			}

			// Create new instance
			return new self($name, new Data($xml));
		}


		/**
		 * Get specified calendar property value
		 * @param strin $name target property name
		 * @return string|null property value or null on failure
		 */
		public function getCalendar($name)
		{
			return $this->xdata->getString("calendar[@name='$name']");
		}


		/**
		 * Get calendar configuration
		 * @return array map of calendar configuratoin
		 *               - int    first_weekday first day of the week
		 *               - bool   show_weeks   show week numbers
		 *               - string format_*     date formatting rules
		 */
		public function getCalendarConfig()
		{
			// Defaults
			$result = array(
				'first_weekday' => 0,
				'show_weeks'    => false,
			);

			// Find desired calendar properties
			foreach ($this->xdata->getNodes('calendar') as $node)
			{
				// Name and value
				$name = $node->getString('@name');
				$value = $node->getString();

				// Name or value is invalid
				if (!is_string($name) || !is_string($value))
					continue;

				// Show weeks as a boolean
				if ($name === 'show_weeks')
					$result[$name] = (strcasecmp($value, 'true') === 0);

				// First weekday
				else if ($name === 'first_weekday')
					$result[$name] = intval($value);

				// Formatting rule
				else if (strpos($name, 'format_') === 0)
					$result[$name] = $node->getString();
			}

			return $result;
		}


		/**
		 * Get specified format rule value
		 * @param strin $name target format rule name
		 * @return string|null format rule value or null on failure
		 */
		public function getFormat($name)
		{
			return $this->getCalendar('format_'.$name);
		}


		/**
		 * Get current locale name
		 * @return string $name target name
		 */
		public function getName()
		{
			return $this->name;
		}


		/**
		 * Get specified text property value
		 * @param string $name target text property name
		 * @return string|null text property value or null on failure
		 */
		public function getText($name)
		{
			return $this->xdata->getString("text[@name='$name']");
		}


		/**
		 * Get text properties in key-value pairs that match
		 * specified regular expression
		 * @param string $pattern regular expression pattern
		 * @return array map of matching key-value pairs
		 */
		public function getTextsByPattern($pattern)
		{
			$result = array();

			// Go trough text nodes
			foreach ($this->xdata->getNodes('text') as $node)
			{
				// Target name
				$name = $node->getString('@name');

				// Add to list when name matches pattern
				if (preg_match($pattern, $name))
					$result[$name] = $node->getString();
			}

			return $result;
		}

	}