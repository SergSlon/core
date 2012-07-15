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

use Fuel\Kernel\DiC\Dependable;

/**
 * Trait to allow easy access to a DiC
 *
 * @package  Fuel\Core
 *
 * @since  2.0.0
 */
trait DiCTrait
{
	/**
	 * @var  string|\Fuel\Kernel\Dic\Dependable  name for the DiC or null to use the App's
	 */
	protected $dic;

	public function getDiC()
	{
		if ( ! $this->dic instanceof Dependable)
		{
			/** @var  \Fuel\Kernel\Application\Base  $app  support either $_app or $app property */
			$app = property_exists($this, '_app') ? $this->_app : $this->app;
			if (is_string($this->dic))
			{
				try
				{
					$this->dic = $app->getObject('Notifier', $this->dic);
				}
				catch (\RuntimeException $e)
				{
					$this->dic = $app->forge(array('Notifier', $this->dic));
				}
			}
			elseif (is_null($this->dic))
			{
				$this->dic = $app->dic;
			}
		}

		return $this->dic;
	}

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
		return $this->getDiC()->getClass($class);
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
		return call_user_func_array(array($this->getDiC(), 'forge'), func_get_args());
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
		return $this->getDiC()->getObject($class, $name);
	}
}
