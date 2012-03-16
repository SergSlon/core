<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace Fuel\Core;
use ArrayAccess;
use Countable;
use Iterator;

/**
 * The Arr class provides a few nice functions for making
 * dealing with arrays easier
 *
 * @package  Fuel\Core
 *
 * @since  1.0.0
 */
class Arr implements ArrayAccess, Iterator, Countable
{
	/**
	 * Forge
	 *
	 * @param   array  $data
	 * @return  Arr
	 */
	public static function forge(array $data = array())
	{
		return new static($data);
	}

	/**
	 * @var  array  keeps the array
	 */
	protected $_data = array();

	/**
	 * Constructor
	 *
	 * @param  array  $data
	 */
	public function __construct(array $data = array())
	{
		$this->set(null, $data);
	}

	/**
	 * Gets a dot-notated key from an array, with a default value if it does
	 * not exist.
	 *
	 * @param   mixed   $key      The dot-notated key or array of keys
	 * @param   string  $default  The default value
	 * @return  mixed
	 *
	 * @since  1.0.0
	 */
	public function get($key, $default = null)
	{
		if (is_null($key))
		{
			return $this->_data;
		}

		if (is_array($key))
		{
			$return = array();
			foreach ($key as $k)
			{
				$return[$k] = $this->get($k, $default);
			}
			return $return;
		}

		if ( ! array_get_dot_key($key, $this->_data, $return))
		{
			return __val($default);
		}

		return $return;
	}

	/**
	 * Set an array item (dot-notated) to the value.
	 *
	 * @param   mixed  $key    The dot-notated key to set or array of keys
	 * @param   mixed  $value  The value
	 * @return  Arr
	 *
	 * @since  1.0.0
	 */
	public function set($key, $value = null)
	{
		if (is_null($key))
		{
			$this->_data = $value;
			return $this;
		}

		if (is_array($key))
		{
			foreach ($key as $k => $v)
			{
				$this->set($k, $v);
			}
		}

		array_set_dot_key($key, $this->_data, $value);
		return $this;
	}

	/**
	 * Array_key_exists with a dot-notated key from an array.
	 *
	 * @param   mixed  $key  The dot-notated key or array of keys
	 * @return  bool
	 *
	 * @since  1.1.0
	 */
	public function key_exists($key)
	{
		return array_get_dot_key($key, $this->_data, $return);
	}

	/**
	 * Unsets dot-notated key from an array
	 *
	 * @param   mixed  $key  The dot-notated key or array of keys
	 * @return  mixed  the deleted value or array of values
	 *
	 * @since  1.1.0
	 */
	public function delete($key)
	{
		if (is_null($key))
		{
			return false;
		}

		if (is_array($key))
		{
			$return = array();
			foreach ($key as $k)
			{
				$return[$k] = $this->delete($k);
			}
			return $return;
		}

		array_set_dot_key($key, $this->_data, $old_value, true);

		return $old_value;
	}

	/**
	 * Converts a multi-dimensional associative array into an array of key => values with the provided field names
	 *
	 * @param   string  $key_field  the field name of the key field
	 * @param   string  $val_field  the field name of the value field
	 * @return  Arr
	 *
	 * @since  1.1.0
	 */
	public function assoc_to_keyval($key_field = null, $val_field = null)
	{
		if (empty($key_field) or empty($val_field))
		{
			return null;
		}

		$output = array();
		foreach ($this->_data as $row)
		{
			if (isset($row[$key_field]) and isset($row[$val_field]))
			{
				$output[$row[$key_field]] = $row[$val_field];
			}
		}

		return $output;
	}

	/**
	 * Converts the given 1 dimensional non-associative array to an associative array.
	 *
	 * The array given must have an even number of elements or an exception will be thrown.
	 *
	 * @return  array|null  the new array or null
	 * @throws  \RangeException
	 *
	 * @since  1.1.0
	 */
	public function to_assoc()
	{
		if (($count = count($this->_data)) % 2 > 0)
		{
			throw new \RangeException('Number of variables must be even.');
		}
		$keys = $vals = array();

		for ($i = 0; $i < $count - 1; $i += 2)
		{
			$keys[] = array_shift($arr);
			$vals[] = array_shift($arr);
		}
		return array_combine($keys, $vals);
	}

	/**
	 * Checks if the given array is an assoc array.
	 *
	 * @return  bool   true if its an assoc array, false if not
	 *
	 * @since  1.1.0
	 */
	public function is_assoc()
	{
		foreach ($this->_data as $key => $unused)
		{
			if ( ! is_int($key))
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Flattens a multi-dimensional associative array down into a 1 dimensional
	 * associative array.
	 *
	 * @param   string  $glue     what to glue the keys together with
	 * @param   bool    $indexed  whether to flatten only associative array's, or also indexed ones
	 * @return  array
	 *
	 * @since  1.1.0
	 */
	public function flatten($glue = ':', $indexed = true)
	{
		$return = array();
		$curr_key = array();
		$flatten = function (&$array, &$curr_key, &$return, $glue, $indexed, $self)
		{
			foreach ($array as $key => &$val)
			{
				$curr_key[] = $key;
				if ((is_array($val) or $val instanceof \Iterator)
					and ($indexed or array_values($val) !== $val))
				{
					$self($val, $curr_key, $return, $glue, false);
				}
				else
				{
					$return[implode($glue, $curr_key)] = $val;
				}
				array_pop($curr_key);
			}
		};
		return $flatten($this->_data, $curr_key, $return, $glue, $indexed, $flatten);
	}

	/**
	 * Flattens a multi-dimensional associative array down into a 1 dimensional
	 * associative array.
	 *
	 * @param   string  $glue   what to glue the keys together with
	 * @param   bool    $reset  whether to reset and start over on a new array
	 * @return  array
	 *
	 * @since  1.1.0
	 */
	public function flatten_assoc($glue = ':', $reset = true)
	{
		return $this->flatten($glue, $reset, false);
	}

	/**
	 * Filters an array on prefixed associative keys.
	 *
	 * @param   string  $prefix         prefix to filter on
	 * @param   bool    $remove_prefix  whether to remove the prefix
	 * @return  array
	 *
	 * @since  1.1.0
	 */
	public function filter_prefixed($prefix = 'prefix_', $remove_prefix = true)
	{
		$return = array();
		foreach ($this->_data as $key => &$val)
		{
			if (preg_match('/^'.$prefix.'/', $key))
			{
				if ($remove_prefix === true)
				{
					$key = preg_replace('/^'.$prefix.'/','',$key);
				}
				$return[$key] = $val;
			}
		}

		return $return;
	}

	/**
	 * Filters an array by an array of keys
	 *
	 * @param   array  $keys     the keys to filter
	 * @param   bool   $remove   if true, removes the matched elements.
	 * @return  array
	 *
	 * @since  1.1.0
	 */
	public function filter_keys($keys, $remove = false)
	{
		$return = array();
		foreach ($keys as $key)
		{
			if (isset($this->_data[$key]))
			{
				$return[$key] = $this->_data[$key];
				if ($remove)
				{
					unset($this->_data[$key]);
				}
			}
		}

		return $return;
	}

	/**
	 * Insert value(s) into an array, mostly an array_splice alias
	 *
	 * @param   array|mixed  $value  the value(s) to insert, arrays needs to be inside an array themselves
	 * @param   int          $pos    the numeric position at which to insert, negative to count from the end backwards
	 * @return  Arr
	 *
	 * @since  1.1.0
	 */
	public function insert($value, $pos)
	{
		if (count($this->_data) < abs($pos))
		{
			throw new \OutOfBoundsException('Position larger than number of elements in array in which to insert.');
		}
		array_splice($original, $pos, 0, $value);

		return $this;
	}

	/**
	 * Insert value(s) into an array before a specific key
	 *
	 * @param   array|mixed  $value  the value(s) to insert, arrays need to be inside an array themselves
	 * @param   string|int   $key    the key before which to insert
	 * @return  Arr
	 * @throws  \OutOfBoundsException
	 *
	 * @since  1.1.0
	 */
	public function insert_before_key($value, $key)
	{
		$pos = array_search($key, array_keys($this->_data));
		if ($pos === false)
		{
			throw new \OutOfBoundsException('Unknown key before which to insert the new value into the array.');
		}

		return $this->insert($value, $pos);
	}

	/**
	 * Insert value(s) into an array after a specific key
	 *
	 * @param   array|mixed  $value  the value(s) to insert, arrays need to be inside an array themselves
	 * @param   string|int   $key    the key after which to insert
	 * @return  Arr
	 * @throws  \OutOfBoundsException
	 *
	 * @since  1.1.0
	 */
	public function insert_after_key($value, $key)
	{
		$pos = array_search($key, array_keys($this->_data));
		if ($pos === false)
		{
			throw new \OutOfBoundsException('Unknown key after which to insert the new value into the array.');
		}

		return $this->insert($value, $pos + 1);
	}

	/**
	 * Insert value(s) into an array after a specific value (first found in array)
	 *
	 * @param   array|mixed  $value   the value(s) to insert, arrays need to be inside an array themselves
	 * @param   string|int   $search  the value after which to insert
	 * @return  Arr
	 * @throws  \OutOfBoundsException
	 *
	 * @since  1.1.0
	 */
	public function insert_after_value($value, $search)
	{
		$key = array_search($search, $this->_data);
		if ($key === false)
		{
			throw new \OutOfBoundsException('Unknown value after which to insert the new value into the array.');
		}

		return $this->insert_after_key($value, $key);
	}

	/**
	 * Replaces key names in an array by names in $replace
	 *
	 * @param   array|string  $replace  key to replace or array containing the replacement keys
	 * @param   string        $new_key  the replacement key
	 * @return  Arr
	 *
	 * @since  1.1.0
	 */
	public function replace_key($replace, $new_key = null)
	{
		is_string($replace) and $replace = array($replace => $new_key);
		if ( ! is_array($replace))
		{
			throw new \InvalidArgumentException('First parameter must be an array or string.');
		}

		foreach ($this->_data as $key => &$value)
		{
			if (array_key_exists($key, $replace))
			{
				$this->_data[$replace[$key]] = $value;
				unset($this->_data[$key]);
			}
			else
			{
				$this->_data[$key] = $value;
			}
		}

		return $this;
	}

	/**
	 * Merge 2 arrays recursively, differs in 2 important ways from array_merge_recursive()
	 * - When there's 2 different values and not both arrays, the latter value overwrites the earlier
	 *   instead of merging both into an array
	 * - Numeric keys that don't conflict aren't changed, only when a numeric key already exists is the
	 *   value added using array_push()
	 *
	 * @param   array  $array*  multiple variables all of which must be arrays
	 * @return  Arr
	 * @throws  \InvalidArgumentException
	 *
	 * @since  1.1.0
	 */
	public function merge($array)
	{
		$merge = function(&$array_1, $array_2, $merge)
		{
			foreach ($array_2 as $k => &$v)
			{
				// numeric keys are appended
				if (is_int($k))
				{
					array_key_exists($k, $array_1) ? array_push($array_1, $v) : $array_1[$k] = $v;
				}
				elseif (is_array($v) and array_key_exists($k, $array_1) and is_array($array_1[$k]))
				{
					$array_1[$k] = $merge($array_1[$k], $v);
				}
				else
				{
					$array_1[$k] = $v;
				}
			}
			return $array_1;
		};

		$arrays = func_get_args();
		foreach ($arrays as &$arr)
		{
			if ( ! is_array($arr))
			{
				throw new \InvalidArgumentException('Arr::merge() - all arguments must be arrays.');
			}

			$this->_data = $merge($this->_data, $arr, $merge);
		}

		return $this;
	}

	/**
	 * Prepends a value with an associative key to an array.
	 * Will overwrite if the value exists.
	 *
	 * @param   string|array  $key    the key or array of keys and values
	 * @param   mixed         $value  the value to prepend
	 * @return  Arr
	 *
	 * @since  1.1.0
	 */
	public function prepend($key, $value = null)
	{
		$this->_data = (is_array($key) ? $key : array($key => $value)) + $this->_data;
		return $this;
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
		return count($this->_data);
	}

	/**
	 * Implements ArrayAccess Interface
	 *
	 * @param   string|int  $offset
	 * @return  bool
	 *
	 * @since  2.0.0
	 */
	public function offsetExists($offset)
	{
		return $this->key_exists($offset);
	}

	/**
	 * Implements ArrayAccess Interface
	 *
	 * @param   string|int  $offset
	 * @return  mixed
	 *
	 * @since  2.0.0
	 */
	public function offsetGet($offset)
	{
		return $this->get($offset);
	}

	/**
	 * Implements ArrayAccess Interface
	 *
	 * @param   string|int  $offset
	 * @param   mixed       $value
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function offsetSet($offset, $value)
	{
		$this->set($offset, $value);
	}

	/**
	 * Implements ArrayAccess Interface
	 *
	 * @param   string|int  $offset
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function offsetUnset($offset)
	{
		$this->delete($offset);
	}

	/**
	 * Implements Iterator Interface
	 *
	 * @return  mixed
	 *
	 * @since  2.0.0
	 */
	public function current()
	{
		return current($this->_data);
	}

	/**
	 * Implements Iterator Interface
	 *
	 * @return  string|int
	 *
	 * @since  2.0.0
	 */
	public function key()
	{
		return key($this->_data);
	}

	/**
	 * Implements Iterator Interface
	 *
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function next()
	{
		next($this->_data);
	}

	/**
	 * Implements Iterator Interface
	 *
	 * @return  mixed
	 *
	 * @since  2.0.0
	 */
	public function rewind()
	{
		return reset($this->_data);
	}

	/**
	 * Implements Iterator Interface
	 *
	 * @return  bool
	 *
	 * @since  2.0.0
	 */
	public function valid()
	{
		return ! is_null(key($this->_data));
	}
}
