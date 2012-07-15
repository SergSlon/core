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

use Fuel\Kernel\Data\Language;

/**
 * Trait to allow easy access to a Language object
 *
 * @package  Fuel\Core
 *
 * @property  \Fuel\Kernel\Application\Base  $app
 *
 * @since  2.0.0
 */
trait LanguageTrait
{
	/**
	 * @var  string|\Fuel\Kernel\Data\Language  name for the Language or null to use the App's
	 */
	protected $language;

	/**
	 * Fetch a language line or return the language object
	 *
	 * @param   null|string  $value
	 * @param   null|mixed   $default
	 * @return  mixed
	 */
	public function getLanguage($value = null, $default = null)
	{
		if ( ! $this->language instanceof Language)
		{
			/** @var  \Fuel\Kernel\Application\Base  $app  support either $_app or $app property */
			$app = property_exists($this, '_app') ? $this->_app : $this->app;
			if (is_string($this->language))
			{
				try
				{
					$this->language = $app->getObject('Language', $this->language);
				}
				catch (\RuntimeException $e)
				{
					$this->language = $app->forge(array('Language', $this->language));
				}
			}
			elseif (is_null($this->language))
			{
				$this->language = $app->language;
			}
		}

		if (func_num_args() == 0)
		{
			return $this->language;
		}

		return $this->language->get($value, $default);
	}
}
