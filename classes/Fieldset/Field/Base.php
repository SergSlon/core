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
	 * @since  2.0.0
	 */
	protected $name;

	/**
	 * @var  string  subtype of the Field
	 *
	 * @since  2.0.0
	 */
	protected $type;

	/**
	 * Constructor
	 *
	 * @param  string  $subtype
	 *
	 * @since  2.0.0
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

	abstract public function set_type();

	public function set_label() {}

	public function set_value() {}

	public function populate() {}

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
