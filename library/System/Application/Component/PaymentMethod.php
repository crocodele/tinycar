<?php

namespace Tinycar\System\Application\Component;

use Tinycar\Core\Http\Params;
use Tinycar\System\Application\View\Field;

class PaymentMethod extends Field
{


    /**
     * When payment method is selected
     * @param object $params Tinycar\Core\Http\Params instance
     * @return bool operation outcome
     */
    public function onSelectAction(Params $params)
    {
        // Target service
        $service = $this->getNodeString(
            'action/@service', 'paymentmethod.select'
        );

        // Get URL parameters
        $url = $this->app->getUrlParams();

        // Call target service
        return $this->app->callService($service, array(
            'app'  => $this->app->getId(),
            'row'  => $url->get('id'),
            'data' => $this->view->getAsModelData($params->getAll()),
        ));
    }


    /**
     * Get list of message
     * @return array list of messages and their properties
     */
    private function getMessageList()
    {
        $result = array();

        $record = $this->view->getDataRecord();

        foreach ($this->xdata->getNodes('message') as $node)
        {
            $record_value = $record->get($node->getString('@data'));
            $target_value = $node->getNative('@value');

            if ($record_value === $target_value)
            {
                $result[] = array(
                    'label' => $this->getStringValue($node->getString('@label')),
                );
            }
        }

        return $result;
    }


    /**
     * Get payment methods
     * @return array list of methods and their properties
     */
    private function getMethodList()
    {
        $result = array();

        foreach ($this->xdata->getNodes('method') as $node)
        {
            $result[] = array(
                'type'  => $node->getString('@type'),
                'label' => $this->getStringValue($node->getString('@label')),
            );
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
        $result['messages'] = $this->getMessageList();
        $result['methods'] = $this->getMethodList();

        return $result;
    }
}
