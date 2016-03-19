<?php 

	namespace Tinycar\App;
	
	use Tinycar\App\Config;
	
	class User
	{
		private $data = array();
		
		
		/**
		 * Initiate class
		 * @param array $data initial data
		 */
		public function __construct(array $data)
		{
			$this->data = $data;
		}
		
		
		/**
		 * Get specified data property value
		 * @param string $name target property name
		 * @return mixed|null property value or null on failure
		 */
		public function get($name)
		{
			return (array_key_exists($name, $this->data) ?
				$this->data[$name] : null
			);
		}
		
		
		/**
		 * Get all data
		 * @return array all user data
		 */
		public function getAll()
		{
			$result = $this->data;
			
			// Don't include the password
			if (array_key_exists('password', $result))
				unset($result['password']);
			
			return $result;
		}
		
		
		/**
		 * Get user id
		 * @return mixed|null id or null on failure
		 */
		public function getId()
		{
			return $this->get('id');
		}
		
		
		/**
		 * Get current password string
		 * @return string|null password or null on failure
		 */
		public function getPassword()
		{
			return $this->get('password');
		}
		
		
		/**
		 * Get current username string
		 * @return string|null username or null on failure
		 */
		public function getUsername()
		{
			return $this->get('username');
		}
		
		
		/**
		 * Check if user instance has a password
		 * @return bool has a password 
		 */
		public function hasPassword()
		{
			return is_string($this->get('password'));
		}
		
		
		/**
		 * Check if this user instance has any data
		 * @return bool is empty
		 */
		public function isEmpty()
		{
			return is_null($this->get('id'));
		}
		
		
		/**
		 * Check if specified string is the password
		 * @param string $password target password
		 * @return bool password matches
		 */
		public function isPassword($password)
		{
			return (strcmp($this->getPassword(), $password) === 0);
		}
		
		
		/**
		 * Check if specified string the username
		 * @param string $username target username
		 * @return bool username matches
		 */
		public function isUsername($username)
		{
			return (strcasecmp($this->getUsername(), $username) === 0);
		}
		
		
		/**
		 * Set specified data property value
		 * @param string $name target property name
		 * @param mixed $value new property value
		 */
		public function set($name, $value)
		{
			$this->data[$name] = $value;
		}
	}
