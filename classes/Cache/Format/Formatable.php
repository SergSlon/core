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
 * Interface for Cache classes that convert values to saveable format and back
 *
 * @package  Fuel\Core
 *
 * @since  1.0.0
 */
interface Formatable
{
	/**
	 * Encodes the content to a saveable format
	 *
	 * @param   mixed  $content
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	public function encode($content);

	/**
	 * Decodes the a saveable format to original content
	 *
	 * @param   string  $encoded
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	public function decode($encoded);
}
