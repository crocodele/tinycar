<?php 

	namespace Tinycar\System\Application;

	use Tinycar\App\Manager;
	use Tinycar\Core\Exception;
	use Tinycar\Core\Http\Params;
	use Tinycar\Core\Xml\Data;
	use Tinycar\System\Application;
	use Tinycar\System\Application\View;
	use Tinycar\System\Application\Model\Property;
	
	class Component
	{
		protected $app;
		protected $data_default = false;
		protected $data_name = false;
		protected $data_value;
		protected $data_value_resolved;
		protected $data_property = false;
		protected $data_required;
		protected $id;
		protected $system;
		protected $view;
		protected $xdata;
		
		
		/**
		 * Initiate class
		 * @param object $system Tinycar\App\Manager
		 * @param object $app Tinycar\System\Application instance
		 * @param object $view Tinycar\System\Application\View instance
		 * @param string $id target component id
		 * @param object $xdata Tinycar\Core\Xml\Data instance
		 */
		public function __construct(Manager $system, Application $app, View $view, $id, Data $xdata)
		{
			$this->system = $system;
			$this->app = $app;
			$this->view = $view;
			$this->id = $id;
			$this->xdata = $xdata;
		}
		
		
		/**
		 * Load component by type
		 * @param object $system Tinycar\App\Manager
		 * @param object $app Tinycar\System\Application instance
		 * @param object $view Tinycar\System\Application\View instance
		 * @param string $id target component id
		 * @param object $xdata Tinycar\Core\Xml\Data instance
		 */
		public static function loadByType(Manager $system, Application $app, View $view, $id, Data $xdata)
		{
			// Target type
			$type = $xdata->getString('@type');
			
			// Target class name
			$class_name = 'Tinycar\\System\Application\\Component\\'.$type;

			// Create new instance
			$instance = (class_exists($class_name) ?
				new $class_name($system, $app, $view, $id, $xdata) : 
				new self($system, $app, $view, $id, $xdata)
			);

			return $instance;
		}
		
		
		/**
		 * Call specified component action and return response
		 * @param string $name target action name
		 * @param array [$params] custom action data
		 * @return mixed action response
		 * @throws Tinycar\Core\Exception 
		 */
		public function callAction($name, array $params = array())
		{
			// Target method name
			$method = 'on'.$name.'Action';
			
			// Invalid action name
			if (!method_exists($this, $method))
			{
				throw new Exception('component_action_invalid', array(
					'type' => $this->getTypeName(),
					'name' => $name,
				));
			}
			
			// Call action
			return $this->$method(new Params($params));
		}
		
		
		/**
		 * Get data default value
		 * @return mixed|null default value or null on failure
		 */
		protected function getDataDefault()
		{
			// Already resolved
			if (!is_bool($this->data_default))
				return $this->data_default;
					
			// Try to get data property value
			$result = $this->getStringValue(
				$this->xdata->getString('data/@default')
			);
				
			// Remeber
			$this->data_default = $result;
			return $this->data_default;
		}
		
		
		/**
		 * Action to get source data name
		 * @return string|null name or null on failure
		 */
		public function getDataName()
		{
			// Already resolved
			if (!is_bool($this->data_name))
				return $this->data_name;
			
			// Try to get service property value
			$name = $this->getStringValue(
				$this->xdata->getString('data/@name')
			);
			
			// Property intance
			if ($name instanceof Property)
				$name = $name->getName();
			
			// Remember
			$this->data_name = $name;
			return $this->data_name;
		}
		
		
		/**
		 * Get data provider source
		 * @return mixed  value data value
		 */
		public function getDataSource()
		{
			// Try to get service property value
			$service = $this->xdata->getString('data/@service');
			
			// We have a service
			if (is_string($service))
				return $service;

			return array();
		}
		
		
		/**
		 * Get data provider type
		 * @return string type
		 */
		public function getDataType()
		{
			// We have a service property
			if (is_string($this->xdata->getString('data/@service')))
				return 'service';
			
			// Default
			return 'static';
		}
		
		
		/**
		 * Get component id
		 * @return string|null component id or null on failure
		 */
		public function getId()
		{
			return $this->id;
		}
		
		
		/**
		 * Get model property instance referenced in data
		 * @return object|null Tinycar\System\Application\Model\Property
		 *                     instance or null on failure
		 */
		protected function getDataProperty()
		{
			// Already resolved
			if (!is_bool($this->data_property))
				return $this->data_property;
					
			// Try to get service property instance
			$value = $this->getStringValue(
				$this->xdata->getString('data/@name')
			);
			
			// Resolve value
			$result = ($value instanceof Property ? 
				$value : null
			);

			// Remember
			$this->data_property = $result;
			return $this->data_property;
		}
		
		
		/**
		 * Get active record's property value of for this component
		 * @param mixed [$default] default value to use instead
		 * @return mixed|null property value or null on failure 
		 */
		public function getDataValue($default = null)
		{
			// Already resolved
			if ($this->data_value_resolved === true)
				return $this->data_value;
			
			// Get active data name
			$name = $this->getDataName();
			
			// No data name specified, use default value
			if (is_null($name))
				return $default;
			
			// Get target record instance
			$record = $this->view->getDataRecord();
			
			// Get record instance property value
			$value = $record->get($name);
			
			// No data available, use default vaule
			if (is_null($value))
				return $default;
			
			// Update state
			$this->data_value_resolved = true;
			$this->data_value = $value;
			
			return $this->data_value;
		}
		
		
		/**
		 * @see Tinycar\System\Application\View::getStringValue();
		 */
		public function getStringValue($source)
		{
			return $this->view->getStringValue($source);
		}
		
		
		/**
		 * Get component tab name
		 * @return string tab name or 'default' on failure
		 */
		public function getTabName()
		{
			$type = $this->xdata->getString('@tab');
			return (is_string($type) ? $type : 'default');
		}
		
		
		/**
		 * Get compoennt type label
		 * @return string|null component label or null on failure
		 */
		public function getTypeLabel()
		{
			return $this->getStringValue(
				$this->xdata->getString('label')
			);
		}
		
		
		/**
		 * Get component type name
		 * @return string|null component type or null on failure
		 */
		public function getTypeName()
		{
			return $this->xdata->getString('@type');
		}
		
		
		/**
		 * Check if data for this component is required
		 * @return bool is required
		 */
		public function isDataRequired()
		{
			// Already resolved
			if (is_bool($this->data_required))
				return $this->data_required;
			
			$result = false;
			
			// Get required property
			$required = $this->xdata->getString('data/@required');
			
			// We have a valid value
			if ($required === 'true' || $required === 'false')
				$result = ($required === 'true');
			
			// Try to get value from referenced property
			else
			{
				// Try to get property instance
				$property = $this->getDataProperty();

				// Get value from property
				if (is_object($property))
					$result = $property->isRequired();
			}
			
			// Remember
			$this->data_required = $result;
			return $this->data_required;
		}
		
		
		/**
		 * Initiate component for use
		 */
		public function init()
		{
		}
		
		
		/**
		 * Action to get source data
		 * @param object $params Tinycar\Core\Http\Params instance
		 * @return array source data 
		 */
		public function onDataAction(Params $params)
		{
			// Set fixed properties
			$params->set('app', $this->app->getId());
			
			// Get source data from service
			$result = $this->app->callService(
				$this->getDataSource(), $params->getAll()
			);
			
			return $result;
		}
		
		
		/**
		 * Action to get custom model data
		 * @param object $params Tinycar\Core\Http\Params instance
		 * @return array model data
		 */
		public function onModelAction(Params $params)
		{
			return array(
				'id'            => $this->getId(),
				'type_name'     => $this->getTypeName(),
				'type_label'    => $this->getTypeLabel(),
				'data_name'     => $this->getDataName(),
				'data_type'     => $this->getDataType(),
				'data_source'   => $this->getDataSource(),
				'data_value'    => $this->getDataValue($this->getDataDefault()),
				'tab_name'      => $this->getTabName(),
			);
		}
		
		
		/**
		 * Set new fixed data value for this component
		 * @param mixed $data new data
		 */
		public function setDataValue($value)
		{
			$this->data_value_resolved = true;
			$this->data_value = $value;
		}
	}
	