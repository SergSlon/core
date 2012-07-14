<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace Fuel\Core\DiC;

/**
 * Trait to allow easy access to a DiC
 *
 * @package  Fuel\Core
 *
 * @property  \Fuel\Kernel\Application\Base  $app
 *
 * @since  2.0.0
 */
trait DiCTrait
{
	/**
	 * @var  \Fuel\Kernel\Dic\Dependable  overwrite to use instead of Application DiC
	 */
	public $dic;

	/**
	 * Translates a classname to the one set in the DiC classes property
	 *
	 * @param   string  $class
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function getClass($class)
	{
		$dic = $this->dic ?: $this->app->dic;
		return $dic->getClass($class);
	}

	/**
	 * Forges a new object for the given class, supporting DI replacement
	 *
	 * @param   string|array  $classname  classname or array($obj_name, $classname)
	 * @return  object
	 *
	 * @since  2.0.0
	 */
	public function forge($classname)
	{
		$dic = $this->dic ?: $this->app->dic;
		return call_user_func_array(array($dic, 'forge'), func_get_args());
	}

	/**
	 * Fetch an instance from the DiC
	 *
	 * @param   string  $class
	 * @param   string  $name
	 * @return  object
	 * @throws  \RuntimeException
	 *
	 * @since  2.0.0
	 */
	public function getObject($class, $name = null)
	{
		$dic = $this->dic ?: $this->app->dic;
		return $dic->getObject($class, $name);
	}
}
