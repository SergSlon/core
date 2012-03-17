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
use Fuel\Core\Fieldset;
use Fuel\Core\Form;
use Fuel\Core\Validation;

/**
 * Interface the field types must extend
 *
 * @package  Fuel\Core
 *
 * @since  2.0.0
 */
abstract class Base implements Form\Inputable, Validation\Validatable
{
	/**
	 * @var  \Fuel\Core\Fieldset\Base  fieldset to which the field belongs
	 *
	 * @since  1.0.0
	 */
	protected $fieldset;

	/**
	 * @var  string  name of the field in the fieldset
	 *
	 * @since  1.0.0
	 */
	protected $name;

	/**
	 * @var  string  label of the field
	 *
	 * @since  1.0.0
	 */
	protected $label;

	/**
	 * @var  string  subtype of the Field
	 *
	 * @since  1.0.0
	 */
	protected $type;

	/**
	 * @var  string  current value of the Field
	 *
	 * @since  1.0.0
	 */
	protected $value;

	/**
	 * @var  array  validations in the form array(rule, additional_args)
	 */
	protected $rules = array();

	/**
	 * @var  array  valid field subtypes
	 */
	protected $_valid_types = array();

	/**
	 * Constructor
	 *
	 * @param  string  $subtype
	 *
	 * @since  1.0.0
	 */
	public function __construct($subtype)
	{
		$this->set_type($subtype);
	}

	/**
	 * Changes the Fieldset of this Field, will also remove from previous parent when set
	 *
	 * @param   \Fuel\Core\Fieldset\Base|null  $fieldset
	 * @return  Base
	 * @throws  \InvalidArgumentException
	 *
	 * @since  2.0.0
	 */
	public function set_fieldset(Fieldset\Base $fieldset = null)
	{
		if (is_null($this->name) and ! is_null($fieldset))
		{
			throw new \InvalidArgumentException('This Field must be given a name before it can be assigned to a Fieldset.');
		}

		if ($this->fieldset)
		{
			$this->fieldset->_remove($this->name);
		}

		$this->fieldset = $fieldset;

		return $this;
	}

	/**
	 * Change the name of the field
	 *
	 * @param   null|string  $name  null is only allowed when it doesn't belong to a fieldset
	 * @return  Base
	 * @throws  \InvalidArgumentException
	 *
	 * @since  2.0.0
	 */
	public function set_name($name = null)
	{
		if (is_null($name) and ! is_null($this->fieldset))
		{
			throw new \InvalidArgumentException('This Field is part of a Fieldset and its name cannot be null.');
		}

		// rename if the Field in the Fieldset
		if ($this->fieldset)
		{
			$this->fieldset->_rename($this->name, $name);
		}

		$this->name = $name;

		return $this;
	}

	/**
	 * Set the subtype of this Field
	 *
	 * @param   string  $type
	 * @return  Base
	 *
	 * @since  1.0.0
	 */
	public function set_type($type)
	{
		if ( ! in_array($type, $this->_valid_types))
		{
			throw new \OutOfBoundsException('Invalid type set on Field.');
		}
		$this->type = $type;

		return $this;
	}

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
	public function set_label($label, $translate = null)
	{
		! is_array($label) and $label = array('value' => $label);

		if ( ! isset($label['value']))
		{
			throw new \InvalidArgumentException('A label must be given either as a string or as the value key in the array.');
		}

		is_null($translate) and $translate = $this->fieldset->_config['translate'];
		$translate and $label['value'] = $this->fieldset->_app->language($label['value'], $label['value']);
		$this->label = $label;

		return $this;
	}

	/**
	 * Change the current value of this field
	 *
	 * @param   mixed  $value
	 * @return  Base
	 *
	 * @since  1.0.0
	 */
	public function set_value($value)
	{
		$this->value = $value;
		return $this;
	}

	public function add_rule($rule, array $args = array())
	{
		$this->rules[] = array($rule, $args);
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

	/**
	 * Prevent parent Fieldset and name from being copied
	 *
	 * @since  2.0.0
	 */
	public function __clone()
	{
		$this->fieldset  = null;
		$this->name      = null;
	}
}
