<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace Fuel\Core\Session;

use Fuel\Kernel\Application;

/**
 * Session driver using PHP native sessions
 *
 * @package  Fuel\Core
 *
 * @since  2.0.0
 */
class PhpNative extends Base
{
	/**
	 * Configure session based on Application
	 *
	 * @param   \Fuel\Kernel\Application\Base  $app
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function _setApp(Application\Base $app)
	{
		@session_start();
		session_name($app->getConfig('session.name', 'fuelSession'));
		session_set_cookie_params(
			$app->getConfig('session.lifetime', 7200),
			$app->getConfig('session.path', '/'),
			$app->getConfig('session.domain', '.'.$app->env->input->server->get('http_host')),
			$app->getConfig('session.secure', false),
			$app->getConfig('session.httpOnly', false)
		);
	}

	/**
	 * Return the Session ID
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function getId()
	{
		return session_id();
	}

	/**
	 * Rotate the Session ID
	 *
	 * @return  PhpNative
	 *
	 * @since  2.0.0
	 */
	public function rotateId()
	{
		session_regenerate_id();
		return $this;
	}

	/**
	 * Fetch the Session's name
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function getName()
	{
		return session_name();
	}

	/**
	 * Get a value or all values from the Session
	 *
	 * @param   string  $key
	 * @param   mixed   $default
	 * @return  array|null
	 *
	 * @since  2.0.0
	 */
	public function get($key = null, $default = null)
	{
		if (func_num_args() === 0)
		{
			return $_SESSION;
		}

		return array_get_dot_key($key, $_SESSION, $return) ? $return : $default;
	}

	/**
	 * Set a value on the session
	 *
	 * @param   string  $key
	 * @param   mixed   $value
	 * @return  PhpNative
	 *
	 * @since  2.0.0
	 */
	public function set($key, $value)
	{
		array_set_dot_key($key, $_SESSION, $value);
		return $this;
	}

	/**
	 * Remove a value from the session
	 *
	 * @param   string  $key
	 * @return  PhpNative
	 *
	 * @since  2.0.0
	 */
	public function remove($key)
	{
		$newVal = null;
		array_set_dot_key($key, $_SESSION, $newVal, true);
		return $this;
	}

	/**
	 * Destroy the current Session
	 *
	 * @return  PhpNative
	 *
	 * @since  2.0.0
	 */
	public function destroy()
	{
		session_id() and session_destroy();
		return $this;
	}

	/**
	 * Write & close Session upon object destruction
	 *
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function __destruct()
	{
		session_write_close();
	}
}
