<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace Fuel\Core\Loader;

use Fuel\Aliases\Loader\Package;

/**
 * Package loader with Closure that translates classname to a path.
 *
 * @package  Fuel\Core
 *
 * @since  2.0.0
 */
class Closure extends Package
{
	/**
	 * @var  \Closure
	 *
	 * @since  2.0.0
	 */
	protected $loader;

	/**
	 * Uses a closure to translate the classname to a path
	 *
	 * @param   string  $fullName
	 * @param   string  $class
	 * @param   string  $basePath
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function classToPath($fullName, $class, $basePath)
	{
		return call_user_func_array($this->loader, array($fullName, $class, $basePath));
	}

	/**
	 * Set the closure that's used as Loader
	 *
	 * @param   \Closure  $loader
	 * @return  Closure
	 *
	 * @since  2.0.0
	 */
	public function setLoader(\Closure $loader)
	{
		$this->loader = $loader;
		return $this;
	}
}
