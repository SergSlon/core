<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace Fuel\Core\Data;

/**
 * Trait to allow easy access to a Config object
 *
 * @package  Fuel\Core
 *
 * @property  \Fuel\Kernel\Application\Base  $app
 *
 * @since  2.0.0
 */
trait ConfigTrait
{
	/**
	 * @var  \Fuel\Kernel\Data\Config  overwrite to use instead of Application config
	 */
	public $config;

	/**
	 * Fetch a configuration value or return the configuration object
	 *
	 * @param   null|string  $value
	 * @param   null|mixed   $default
	 * @return  mixed
	 */
	public function config($value = null, $default = null)
	{
		$config = $this->config ?: $this->app->getConfig();

		if (func_num_args() == 0)
		{
			return $config;
		}

		return $config->get($value, $default);
	}
}
