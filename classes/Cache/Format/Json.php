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
 * Formats Cache content using PHP's JSON functions
 *
 * @package  Fuel\Core
 *
 * @since  1.0.0
 */
class Json implements Formatable
{
	/**
	 * Encodes using json_encode()
	 *
	 * @param   mixed  $contents
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	public function encode($contents)
	{
		$array = '';
		if (is_array($contents))
		{
			$array = 'a';
		}

		return $array.json_encode($contents);
	}

	/**
	 * Decodes using json_decode()
	 *
	 * @param   string  $encoded
	 * @return  mixed
	 *
	 * @since  1.0.0
	 */
	public function decode($encoded)
	{
		$array = false;
		if (substr($encoded, 0, 1) == 'a')
		{
			$encoded = substr($encoded, 1);
			$array = true;
		}

		return json_decode($encoded, $array);
	}
}
