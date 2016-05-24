<?php

namespace Tinycar\System\Application;

use Tinycar\System\Application\Xml\Section;

class SideBar extends Section
{


    /**
     * Get sidebar default width
     * @return int default width
     */
    public function getDefaultWidth()
    {
        return $this->xdata->getInt('width');
    }
}
