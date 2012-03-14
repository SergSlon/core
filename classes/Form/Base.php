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
 * Form HTML builder class
 *
 * @package  Fuel\Core
 *
 * @since  2.0.0
 */
class Base
{
	protected $contents = array();

	protected $output = false;

	public function form($action, array $attributes = array()) {}

	public function form_close() {}

	public function fieldset(array $attributes = array()) {}

	public function fieldset_close() {}

	public function label($for, $label = '', array $attributes = array()) {}

	public function text($name, $value = '', array $attributes = array()) {}

	public function password($name, $value = '', array $attributes = array()) {}

	public function textarea($name, $value = '', array $attributes = array()) {}

	public function radio($name, $value = '', $checked = false, array $attributes = array()) {}

	public function checkbox($name, $value = '', $checked = false, array $attributes = array()) {}

	public function select($name, $value = '', array $options = array(), array $attributes = array()) {}

	public function raw_html($html) {}

	public function render() {}

	public function __toString()
	{
		return $this->render();
	}
}
