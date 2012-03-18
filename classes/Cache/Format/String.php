<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace Fuel\Core\Cache\Format;

/**
 * Formats Cache content, does nothing but enforce string
 *
 * @package  Fuel\Core
 *
 * @since  1.0.0
 */
class String implements Formatable
{
	/**
	 * Encodes by enforcing string value
	 *
	 * @param   string  $contents
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	public function encode($contents)
	{
		return strval($contents);
	}

	/**
	 * Decodes by enforcing string value
	 *
	 * @param   string  $encoded
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	public function decode($encoded)
	{
		return strval($encoded);
	}
}
