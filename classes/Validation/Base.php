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

use Fuel\Kernel\Application;

/**
 * Validation class
 *
 * @package  Fuel\Core
 *
 * @since  1.0.0
 */
class Base
{
	/**
	 * @var  \Fuel\Kernel\Application\Base
	 *
	 * @since  2.0.0
	 */
	public $app;

	/**
	 * @var  array  arrays of validation rules per key
	 *
	 * @since  2.0.0
	 */
	protected $validations = array();

	/**
	 * @var  array  objects that are searched for validation rules
	 *
	 * @since  1.0.0
	 */
	protected $callables = array();

	/**
	 * @var  null|array  null when there's no input, otherwise array
	 *
	 * @since  1.0.0
	 */
	protected $input = null;

	/**
	 * @var  null|array  null when nothing has yet been validated, otherwise array
	 *
	 * @since  1.0.0
	 */
	protected $validated = null;

	/**
	 * @var  null|array  null when nothing has yet been validated, otherwise array
	 *
	 * @since  1.0.0
	 */
	protected $errors = null;

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
		$this->app = $app;
	}

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
	public function addValidation($key, $rule, array $args = array())
	{
		$this->validations[$key][] = array($rule, $args);

		return $this;
	}

	/**
	 * Accept outside input to create validations
	 *
	 * @param   Validatable|array  $validations
	 * @param   string             $prefix
	 * @return  Base
	 *
	 * @since  2.0.0
	 */
	public function addValidations($validations, $prefix = '')
	{
		$validations instanceof Validatable
			and $validations = $validations->_validation();

		foreach ($validations as $v)
		{
			list($key, $rule, $args) = $v;
			$this->addValidation($prefix.$key, $rule, $args);
		}

		return $this;
	}

	/**
	 * Add an object or class in which validation rules may be found
	 *
	 * @param   object|string  $callable
	 * @return  Base
	 * @throws  \InvalidArgumentException
	 *
	 * @since  1.0.0
	 */
	public function addCallable($callable)
	{
		if ( ! (is_object($callable) || class_exists($callable)))
		{
			throw new \InvalidArgumentException('Input for add_callable is not a valid object or class.');
		}

		// Prevent having the same class twice in the array, remove to re-add on top if...
		foreach ($this->callables as $key => $c)
		{
			// ...it already exists in callables
			if ($c === $callable)
			{
				unset($this->callables[$key]);
			}
			// ...new object/class extends it or an instance of it
			elseif (is_string($c) and (is_subclass_of($callable, $c) or (is_object($callable) and is_a($callable, $c))))
			{
				unset($this->callables[$key]);
			}
			// but if there's a subclass in there to the new one, put the subclass on top and forget the new
			elseif (is_string($callable) and (is_subclass_of($c, $callable) or (is_object($c) and is_a($c, $callable))))
			{
				unset($this->callables[$key]);
				$callable = $c;
			}
		}

		array_unshift($this->callables, $callable);

		return $this;
	}

	/**
	 * Removes an object from the callables array
	 *
	 * @param   string|object  classname or object
	 * @return  Base
	 *
	 * @since  1.1.0
	 */
	public function removeCallable($class)
	{
		if (($key = array_search($class, $this->callables, true)))
		{
			unset($this->callables[$key]);
		}

		return $this;
	}

	/**
	 * Fetch the callables
	 *
	 * @return  array
	 *
	 * @since  1.1.0
	 */
	public function callables()
	{
		return $this->callables;
	}

	/**
	 * Run validation on input, defaults to Request input->param() when input is null
	 *
	 * @param   array|null  $input
	 * @param   bool        $allowPartial
	 * @param   array       $tempCallables
	 * @return  bool
	 *
	 * @since  1.0.0
	 */
	public function run($input, $allowPartial = false, $tempCallables = array())
	{
		// Backup current state of callables so they can be restored after adding temp callables
		$callableBackup = $this->callables;

		// Add temporary callables, reversed so first ends on top
		foreach (array_reverse($tempCallables) as $tempCallable)
		{
			$this->addCallable($tempCallable);
		}

		$this->validated  = array();
		$this->errors     = array();
		$this->input      = $input;
		foreach ($this->validations as $field => $rules)
		{
			foreach ($rules as $rule)
			{
				list($callback, $args) = $rule;

				// @todo execute rules
				// @todo allow wildcards like blog.*.title where the title is checked for every value in blog array
			}
		}

		// Restore callables
		$this->callables = $callableBackup;

		return empty($this->errors);
	}

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

		return array_key_exists($name, $this->input) ? $this->input[$name] : $default;
	}

	/**
	 * Fetch a specific or all of the errors that were thrown during validation
	 *
	 * @param   null|string  $name
	 * @return  array|Error\Base|null
	 * @throws  \RuntimeException
	 *
	 * @since  1.0.0
	 */
	public function error($name = null)
	{
		if ( ! is_array($this->errors))
		{
			throw new \RuntimeException('No input set, nothing to return.');
		}
		elseif (is_null($name))
		{
			return $this->errors;
		}

		return array_key_exists($name, $this->errors) ? $this->errors[$name] : null;
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

		return array_key_exists($name, $this->validated) ? $this->validated[$name] : $default;
	}
}
