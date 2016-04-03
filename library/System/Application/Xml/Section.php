<?php

	namespace Tinycar\System\Application\Xml;

	use Tinycar\App\Manager;
	use Tinycar\Core\Exception;
	use Tinycar\Core\Xml\Data;
	use Tinycar\System\Application;
	use Tinycar\System\Application\Component;
	use Tinycar\System\Application\Model\Property;
	use Tinycar\System\Application\Model\Variable;
	use Tinycar\System\Application\Storage\Record;
	use Tinycar\System\Application\Xml\Action;


	class Section
	{
		protected $actions;
		protected $app;
		protected $components;
		protected $data_record;
		protected $system;
		protected $xdata;


		/**
		 * Initiate class
		 * @param object $system Tinycar\App\System instance
		 * @param object $app Tinycar\System\Application instance
		 * @param object $xdata Tinycar\Core\Xml\Data instance
		 */
		public function __construct(Manager $system, Application $app, Data $xdata)
		{
			$this->app = $app;
			$this->xdata = $xdata;
			$this->system = $system;
		}


		/**
		 * Create a component instance for this section
		 * @param object $xdata Tinycar\Core\Xml\Data instance
		 * @return object Tinycar\System\Application\Component instance
		 */
		public function createComponent(Data $xdata)
		{
			return $this->app->createComponent($this, $xdata);
		}


		/**
		 * Get list of action instances
		 * @return array list of Tinycar\System\Application\Xml\Action instances
		 */
		public function getActions()
		{
			// Already resolved
			if (is_array($this->actions))
				return $this->actions;

			$result = array();

			// Create instances
			foreach ($this->xdata->getNodes('actions/action') as $node)
				$result[] = Action::loadFromSection($this, $node);

			// Remember
			$this->actions = $result;
			return $this->actions;
		}


		/**
		 * Get component instance by id
		 * @param string $id target component id
		 * @return object|null Tinycar\System\Application\Component instance or null on failure
		 */
		public function getComponentById($id)
		{
			// Initiate components
			if (!is_array($this->components))
				$this->getComponents();

			// Try to get component by id
			return $this->app->getComponentById($id);
		}


		/**
		 * Get list of component instances
		 * @return array list of Tinycar\System\Application\Component instances
		 */
		public function getComponents()
		{
			// Already resolved
			if (is_array($this->components))
				return $this->components;

			// Get components data
			$list = $this->xdata->getNodes('component');

			$result = array();

			// Create instances
			foreach ($list as $xdata)
				$result[] = $this->createComponent($xdata);

			// Remember
			$this->components = $result;
			return $this->components;
		}


		/**
		 * Get data defaults from view manifest
		 * @return array data default properties as key-value pairs
		 * @throws Tinycar\Core\Exception
		 */
		protected function getDataDefaults()
		{
			$result = array();

			// Pick data nodes
			foreach ($this->xdata->getNodes('data/property') as $node)
			{
				// Get name and value
				$name = $this->getStringValue($node->getString('@name'));
				$value = $this->getStringValue($node->getString('@value'));

				// Process property instance
				if ($name instanceof Property)
				{
					// Invalid default value for this property
					if (!$name->isValidValue($value))
					{
						throw new Exception('invalid_property_value', array(
							'name' => $name->getName(),
						));
					}

					// Add to list as typed value
					$result[$name->getName()] = $name->getAsValue($value);
				}
				// Simple, untyped value
				else
				{
					$result[$name] = $value;
				}
			}

			return $result;
		}


		/**
		 * Get section's data id, if any
		 * @return string data id or null on failure
		 */
		public function getDataId()
		{
			return $this->getStringValue(
				$this->xdata->getString('@data')
			);
		}


		/**
		 * Get views target data record, if any
		 * @return object Tinycar\System\Application\Storage\Record instance
		 */
		public function getDataRecord()
		{
			// Already resolved
			if (!is_null($this->data_record))
				return $this->data_record;

			// Target data id
			$id = $this->getDataId();

			$result = null;

			// We have a custom id defined
			if (!is_null($id))
			{
				// Get application services
				$services = $this->app->getServices();

				// We have a service for retrieving a single
				// row, let's use that to collect data
				if ($services->hasService('storage.row'))
				{
					// Try to get record data
					$data = $services->callService('storage.row', array(
						'app' => $this->app->getId(),
						'row' => $id,
					));

					// Create record instance from provided data
					if (is_array($data))
						$result = Record::loadFromCustomData($data);

					// Revert to an empty instance
					else
						$result = new Record(array());
				}
				else
				{
					// Try to get target row by id
					$result = $this->app->getRowQuery()->id($id);

					// Revert to an empty instance
					if (is_null($result))
						$result = new Record(array());
				}
			}
			// Create an instance from default data, if any
			else
			{
				$result = new Record(
					$this->getDataDefaults()
				);
			}

			// Remember
			$this->data_record = $result;
			return $this->data_record;
		}


		/**
		 * Get section heading
		 * @return string|null heading or null on failure
		 */
		public function getHeading()
		{
			return $this->getStringValue(
				$this->xdata->getString('heading')
			);
		}


		/**
		 * @see Tinycar\System\Application\Xml\Section::getStringValue()
		 */
		public function getStringValue($source)
		{
			// Try to load variable instnace
			$variable = Variable::loadByString($source);

			// Data record's property value
			if (!is_null($variable) && $variable->getType() === '$data')
			{
				$record = $this->getDataRecord();

				return $variable->getAsValue(
					$record->get($variable->getProperty())
				);
			}

			// Default to application level
			return $this->app->getStringValue($source);
		}
	}
