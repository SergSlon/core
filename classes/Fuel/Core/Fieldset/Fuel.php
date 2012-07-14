<?php
/**
 * FieldSet library
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace Fuel\Core\FieldSet;

use Fuel\Kernel\Application;
use Fuel\Core\Form;
use Fuel\Core\Validation;
use Fuel\FieldSet\Base;

/**
 * The FieldSet class models a set of fields from a Model
 *
 * @package  Fuel\Core
 *
 * @since  2.0.0
 */
class Fuel extends Base implements Inputable, Validation\Validatable
{
	/**
	 * @var  \Fuel\Kernel\Application\Base  app that created this request
	 *
	 * @since  2.0.0
	 */
	public $_app;

	/**
	 * @var  \Fuel\Kernel\Data\Config
	 *
	 * @since  2.0.0
	 */
	public $_config;

	/**
	 * Magic Fuel method that is the setter for the current app
	 *
	 * @param   \Fuel\Kernel\Application\Base  $app
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function _setApp(Application\Base $app)
	{
		$this->_app = $app;
		$this->_config = $app->forge('Config\Object', 'fieldset', $this->_config);

		$this->_config
			// Set defaults
			->add(array(
				'translate' => true,
			))
			// Add validators
			->setValidators(array(
				'translate' => 'is_bool',
			));
	}

	/**
	 * Create a field-object according to the given type & subtype
	 *
	 * @param   string  $type
	 * @param   string  $subtype
	 * @return  \Fuel\Fieldset\Field\Base
	 *
	 * @since  2.0.0
	 */
	public function createField($type, $subtype)
	{
		return $this->_app->forge('FieldSet\Field.'.$type, $subtype);
	}

	/**
	 * Implements Inputable interface
	 * Returns a Form instance with inputs generated based on this Fieldset.
	 *
	 * @return  array
	 *
	 * @since  2.0.0
	 */
	public function _form()
	{
		$inputs = array();
		foreach ($this->_fields as $field)
		{
			$inputs += $field->_form();
		}

		return $inputs;
	}

	/**
	 * Implements Validatable interface
	 * Returns a Validation instance with validations generated based on this Fieldset.
	 *
	 * @return  array
	 *
	 * @since  2.0.0
	 */
	public function _validation()
	{
		$validations = array();
		foreach ($this->_fields as $field)
		{
			$validations += $field->_validation();
		}

		return $validations;
	}
}
