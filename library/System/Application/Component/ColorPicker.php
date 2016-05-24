<?php

namespace Tinycar\System\Application\Component;

use Tinycar\Core\Http\Params;
use Tinycar\System\Application\View\Field;

class ColorPicker extends Field
{
    private $default_palette = array(
        '#a5ba5a', '#a8d164', '#27ae60', '#1abc9c',
        '#2ecc71', '#27ae60', '#16a085', '#0dccc0',
        '#3498db', '#3498db', '#2980b9', '#0ead9a',
        '#d49e99', '#74525f', '#9b59b6', '#8e44ad',
        '#48647c', '#2c3e50', '#34495e', '#2c3e50',
        '#f1c40f', '#f8c82d', '#fbcf61', '#e3a712',
        '#e67e22', '#fe7c60', '#ff6f6f', '#e74c3c',
        '#d35400', '#d1404a', '#c0392b', '#e84b3a',
        '#c0392b', '#b23f73', '#832d51', '#404148',
        '#ecf0f1', '#bdc3c7', '#95a5a6', '#7f8c8d',
    );


    /**
     * @see Tinycar\System\Application\View\Field::getDataValue()
     */
    public function getDataValue($default = null)
    {
        // Get data value
        $result = parent::getDataValue($default);

        // Invalid data value
        if (!is_string($result) || strlen($result) !== 7)
            return null;

        // Format value
        return strtolower($result);
    }


    /**
     * Get palette colors for the palette
     * @return array list of color hexadecimals
     */
    private function getPaletteColors()
    {
        $result = array();

        // Try to read colors
        foreach ($this->xdata->getNodes('palette/color') as $node)
            $result[] = strtolower($node->getString());

        // No palette colors defined, use default colors
        if (count($result) === 0)
            return $this->default_palette;

        return $result;
    }


    /**
     * @see Tinycar\System\Application\View\Field::onModelAction()
     */
    public function onModelAction(Params $params)
    {
        $result = parent::onModelAction($params);

        // Properties
        $result['colors'] = $this->getPaletteColors();

        return $result;
    }
}
