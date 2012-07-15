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

use ArrayAccess;

/**
 * Session driver using PHP native sessions
 *
 * @package  Fuel\Core
 *
 * @since  2.0.0
 */
abstract class Base implements ArrayAccess
{
	/**
	 * Return the Session ID
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	abstract public function getId();

	/**
	 * Rotate the Session ID
	 *
	 * @return  PhpNative
	 *
	 * @since  2.0.0
	 */
	abstract public function rotateId();

	/**
	 * Fetch the Session's name
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	abstract public function getName();

	/**
	 * Get a value or all values from the Session
	 *
	 * @param   string  $key
	 * @param   mixed   $default
	 * @return  array|null
	 *
	 * @since  2.0.0
	 */
	abstract public function get($key = null, $default = null);

	/**
	 * Set a value on the session
	 *
	 * @param   string  $key
	 * @param   mixed   $value
	 * @return  PhpNative
	 *
	 * @since  2.0.0
	 */
	abstract public function set($key, $value);

	/**
	 * Remove a value from the session
	 *
	 * @param   string  $key
	 * @return  PhpNative
	 *
	 * @since  2.0.0
	 */
	abstract public function remove($key);

	/**
	 * Destroy the current Session
	 *
	 * @return  PhpNative
	 *
	 * @since  2.0.0
	 */
	abstract public function destroy();

	/**
	 * Implements ArrayAccess to check variable existence
	 *
	 * @param   string  $key
	 * @return  bool
	 *
	 * @since  2.0.0
	 */
	public function offsetExists($key)
	{
		return array_get_dot_key($key, $_SESSION, $return);
	}

	/**
	 * Implements ArrayAccess to fetch a variable
	 *
	 * @param   string  $key
	 * @return  mixed
	 *
	 * @since  2.0.0
	 */
	public function offsetGet($key)
	{
		return $this->get($key);
	}

	/**
	 * Implements ArrayAccess to set a variable
	 *
	 * @param   string  $key
	 * @param   mixed   $value
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function offsetSet($key, $value)
	{
		$this->set($key, $value);
	}

	/**
	 * Implements ArrayAccess to unset a variable
	 *
	 * @param   string  $key
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function offsetUnset($key)
	{
		$this->remove($key);
	}

	/**
	 * Write & close Session upon object destruction
	 *
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	abstract public function __destruct();
}
