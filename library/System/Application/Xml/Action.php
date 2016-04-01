<?php

    namespace Tinycar\System\Application\Xml;

    use Tinycar\Core\Xml\Data;
    use Tinycar\System\Application\Xml\Section;

    class Action
    {
        protected $data = array();


        /**
         * Initiate class
         * @param array $data initial data
         */
        public function __construct(array $data)
        {
            $this->data = $data;
        }


        /**
         * Load action data from section instance
         * @param object $section Tinycar\System\Application\Xml\Section instance
         * @param object $xdata Tinycar\Core\Xml\Data instance
         * @return object Tinycar\System\Application\Action instance
         */
        public static function loadFromSection(Section $section, Data $xdata)
        {
            // Defaults
            $data = array(
                'target' => 'view',
                'type'   => 'default',
                'label'   => '',
                'service' => $xdata->getString('@service'),
                'link'    => $xdata->getAttributes('link'),
                'toast'   => $xdata->getString('toast'),
                'dialog'  => $xdata->getString('@dialog'),
            );

            // Resolve type
            $type = $xdata->getString('@type');
            $label = $xdata->getString('@label');

            // Custom type
            if (is_string($type))
                $data['type'] = $type;

            // Default label using type
            if (!is_string($label))
                $label = '$locale.action_'.$data['type'];

            // Default toast message for remove
            if (!is_string($data['toast']) && $data['service'] === 'storage.remove')
                $data['toast'] = '$locale.toast_removed_success';

            // Defaults for save
            if ($data['type'] === 'save')
            {
                // Default toast
               	if (!is_string($data['toast']))
               		$data['toast'] = '$locale.toast_saved_success';
            }

            // Resolve locales
            $data['label'] = $section->getStringValue($label);
            $data['toast'] = $section->getStringValue($data['toast']);

            return new self($data);
        }


        /**
         * Get as data structure
         * @return array data properties
         */
        public function getAll()
        {
            return array(
                'type'    => $this->getType(),
                'target'  => $this->get('target'),
                'label'   => $this->getLabel(),
                'link'    => $this->getLink(),
                'service' => $this->getService(),
                'toast'   => $this->get('toast'),
                'dialog'  => $this->get('dialog'),
            );
        }


        /**
         * Get specified data property value
         * @param string $name target data property name
         * @return mixed|null property value or null on failure
         */
        protected function get($name)
        {
            return (array_key_exists($name, $this->data) ?
                $this->data[$name] : null
            );
        }


        /**
         * Get action label
         * @return string|null label or null on failure
         */
        public function getLabel()
        {
            return $this->get('label');
        }


        /**
         * Get action link parameters
         * @return array|null link parameters or null on failure
         */
        public function getLink()
        {
            return $this->get('link');
        }


        /**
         * Get action service name
         * @return string|null service or null on failure
         */
        public function getService()
        {
            return $this->get('service');
        }


        /**
         * Get action type
         * @return string|null type or null on failure
         */
        public function getType()
        {
            return $this->get('type');
        }
    }
