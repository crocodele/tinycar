<?php

    use Tinycar\App\Config;
    use Tinycar\Core\Exception;
    use Tinycar\Core\Http\Params;
    use Tinycar\System\Application;


    /**
     * Verify that active user has access to these services
     * @param object $params Tinycar\Core\Http\Params instance
     * @return boolean has access
     */
    $api->setService('access', function(Params $params) use ($system)
    {
        return (
            $params->get('app') === Config::get('UI_APP_LOGIN') ||
            $system->hasAuthentication() === false ||
            $system->hasAuthenticated() === true
        );
    });


    /**
     * Show image contents
     * @param object $params Tinycar\Core\Http\Params instance
     *               - string app     target application id
     *               - array  url     source URL parameters
     *               - string service target service name
     * @return bool operation outcome
     * @throws Tinycar\Core\Exception
     */
    $api->setService('image', function(Params $params) use ($system)
    {
        // Get image writer
        $writer = $system->getWriter('Image');

        // Target storage path
        $file = Config::getPath('STORAGE_FOLDER',
            '/uploads/'.$params->get('name')
            );

        // Read from target image
        $writer->loadFromPath($file);
    });


    /**
     * Upload images to temprorary storage
     * @param object $params Tinycar\Core\Http\Params instance
     *               - string app     target application id
     *               - array  url     source URL parameters
     *               - string service target service name
     * @return array list of image id's
     * @throws Tinycar\Core\Exception
     */
    $api->setService('images', function(Params $params) use ($system)
    {
        $result = array();

        // No uploaded files
        if (!array_key_exists('files', $_FILES))
            throw new Exception('upload_images_missing');

        // Process uploads
        foreach ($_FILES['files']['error'] as $key => $error)
        {
            // This upload has failed
            if ($error != UPLOAD_ERR_OK)
                throw new Exception('upload_image_invalid');

            // Not an uploaded file
            if (!is_uploaded_file($_FILES['files']['tmp_name'][$key]))
                throw new Exception('upload_image_invalid');

            // Target filename
            $name = sprintf('%s_%s',
                $params->getString('app'),
                basename($_FILES['files']['tmp_name'][$key])
            );

            // Target storage path
            $path = Config::getPath('STORAGE_FOLDER', '/uploads/'.$name);

            // Failed to move uploaded file
            if (!move_uploaded_file($_FILES['files']['tmp_name'][$key], $path))
                throw new Exception('upload_image_move');

            // Add to results
            $result[] = $name;
        }

        return $result;
    });
