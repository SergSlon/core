<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace Fuel\Core\Validation;

/**
 * Classes extending this can be given to the Validation::add() method
 *
 * @package  Fuel\Core
 *
 * @since  2.0.0
 */
interface Validatable
{
	/**
	 * Returns an array with validator closures indexed by the input keys they should match
	 *
	 * @return  array
	 *
	 * @since  2.0.0
	 */
	public function _validation();
}
