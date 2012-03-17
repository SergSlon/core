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
 * Models a Fieldset Field choosing between multiple options
 *
 * @package  Fuel\Core
 *
 * @since  2.0.0
 */
class Options extends Base
{
	/**
	 * @var  string  multiple choice which gets exactly one answer
	 */
	const T_RADIO = 'radio';

	/**
	 * @var  string  multiple choice that gets any number of answers
	 */
	const T_CHECKBOX = 'checkbox';

	/**
	 * @var  string  dropdown or scrollable list which can take exactly one or multiselect
	 */
	const T_SELECT = 'select';

	/**
	 * @var  array  valid subtypes for the Button Field object
	 *
	 * @since  2.0.0
	 */
	protected $_valid_types = array(
		self::T_RADIO,
		self::T_CHECKBOX,
		self::T_SELECT,
	);
}
