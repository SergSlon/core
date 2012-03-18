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
 * Formats Cache content using PHP's serialize function
 *
 * @package  Fuel\Core
 *
 * @since  1.0.0
 */
class Serialize implements Formatable
{
	/**
	 * Encodes using serialize()
	 *
	 * @param   mixed  $contents
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	public function encode($contents)
	{
		return serialize($contents);
	}

	/**
	 * Decodes using unserialize()
	 *
	 * @param   string  $encoded
	 * @return  mixed
	 *
	 * @since  1.0.0
	 */
	public function decode($encoded)
	{
		return unserialize($encoded);
	}
}
