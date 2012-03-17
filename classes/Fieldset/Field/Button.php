<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace Fuel\Core\Fieldset\Field;

/**
 * Models a Fieldset Field that is a button
 *
 * @package  Fuel\Core
 *
 * @since  2.0.0
 */
class Button extends Base
{
	/**
	 * @var  string  general purpose button
	 */
	const T_BUTTON = 'button';

	/**
	 * @var  string  submit button
	 */
	const T_SUBMIT = 'submit';

	/**
	 * @var  string  reset button
	 */
	const T_RESET = 'reset';

	/**
	 * @var  array  valid subtypes for the Button Field object
	 *
	 * @since  2.0.0
	 */
	protected $_valid_types = array(
		self::T_BUTTON,
		self::T_SUBMIT,
		self::T_RESET,
	);
}
