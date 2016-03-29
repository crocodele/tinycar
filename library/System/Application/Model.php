<?php

	namespace Tinycar\System\Application;

	use Tinycar\Core\Exception;
	use Tinycar\Core\Xml\Data;
	use Tinycar\System\Application;
	use Tinycar\System\Application\Model\Property;
	use Tinycar\System\Application\Model\Row;

	class Model
	{
		private $app;
		private $properties_native;
		private $properties_custom;
		private $xdata = array();

		// Native properties
		private static $native = array(
			array('name' => 'id', 'type' => 'int'),
			array('name' => 'created_time', 'type' => 'epoch'),
			array('name' => 'modified_time', 'type' => 'epoch'),
		);


		/**
		 * Initiate class
		 * @param object $app Tinycar\System\Application instance
		 * @param object $xdata Tinycar\Core\Xml\Data instance
		 */
		public function __construct(Application $app, Data $xdata)
		{
			// Remember
			$this->app = $app;
			$this->xdata = $xdata;

			// Initiate properties list
			$this->initProperties();
		}


		/**
		 * Get custom property instances
		 * @return array map Tinycar\System\Application\Model\Property instances
		 */
		public function getCustomProperties()
		{
			return $this->properties_custom;
		}

		/**
		 * Get native model property instance by name
		 * @param string $name target property name
		 * @return object|null Tinycar\System\Application\Model\Property instance
		 *                     or null on failure
		 */
		public function getCustomPropertyByName($name)
		{
			return (array_key_exists($name, $this->properties_custom) ?
				$this->properties_custom[$name] : null
			);
		}


		/**
		 * Get specified data as rows
		 * @param array $data source data to study
		 * @return array Tinycar\System\Application\Model\Row instances
		 * @throws Tinycar\Core\Exception
		 */
		public function getDataAsRows(array $data)
		{
			$result = array();

			foreach ($data as $name => $value)
			{
				// Try to get custom property
				$p = $this->getCustomPropertyByName($name);

				// Invalid property
				if (is_null($p))
				{
					throw new Exception(
						'model_property_unknown',
						array('name' => $name)
					);
				}

				// Invalid property value
				if (!$p->isValidValue($value))
				{
					throw new Exception(
						'model_value_invalid',
						array('name' => $p->getName())
					);
				}

				// Create new row instances
				$result = array_merge($result, Row::loadForData(
					$p->getName(), $p->getAsValueForStorage($value)
				));
			}

			return $result;
		}


		/**
		 * Get current data model as rows
		 * @param array $data source data to study
		 * @return array Tinycar\System\Application\Model\Row instances
		 * @throws Tinycar\Core\Exception
		 */
		public function getModelAsRows(array $data)
		{
			$result = array();

			foreach ($this->getCustomProperties() as $p)
			{
				// No value for this property
				if (!array_key_exists($p->getName(), $data))
				{
					// Property is required
					if ($p->isRequired())
					{
						throw new Exception('model_property_required', array(
							'name' => $p->getName(),
						));
					}
				}
				// Invalid property value
				else if (!$p->isValidValue($data[$p->getName()]))
				{
					throw new Exception('model_value_invalid', array(
						'name' => $p->getName(),
					));
				}
				// Missing a value
				else if ($p->isRequired() && $p->isEmptyValue($data[$p->getName()]))
				{
					throw new Exception('model_value_required', array(
						'name' => $p->getName(),
					));
				}
				// Create new row instances
				else
				{
					$result = array_merge($result, Row::loadForData(
						$p->getName(), $p->getAsValueForStorage($data[$p->getName()])
					));
				}
			}

			return $result;
		}


		/**
		 * Get native property instances
		 * @return array map Tinycar\System\Application\Model\Property instances
		 */
		public function getNativeProperties()
		{
			return $this->properties_native;
		}


		/**
		 * Get native model property instance by name
		 * @param string $name target property name
		 * @return object|null Tinycar\System\Application\Model\Property instance
		 *                     or null on failure
		 */
		public function getNativePropertyByName($name)
		{
			return (array_key_exists($name, $this->properties_native) ?
				$this->properties_native[$name] : null
			);
		}


		/**
		 * Get property instances by list of names
		 * @param array $names list of names
		 * @return array Tinycar\System\Application\Model\Property instances
		 */
		public function getPropertiesByNames(array $names)
		{
			$result = array();

			foreach ($names as $name)
			{
				$item = $this->getPropertyByName($name);

				if (is_object($item))
					$result[] = $item;
			}

			return $result;
		}


		/**
		 * Get model property instance by name
		 * @param string $name target property name
		 * @return object|null Tinycar\System\Application\Model\Property instance
		 *                     or null on failure
		 */
		public function getPropertyByName($name)
		{
			$result = $this->getCustomPropertyByName($name);

			if (is_null($result))
				$result = $this->getNativePropertyByName($name);

			return $result;
		}


		/**
		 * Get model property instances
		 * @return array map Tinycar\System\Application\Model\Property instances
		 */
		public function getProperties()
		{
			return array_merge(
				$this->properties_native,
				$this->properties_custom
			);
		}


		/**
		 * Initiate property instances
		 */
		private function initProperties()
		{
			$native = array();

			// Native properties
			foreach (self::$native as $item)
			{
				$native[$item['name']] = new Property(
					$item['name'], true,
					$this->xdata->getAsNode($item)
				);
			}

			$custom = array();

			// Custom properties
			foreach ($this->xdata->getNodes('model/property') as $node)
			{
				$name = $node->getString('@name');

				if (!array_key_exists($name, $native))
					$custom[$name] = new Property($name, false, $node);
			}

			// Remember
			$this->properties_native = $native;
			$this->properties_custom = $custom;
		}
	}
