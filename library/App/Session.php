<?php

namespace Tinycar\App;

use Tinycar\App\Config;
use Tinycar\App\User;

class Session
{
    private $user;


    /**
     * Initiate class
     */
    public function __construct()
    {
        session_start();
    }


    /**
     * Clear specified property value
     * @param string $name target property name
     */
    public function clear($name)
    {
        if (array_key_exists($name, $_SESSION))
            unset($_SESSION[$name]);
    }


    /**
     * Destroy current session
     */
    public function destroy()
    {
        // Remove session coookie
        if (ini_get('session.use_cookies'))
        {
            $name   = session_name();
            $params = session_get_cookie_params();

            // Remove cookie reference
            if (array_key_exists($name, $_COOKIE))
                unset($_COOKIE[$name]);

            // Remove cookie
            setcookie(
                $name, '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        // Reset local data
        $this->user = null;

        // Reset global data
        $_SESSION = array();

        // Destroy server data
        session_destroy();
    }


    /**
     * Get specified property value
     * @param string $name target property name
     * @return mixed|null property value or null on failure
     */
    public function get($name)
    {
        return (array_key_exists($name, $_SESSION) ?
            $_SESSION[$name] : null
        );
    }


    /**
     * Get current locale value
     * @return string locale value
     */
    public function getLocale()
    {
        // Get local property
        $name = $this->get('locale');

        // We have a custom locale
        if (is_string($name))
            return $name;

        // Revert to system defualt
        return Config::get('SYSTEM_LOCALE');
    }


    /**
     * Get current user instance
     * @return object Tinycar\App\User instance
     */
    public function getUser()
    {
        // Already resolved
        if (is_object($this->user))
            return $this->user;

        // Get user data
        $data = $this->get('user');
        $data = is_array($data) ? $data : array();

        // Create new instance
        $result = new User($data);

        // Remember
        $this->user = $result;
        return $this->user;
    }


    /**
     * Check if session has specified property
     * @param string $name target property name
     */
    public function has($name)
    {
        return array_key_exists($name, $_SESSION);
    }


    /**
     * Set specified property value
     * @param string $name target property name
     * @param mixed $value new property value
     */
    public function set($name, $value)
    {
        $_SESSION[$name] = $value;
    }


    /**
     * Set new locale value
     * @param string $name new locale name
     */
    public function setLocale($name)
    {
        $this->set('locale', $name);
    }


    /**
     * Set new user data to session
     * @param object $user target Tinycar\App\User instance
     */
    public function setUser(User $user)
    {
        // Rememeber existing session data
        $session = $_SESSION;

        // Remove any existing session data, cookies etc.
        $this->destroy();

        // Start new session with new id
        session_start();
        session_regenerate_id(true);

        // Restore initial session data
        $_SESSION = $session;

        // Remember user data
        $this->set('user', $user->getAll());

        // Remember user instance
        $this->user = $user;
    }
}
