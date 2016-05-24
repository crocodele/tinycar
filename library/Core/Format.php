<?php

namespace Tinycar\Core;

class Format
{


    /**
     * Get color adjustment
     * @param string $color source color
     * @param int $adjust adjustment color (-255 - +255)
     * @return string new color
     */
    public static function adjustColor($color, $adjust)
    {
        // Trim value
        $color = substr($color, 1);

        $result = '#';

        // Adjust R, G and B
        foreach (str_split($color, 2) as $c)
        {
            $c = hexdec($c);
            $c = max(0, min(255, $c + $adjust));
            $c = dechex($c);

            $result .= str_pad($c, 2, '0', STR_PAD_LEFT);
        }

        return $result;
    }
}
