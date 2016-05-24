<?php

namespace Tinycar\System\Application\Component;

use Tinycar\Core\Http\Params;
use Tinycar\System\Application\Component;

class TileGrid extends Component
{


    /**
     * @see Tinycar\System\Application\Compont::onModelAction()
     */
    public function onModelAction(Params $params)
    {
        $result = parent::onModelAction($params);

        // Link properties
        $result['link'] = $this->xdata->getAttributes('link');

        return $result;
    }
}
