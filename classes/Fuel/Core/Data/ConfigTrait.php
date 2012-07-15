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

use Fuel\Kernel\Data\Config;

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
	 * @var  string|\Fuel\Kernel\Data\Config  name for the Config or null to use the App's
	 */
	protected $config;

	/**
	 * Fetch a configuration value or return the configuration object
	 *
	 * @param   null|string  $value
	 * @param   null|mixed   $default
	 * @return  mixed
	 */
	public function getConfig($value = null, $default = null)
	{
		if ( ! $this->config instanceof Config)
		{
			/** @var  \Fuel\Kernel\Application\Base  $app  support either $_app or $app property */
			$app = property_exists($this, '_app') ? $this->_app : $this->app;
			if (is_string($this->config))
			{
				try
				{
					$this->config = $app->getObject('Config', $this->config);
				}
				catch (\RuntimeException $e)
				{
					$this->config = $app->forge(array('Config', $this->config));
				}
			}
			elseif (is_null($this->config))
			{
				$this->config = $app->config;
			}
		}

		if (func_num_args() == 0)
		{
			return $this->config;
		}

		return $this->config->get($value, $default);
	}
}
