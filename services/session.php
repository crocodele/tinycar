<?php 

	use Tinycar\App\Config;
	use Tinycar\Core\Http\Params;
	
	
	/**
     * Get or set locale name
     * @param object $params Tinycar\Core\Http\Params instance
     *               - string [name] new locale name
     * @return string current locale
	 */
	$api->setService('locale', function(Params $params) use ($session) 
	{
        // Set new value
        if ($params->has('name'))
       		$session->setLocale($params->get('name'));
         	
       	// Get current value
        return $session->getLocale();
	});
	
	
	/**
	 * Logout current user, if any
	 * @param object $params Tinycar\Core\Http\Params instance
	 * @return bool operation outcome
	 */
	$api->setService('logout', function(Params $params) use ($system, $session)
	{
		// Get login application
		$instance = $system->getApplicationById(Config::get('UI_APP_LOGIN'));
		
		// Execute custom action, if any
		if ($instance->hasCustomService('logout'))
			$instance->callCustomService('logout');
		
		// Remove existing session data
		$session->destroy();
		
		return true;
	});
