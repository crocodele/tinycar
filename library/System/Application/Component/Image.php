<?php

namespace Tinycar\System\Application\Component;

use Tinycar\Core\Http\Params;
use Tinycar\System\Application\Component;

class Image extends Component
{

    /**
     * Get image data for the target image
     * @return string|null image data or null on failure
     */
    private function getImageData()
    {
        // We have no custom service defined
        if (!is_string($this->getDataSource()))
            return null;

        // Current URL
        $url = $this->app->getUrlParams();

        // Call target service
        return $this->app->callService($this->getDataSource(), array(
            'url'  => $url->getAll(),
            'app'  => $url->get('app'),
            'view' => $url->get('view'),
            'row'  => $url->get('id'),
        ));
    }

    /**
     * @see Tinycar\System\Application\Component::onModelAction()
     */
    public function onModelAction(Params $params)
    {
        $result = parent::onModelAction($params);

        // Properties
        $result['image_data']   = $this->getImageData();
        $result['image_screen'] = $this->xdata->getString('path/screen');
        $result['image_mobile'] = $this->xdata->getString('path/mobile');

        return $result;
    }
}
