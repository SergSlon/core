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
 * Interface the field types must extend
 *
 * @package  Fuel\Core
 *
 * @since  2.0.0
 */
abstract class Base
{
	public function set_label() {}

	public function set_value() {}

	public function populate() {}

	public function render() {}

	public function __toString()
	{
		return $this->render();
	}
}
