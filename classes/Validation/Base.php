<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace Fuel\Core\Validation;

/**
 * Validation class
 *
 * @package  Fuel\Core
 *
 * @since  2.0.0
 */
class Base
{
	/**
	 * @var  array  arrays of validation rules per key
	 */
	protected $validations = array();

	/**
	 * @var  array  objects that are searched for validation rules
	 */
	protected $callables = array();

	/**
	 * @var  null|array  null when there's no input, otherwise array
	 */
	protected $input = null;

	/**
	 * @var  null|array  null when nothing has yet been validated, otherwise array
	 */
	protected $validated = null;

	/**
	 * Add a validation $rule for $key with optional $args
	 *
	 * @param   string           $key
	 * @param   callback|string  $rule
	 * @param   array            $args
	 * @return  Base
	 *
	 * @since  2.0.0
	 */
	public function validate($key, $rule, array $args = array()) {}

	/**
	 * Accept outside input to create validations
	 *
	 * @param   Validatable  $validation
	 * @return  Base
	 *
	 * @since  2.0.0
	 */
	public function add(Validatable $validation)
	{
		$validations = $validation->_validation();
		foreach ($validations as $v)
		{
			list($key, $rule, $args) = $v;
			$this->validate($key, $rule, $args);
		}

		return $this;
	}

	/**
	 * Add an object or class in which validation rules may be found
	 *
	 * @param   object|string  $callable
	 * @return  Base
	 *
	 * @since  1.0.0
	 */
	public function add_callable($callable) {}

	/**
	 * Add a model as a callable and attempt to fetch validations from it
	 *
	 * @param   object|string  $model
	 * @param   string         $method  to call to fetch the validations
	 * @return  Base
	 *
	 * @since  2.0.0
	 */
	public function add_model($model, $method = 'get_fields') {}

	/**
	 * Run validation on input, defaults to Request input->param() when input is null
	 *
	 * @param   array|null  $input
	 * @param   array       $extra_callables
	 * @return  bool
	 *
	 * @since  1.0.0
	 */
	public function run(array $input = null, array $extra_callables = array()) {}

	/**
	 * Fetch one or all of the values that were validated as input
	 *
	 * @param   null|string  $name
	 * @param   mixed        $default
	 * @return  mixed
	 * @throws  \RuntimeException
	 *
	 * @since  1.0.0
	 */
	public function input($name = null, $default = null)
	{
		if ( ! is_array($this->input))
		{
			throw new \RuntimeException('No input set, nothing to return.');
		}
		elseif (is_null($name))
		{
			return $this->input;
		}
		elseif ( ! array_key_exists($name, $this->input))
		{
			return $default;
		}

		return $this->input[$name];
	}

	/**
	 * Fetch one or all of the values that were validated successfully
	 *
	 * @param   null|string  $name
	 * @param   mixed        $default
	 * @return  mixed
	 * @throws  \RuntimeException
	 *
	 * @since  1.0.0
	 */
	public function validated($name = null, $default = null)
	{
		if ( ! is_array($this->validated))
		{
			throw new \RuntimeException('Nothing has been validated yet, nothing to return.');
		}
		elseif (is_null($name))
		{
			return $this->validated;
		}
		elseif ( ! array_key_exists($name, $this->validated))
		{
			return $default;
		}

		return $this->validated[$name];
	}
}
