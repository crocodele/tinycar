<?php

    namespace Tinycar\System\Application\Component;

    use Tinycar\Core\Http\Params;
    use Tinycar\System\Application\View\Field;

    class RadioList extends Field
    {

        /**
         * Get select list options data
         * @return array options data
         */
        private function getOptionsData()
        {
            $result = array();

            // Go trough options
            foreach ($this->xdata->getNodes('options/option') as $node)
            {
                // Ddefaults
                $item = array(
                    'name'         => $node->getString('@name'),
                    'label'        => $node->getString('@label'),
                    'help'         => $node->getString('help'),
                    'instructions' => $node->getString('instructions'),
                );

                // Translate label
                $item['label'] = $this->getStringValue(
                    $item['label']
                );

                // Translate help
                $item['help'] = $this->getStringValue(
                    $item['help']
                );

                // Translate instructions
                $item['instructions'] = $this->getStringValue(
                    $item['instructions']
                );

                // Add to list
                $result[] = $item;
            }

            return $result;
        }


        /**
         * @see Tinycar\System\Application\View\Field::onModelAction()
         */
        public function onModelAction(Params $params)
        {
            $result = parent::onModelAction($params);

            // Add properties
            $result['layout']  = $this->getNodeString('layout');
            $result['options'] = $this->getOptionsData();

            return $result;
        }
    }