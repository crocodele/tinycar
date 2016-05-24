<?php

namespace Tinycar\App;

use Tinycar\Core\Http\Params;

class Url extends Params
{


    /**
     * Get URL as a string
     * @return string URL
     */
    public function getAsString()
    {
        $result = array();

        foreach ($this->data as $name => $value)
            $result[] = $name.':'.urlencode($value);

        return sprintf(
            'index.php?%s=/%s/',
            Config::get('UI_PATH_PARAM'),
            implode('/', $result)
        );
    }
}
