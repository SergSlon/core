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
use Fuel\Kernel\Application;
use Fuel\Core\Form;
use Fuel\Core\Validation;
use ArrayAccess;
use Countable;
use Iterator;

/**
 * The Fieldset class models a set of fields from a Model
 *
 * @package  Fuel\Core
 *
 * @since  2.0.0
 */
class Base implements Form\Inputable, Validation\Validatable, ArrayAccess, Iterator, Countable
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
	 * @var  array  associative array of field objects indexed by their fieldname
	 *
	 * @since  1.0.0
	 */
	protected $_fields = array();

	/**
	 * Constructor
	 *
	 * @param  array|\Fuel\Kernel\Data\Config  $config
	 *
	 * @since  1.0.0
	 */
	public function __construct($config = null)
	{
		$this->_config = $config;
	}

	/**
	 * Magic Fuel method that is the setter for the current app
	 *
	 * @param   \Fuel\Kernel\Application\Base  $app
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function _set_app(Application\Base $app)
	{
		$this->_app = $app;
		$this->_config = $app->forge('Object_Config', 'fieldset', $this->_config);

		$this->_config
			// Set defaults
			->add(array(
				'translate' => true,
			))
			// Add validators
			->validators(array(
				'translate' => 'is_bool',
			));
	}

	/**
	 * Add a new field to the Fieldset and return it
	 *
	 * @param   string             $name
	 * @param   string|Field\Base  $field
	 * @return  Field\Base
	 *
	 * @since  1.0.0
	 */
	public function add($name, $field = 'Text')
	{
		// Field can be an existing instance of Field\Base or the type of Field to create
		if ( ! $field instanceof Field\Base)
		{
			$type     = $field;
			$subtype  = null;
			if ($pos = strpos($type, ':'))
			{
				$subtype  = substr($type, $pos + 1);
				$type     = substr($type, 0, $pos);
			}

			$field = $this->_app->forge('Fieldset_Field_'.$type, $subtype);
		}

		if (array_key_exists($name, $this->_fields))
		{
			throw new \OutOfBoundsException('Name already exists in the Fieldset, cannot overwrite.');
		}
		$this->_fields[$name] = $field;
		$field->set_name($name);
		$field->set_fieldset($this);

		return $field;
	}

	/**
	 * Renames a field inside this fieldset
	 * WARNING: DO NOT USE DIRECTLY, use $fieldset->field($name)->set_name() instead.
	 *
	 * @param   string  $oldname
	 * @param   string  $newname
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function _rename($oldname, $newname)
	{
		$field = $this->_fields[$oldname];
		$pos = array_search($oldname, array_keys($this->_fields));
		unset($this->_fields[$oldname]);

		$this->_fields = array_slice($this->_fields, 0, $pos)
			+ array($newname => $field)
			+ array_slice($this->_fields, $pos);
	}

	/**
	 * Remove a field from this fieldset
	 *
	 * @param   string  $name
	 * @return  Base
	 * @throws  \OutOfBoundsException
	 *
	 * @since  2.0.0
	 */
	public function remove($name)
	{
		if ( ! array_key_exists($name, $this->_fields))
		{
			throw new \OutOfBoundsException('Field with name "'.$name.'" does not exist in this fieldset, cannot delete.');
		}

		$this->_fields[$name]->set_fieldset(null);
		unset($this->_fields[$name]);

		return $this;
	}

	/**
	 * Remove a field from this fieldset
	 * WARNING: DO NOT USE DIRECTLY, use $fieldset->remove_field() instead.
	 *
	 * @param   string  $name
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function _remove($name)
	{
		unset($this->_fields[$name]);
	}

	/**
	 * Fetch a specific Field object from this Fieldset
	 *
	 * @param   string  $name
	 * @return  Field\Base
	 * @throws  \OutOfBoundsException
	 *
	 * @since  1.0.0
	 */
	public function get($name)
	{
		if ( ! array_key_exists($name, $this->_fields))
		{
			throw new \OutOfBoundsException('Field with name "'.$name.'" does not exist in this fieldset, cannot delete.');
		}

		return $this->_fields[$name];
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

	/**
	 * Implements Countable interface
	 *
	 * @return  int
	 *
	 * @since  2.0.0
	 */
	public function count()
	{
		return count($this->_fields);
	}

	/**
	 * Implements ArrayAccess interface
	 *
	 * @param   string|int  $offset
	 * @return  bool
	 *
	 * @since  2.0.0
	 */
	public function offsetExists($offset)
	{
		return isset($this->_fields[$offset]);
	}

	/**
	 * Implements ArrayAccess interface, maps to get() method
	 *
	 * @param   string|int  $offset
	 * @return  Field\Base
	 *
	 * @since  2.0.0
	 */
	public function offsetGet($offset)
	{
		return $this->get($offset);
	}

	/**
	 * Implements ArrayAccess interface, maps to add() method
	 *
	 * @param   string|int  $offset
	 * @param   Field\Base  $value
	 * @return  void
	 * @throws  \InvalidArgumentException
	 *
	 * @since  2.0.0
	 */
	public function offsetSet($offset, $value)
	{
		if ( ! $value instanceof Field\Base)
		{
			throw new \InvalidArgumentException('Direct setting only allowed with Field object instances.');
		}

		$this->add($offset, $value);
	}

	/**
	 * Implements ArrayAccess interface, maps to remove() method
	 *
	 * @param   string|int  $offset
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function offsetUnset($offset)
	{
		$this->remove($offset);
	}

	/**
	 * Implements Iterator interface
	 *
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function rewind()
	{
		reset($this->_fields);
	}

	/**
	 * Implements Iterator interface
	 *
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function next()
	{
		next($this->_fields);
	}

	/**
	 * Implements Iterator interface
	 *
	 * @return  Field\Base
	 *
	 * @since  2.0.0
	 */
	public function current()
	{
		return current($this->_fields);
	}

	/**
	 * Implements Iterator interface
	 *
	 * @return  string|int
	 *
	 * @since  2.0.0
	 */
	public function key()
	{
		return key($this->_fields);
	}

	/**
	 * Implements Iterator interface
	 *
	 * @return  bool
	 *
	 * @since  2.0.0
	 */
	public function valid()
	{
		return $this->key() !== null;
	}

	/**
	 * Check if a field exists using the ArrayAccessInterface
	 *
	 * @param   string  $name
	 * @return  Field\Base
	 *
	 * @since  2.0.0
	 */
	public function __isset($name)
	{
		return $this->offsetExists($name);
	}

	/**
	 * Fetches an existing Field using the ArrayAccess interface
	 *
	 * @param   string  $name
	 * @return  Field\Base
	 *
	 * @since  2.0.0
	 */
	public function __get($name)
	{
		if (strncmp($name, '_', 1) === 0)
		{
			throw new \OutOfBoundsException('Cannot access protected object properties.');
		}

		return $this->offsetGet($name);
	}

	/**
	 * Create a new Field using the ArrayAccess interface
	 *
	 * @param   string             $name
	 * @param   string|Field\Base  $field
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function __set($name, $field)
	{
		if (strncmp($name, '_', 1) === 0)
		{
			throw new \OutOfBoundsException('Cannot set protected object properties.');
		}

		$this->offsetSet($name, $field);
	}

	/**
	 * Removes a field using the ArrayAccess interface
	 *
	 * @param   string  $name
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function __unset($name)
	{
		$this->offsetUnset($name);
	}
}
