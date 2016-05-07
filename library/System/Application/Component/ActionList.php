<?php

    namespace Tinycar\System\Application\Component;

    use Tinycar\Core\Http\Params;
    use Tinycar\System\Application\Component;

    class ActionList extends Component
    {


        /**
         * Get options data
         * @return array options data
         */
        private function getOptionsData()
        {
            return (is_string($this->getDataSource()) ?
                $this->getOptionsDataFromService() :
                $this->getOptionsDataFromXml()
            );
        }


        /**
         * Get options data from service
         * @return array options data
         */
        private function getOptionsDataFromService()
        {
            // Current URL
            $url = $this->app->getUrlParams();

            // Call target service
            $data = $this->app->callService($this->getDataSource(), array(
                'url'  => $url->getAll(),
                'app'  => $url->get('app'),
                'view' => $url->get('view'),
                'row'  => $url->get('id'),
            ));

            $result = array();

            // Go through data
            foreach ($data as $row)
            {
                // Defaults
                $item = array(
                    'icon'         => null,
                    'type'         => '',
                    'label'        => null,
                	'link_path'    => null,
                    'link_service' => null,
                    'link_target'  => null,
                    'dialog'       => null,
                	'service'      => null,
                	'toast'        => null,
                );

                // Add custom icon
                if (array_key_exists('icon', $row))
                {
                    if (is_string($row['icon']))
                        $item['icon'] = $row['icon'];
                }

                // Add custom type
                if (array_key_exists('type', $row))
                {
                    if (is_string($row['type']))
                        $item['type'] = $row['type'];
                }

                // Add custom label
                if (array_key_exists('label', $row))
                {
                    if (is_string($row['label']))
                        $item['label'] = $row['label'];
                }

                // Add custom link path
                if (array_key_exists('link_path', $row))
                {
                    if (is_array($row['link_path']) && count($row['link_path']) > 0)
                    	$item['link_path'] = $row['link_path'];
                }

                // Add custom link service name
                if (array_key_exists('link_service', $row) && is_string($row['link_service']))
                    $item['link_service'] = $row['link_service'];

                // Add custom link target
                if (array_key_exists('link_target', $row) && is_string($row['link_target']))
                    $item['link_target'] = $row['link_target'];

                // Add custom dialog
                if (array_key_exists('dialog', $row))
                {
                	if (is_string($row['dialog']))
                		$item['dialog'] = $row['dialog'];
                }

                // Add custom service
                if (array_key_exists('service', $row))
                {
                	if (is_string($row['service']))
                		$item['service'] = $row['service'];
                }

                // Add custom toast message
                if (array_key_exists('toast', $row))
                {
                	if (is_string($row['toast']))
                		$item['toast'] = $row['toast'];
                }

                // Add to list
                $result[] = $item;
            }

            return $result;
        }


        /**
         * Get options data from XML
         * @return array options data
         */
        private function getOptionsDataFromXml()
        {
            $result = array();

            // Go through nodes
            foreach ($this->xdata->getNodes('options/option') as $node)
            {
                $result[] = array(
                    'icon'         => $node->getString('@icon'),
                    'type'         => null,
                    'label'        => $this->getStringValue($node->getString('@label')),
                	'link_path'    => $node->getAttributes('link'),
                    'link_service' => $node->getString('link/service'),
                    'link_target'  => $node->getString('link/service'),
                    'dialog'       => $node->getString('@dialog'),
                	'service'      => $this->getStringValue($node->getString('@service')),
                	'toast'        => null,
                );
            }

            return $result;
        }


        /**
         * @see Tinycar\System\Application\Compont::onModelAction()
         */
        public function onModelAction(Params $params)
        {
            $result = parent::onModelAction($params);

            // Get properties
            $result['title'] = $this->getNodeString('title');
            $result['options'] = $this->getOptionsData();

            return $result;
        }
    }