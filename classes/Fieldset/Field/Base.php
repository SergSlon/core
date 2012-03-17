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
	abstract public function set_type($type);

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

	/**
	 * Create output HTML based on this field
	 *
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	abstract public function render();

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

	/**
	 * Magic method for Field to string conversion;
	 *
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	public function __toString()
	{
		return $this->render();
	}
}
