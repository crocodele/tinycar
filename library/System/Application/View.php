<?php

	namespace Tinycar\System\Application;

	use Tinycar\App\Manager;
	use Tinycar\Core\Exception;
	use Tinycar\Core\Xml\Data;
	use Tinycar\System\Application;
	use Tinycar\System\Application\Component;
	use Tinycar\System\Application\Model\Property;
	use Tinycar\System\Application\Model\Variable;
	use Tinycar\System\Application\Storage\Record;
	use Tinycar\System\Application\View\Action;
	use Tinycar\System\Application\View\Field;
	use Tinycar\System\Application\View\Tab;

	class View
	{
		protected $actions;
		protected $app;
		protected $component_index = array();
		protected $component_list = array();
		protected $components;
		protected $data_record;
		protected $tabs;
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
		 * Create a component instnace for this view
		 * @param object $xdata Tinycar\Core\Xml\Data instance
		 * @return object Tinycar\System\Application\Component instance
		 */
		public function createComponent(Data $xdata)
		{
			// Current compnent index number
			$index = count($this->component_list);

			// Create new instance
			$result = Component::loadByType(
				$this->system, $this->app, $this, 'cmp-'.$index, $xdata
			);

			// Target id
			$id = $result->getId();

			// Add to list
			$this->component_list[] = $result;
			$this->component_index[$id] = $index;

			// Initiate component for use
			$result->init();

			return $result;
		}


		/**
		 * Get list of action instances
		 * @return array list of Tinycar\System\Application\View\Action instances
		 */
		public function getActions()
		{
			// Already resolved
			if (is_array($this->actions))
				return $this->actions;

			$result = array();

			// Create instances
			foreach ($this->xdata->getNodes('actions/action') as $node)
				$result[] = Action::loadFromView($this, $node);

			// Remember
			$this->actions = $result;
			return $this->actions;
		}


		/**
		 * Map specified data to components and get as model data
		 * @param array $data source data to study
		 * @return array mapped data
		 */
		public function getAsModelData(array $data)
		{
			$result = array();

			// Set new values to components
			foreach ($data as $id => $value)
			{
				// Try to get component
				$component = $this->getComponentById($id);

				// Invalid component
				if (!is_object($component))
					continue;

				// No component data name available
				if (is_null($component->getDataName()))
					continue;

				// Update data value
				$component->setDataValue($value);

				// Pick property value
				$result[$component->getDataName()] = $component->getDataValue();
			}

			return $result;
		}


		/**
		 * Get component instance by id
		 * @param string $id target component id
		 * @return object|null Tinycar\System\Application\Component instance or null on failure
		 */
		public function getComponentById($id)
		{
			// Initiate components
			if (count($this->component_index) === 0)
				$this->getComponents();

			// No such component
			if (!array_key_exists($id, $this->component_index))
				return null;

			// Get component instance
			$index = $this->component_index[$id];
			return $this->component_list[$index];
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
				// Get name
				$property = $this->getStringValue($node->getString('@name'));

				// Not a property instance, ignore
				if (!($property instanceof Property))
					continue;

				// Get desired value
				$value = $this->getStringValue(
					$node->getString('@value')
				);

				// Invalid default value for this property
				if (!$property->isValidValue($value))
				{
					throw new Exception('invalid_property_value', array(
						'name' => $property->getName(),
					));
				}

				// Add to list as typed value
				$result[$property->getName()] = $property->getAsValue($value);
			}

			return $result;
		}


		/**
		 * Get view's data id, if any
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
		 * Get view name
		 * @return string name
		 */
		public function getName()
		{
			$name = $this->xdata->getString('@name');
			return (is_string($name) ? $name : 'default');
		}


		/**
		 * Get view heading
		 * @return string|null heading or null on failure
		 */
		public function getHeading()
		{
			return $this->getStringValue(
				$this->xdata->getString('heading')
			);
		}


		/**
		 * Get view layout type
		 * @return string layout type
		 */
		public function getLayoutType()
		{
			$value = $this->xdata->getString('layout');
			return (is_string($value) ? $value : 'default');
		}


		/**
		 * @see Tinycar\System\Application::getStringValue()
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


		/**
		 * Get list of tab item instances
		 * @return array list of Tinycar\System\Application\View\Tab instances
		 */
		public function getTabs()
		{
			// Already resolved
			if (is_array($this->tabs))
				return $this->tabs;

			$result = array();

			// Create instances
			foreach ($this->xdata->getNodes('tabs/tab') as $node)
				$result[] = new Tab($this, $node);

			// Remember
			$this->tabs = $result;
			return $this->tabs;
		}


		/**
		 * Check to seee if this is the default view
		 * @return bool is default view
		 */
		public function isDefault()
		{
			return ($this->getName() === 'default');
		}

	}
