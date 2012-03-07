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
 * Trait to allow easy access to a Language object
 *
 * @package  Fuel\Core
 *
 * @since  2.0.0
 */
trait LanguageTrait
{
	/**
	 * @var  \Fuel\Kernel\Data\Language  overwrite to use instead of Application language
	 */
	public $language;

	/**
	 * Fetch a language line or return the language object
	 *
	 * @param   null|string  $value
	 * @param   null|mixed   $default
	 * @return  mixed
	 */
	public function language($value = null, $default = null)
	{
		$language = $this->language ?: $this->app->language;

		if (func_num_args() == 0)
		{
			return $language;
		}

		return $language->get($value, $default);
	}
}
