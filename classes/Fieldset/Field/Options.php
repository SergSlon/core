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
	const T_RADIO = 'radio';
	const T_CHECKBOX = 'checkbox';
	const T_SELECT = 'select';
}
