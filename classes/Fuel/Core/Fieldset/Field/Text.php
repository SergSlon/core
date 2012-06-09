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
 * Models a Fieldset Field containing text
 *
 * @package  Fuel\Core
 *
 * @since  2.0.0
 */
class Text extends Base
{
	/**
	 * @var  string  single line text input
	 *
	 * @since  2.0.0
	 */
	const T_TEXT = 'text';

	/**
	 * @var  string  multi line text input
	 *
	 * @since  2.0.0
	 */
	const T_AREA = 'textarea';

	/**
	 * @var  string  single line text input with obfuscated characters
	 *
	 * @since  2.0.0
	 */
	const T_PASS = 'password';

	/**
	 * @var  array  valid subtypes for the Text Field object
	 *
	 * @since  2.0.0
	 */
	protected $_valid_types = array(
		self::T_TEXT,
		self::T_PASS,
		self::T_AREA,
	);
}
