<?php
/**
 * FieldSet library
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace Fuel\FieldSet\Field;

use Fuel\Core\FieldSet;
use Fuel\Core\Validation;
use Fuel\FieldSet\Field\Base;

/**
 * Interface the field types must extend
 *
 * @package  Fuel\Core
 *
 * @since  2.0.0
 */
abstract class Fuel extends Base implements FieldSet\Inputable, Validation\Validatable
{
	/**
	 * @var  \Fuel\Core\FieldSet\Fuel  fieldset to which the field belongs
	 *
	 * @since  1.0.0
	 */
	protected $fieldSet;

	/**
	 * Set label and translate when enabled, through config or by second param
	 *
	 * @param   string|array  $label
	 * @param   null|bool     $translate
	 * @return  Base
	 * @throws  \InvalidArgumentException
	 *
	 * @since  1.0.0
	 */
	public function setLabel($label, $translate = null)
	{
		! is_array($label) and $label = array('value' => $label);

		if ( ! isset($label['value']))
		{
			throw new \InvalidArgumentException('A label must be given either as a string or as the value key in the array.');
		}

		is_null($translate) and $translate = $this->fieldSet->_config['translate'];
		$translate and $label['value'] = $this->fieldSet->_app->getLanguage($label['value'], $label['value']);
		$this->label = $label;

		return $this;
	}

	/**
	 * Export fields into an array for the Form class
	 *
	 * @return  array
	 *
	 * @since  2.0.0
	 */
	public function _form()
	{
		// Create the label element
		$label = array(
			'label',
			array(
				'value' => $this->label,
			),
		);

		// Create the input element
		$field = array(
			'input',
			array(
				'name' => $this->name,
				'type' => $this->type,
				'value' => $this->value,
			),
		);

		// @todo attributes, id, for, etc...
		// @todo add support for a closure processing the arrays before they are returned

		return array($label, $field);
	}

	/**
	 * Export fields into an array for the Validation class
	 *
	 * @return  array
	 *
	 * @since  2.0.0
	 */
	public function _validation()
	{
		$rules = $this->rules;
		foreach ($rules as $rule)
		{
			count($rule) < 3 and array_unshift($rule, $this->name);
		}

		return array($rules);
	}
}
