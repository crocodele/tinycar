<?php

	use Tinycar\App\Config;
	use Tinycar\App\User;
	use Tinycar\Core\Exception;
	use Tinycar\Core\Http\Params;

	/**
	 * Handle submitted application
     * @param object $params Tinycar\Core\Http\Params instance 
	 * @return bool operation outcome
	 */
	$api->setService('submit', function(Params $params) use ($system, $session) 
	{
		// Get custom data
		$data = $params->getParams('data');
		
		// Target usrename and password
		$username = $data->get('username');
		$password = $data->get('password');

		// Get configuration credentials
		$list = Config::get('LOGIN_CREDENTIALS');
		
		// Find target user
		foreach ($list as $item)
		{
			// Create new instance from source data
			$user = new User($item);
			
			// This is not the target user
			if (!$user->isUsername($username))
				continue;
			
			// Password is invalid
			if (!$user->isPassword($password))
				throw new Exception('login_credentials_invalid');
			
			// Remember user details
			$session->setUser($user);
			return true;
		}
		
		// Login failure
		throw new Exception('login_credentials_invalid');
		return false;
	});