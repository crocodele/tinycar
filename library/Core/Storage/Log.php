<?php

namespace Tinycar\Core\Storage;

use Tinycar\App\Config;

class Log
{


    /**
     * Log given data to custom logfile
     * @param mixed $arg1 argument #1
     * @param mixed $arg2 argument #2
     * ..
     */
    public static function debug()
    {
        // Get given arguments
        $args = func_get_args();

        // Resolve message
        $message = sprintf("%s\n[%s] DEBUG: %s\n",
            str_repeat('-', 50),
            date('Y-m-d H:i:s'),
            print_r($args, true)
        );

        // Write message to custom logfile
        error_log($message, 3, Config::getPath(
            'FOLDER_STORAGE', '/logs/tinycar.log'
        ));
    }
}
