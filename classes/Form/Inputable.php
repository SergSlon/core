<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace Fuel\Core\Form;

/**
 * Classes extending this can be given to the Form::add() method
 *
 * @package  Fuel\Core
 *
 * @since  2.0.0
 */
interface Inputable
{
	/**
	 * Returns an array of arrays describing the Form inputs
	 *
	 * @return  array
	 *
	 * @since  2.0.0
	 */
	public function _form();
}
