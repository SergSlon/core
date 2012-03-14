<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace Fuel\Core\Fieldset;

/**
 * The Fieldset class models a set of fields from a Model
 *
 * @package  Fuel\Core
 *
 * @since  2.0.0
 */
class Base
{
	/**
	 * @var  array  associative array of field objects indexed by their fieldname
	 */
	protected $fields = array();

	public function add_field() {}

	public function form() {}

	public function validation() {}
}
