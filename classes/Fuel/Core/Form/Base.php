<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace Fuel\Core\Form;

use Fuel\Kernel\Application;

/**
 * Form HTML builder class
 *
 * @package  Fuel\Core
 *
 * @since  2.0.0
 */
class Base
{
	/**
	 * @var  \Fuel\Kernel\Application\Base  app that created this request
	 *
	 * @since  2.0.0
	 */
	public $app;

	/**
	 * @var  \Fuel\Kernel\Data\Config
	 */
	public $config;

	/**
	 * Constructor
	 *
	 * @param  array|\Fuel\Kernel\Data\Config  $config
	 *
	 * @since  1.0.0
	 */
	public function __construct($config = null)
	{
		$this->config = $config;
	}

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
		$this->config = $app->forge('Config\Object', 'form', $this->config);

		$this->config
			// Set defaults
			->add(array(
				'formMethod' => 'POST',
				'securityClean' => true,
				'autoId' => true,
				'autoIdPrefix' => 'form_',
				'buttonAsInput' => false,
				'validInputTypes' => array(
					'button', 'checkbox', 'color', 'date', 'datetime',
					'datetime-local', 'email', 'file', 'hidden', 'image',
					'month', 'number', 'password', 'radio', 'range',
					'reset', 'search', 'submit', 'tel', 'text', 'time',
					'url', 'week'
				),
			))
			// Add validators
			->setValidators(array(
				'directOutput' => 'is_bool',
				'autoId' => 'is_bool',
				'securityClean' => 'is_bool',
				'validInputTypes' => 'is_array',
			));
	}

	/**
	 * Create a form open tag
	 *
	 * @param   string|array  action string or array with more tag attribute settings
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	public function formOpen(array $attributes = array())
	{
		$attributes = ! is_array($attributes) ? array('action' => $attributes) : $attributes;

		// If there is still no action set, Form-post
		if( ! array_key_exists('action', $attributes) or $attributes['action'] === null)
		{
			$attributes['action'] = $this->app->getObject('Uri')->main();
		}
		// If not a full URL, create one
		elseif ( ! strpos($attributes['action'], '://'))
		{
			$attributes['action'] = $this->app->getObject('Uri')->create($attributes['action']);
		}

		// Default charset to the one set in the environment
		if ( ! isset($attributes['accept-charset']))
		{
			$attributes['accept-charset'] = strtolower($this->app->env->encoding);
		}

		// If method is empty, use POST
		if ( ! isset($attributes['method']))
		{
			$attributes['method'] = $this->config['formMethod'] ?: 'POST';
		}

		return '<form '.array_to_attr($attributes).'>';
	}

	/**
	 * Create a form close tag
	 *
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	public function formClose()
	{
		return '</form>';
	}

	/**
	 * Create a fieldset open tag
	 *
	 * @param   array   array with tag attribute settings
	 * @param   string  string for the fieldset legend
	 * @return  string
	 *
	 * @since  1.1.0
	 */
	public function fieldsetOpen($attributes = array())
	{
		return '<fieldset ' . array_to_attr($attributes) . '>';
	}

	/**
	 * Create a fieldset close tag
	 *
	 * @return string
	 *
	 * @since  1.1.0
	 */
	public function fieldsetClose()
	{
		return '</fieldset>';
	}

	/**
	 * Create a legend
	 *
	 * @param   string  $legend
	 * @param   array   $attributes
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function legend($legend, array $attributes = array())
	{
		return html_tag('legend', $attributes, $legend);
	}

	public function label($label, $for = null, array $attributes = array()) {}

	/**
	 * Create a form input
	 *
	 * @param   string|array  $name  either fieldname or attributes array
	 * @param   string|null   $value
	 * @param   array         $attributes
	 * @return  array
	 * @throws  \InvalidArgumentException
	 *
	 * @since  1.0.0
	 */
	public function input($name, $value = null, array $attributes = array())
	{
		if (is_array($name))
		{
			$attributes = $name + $attributes;
			! array_key_exists('value', $attributes) and $attributes['value'] = $value;
		}
		else
		{
			$attributes['name'] = (string) $name;
			$attributes['value'] = (string) $value;
		}

		// Default to 'text' when no type was set
		$attributes['type'] = empty($attributes['type']) ? 'text' : $attributes['type'];

		// Check type validity
		if ( ! in_array($attributes['type'], $this->config['validInputTypes']))
		{
			throw new \InvalidArgumentException('"'.$attributes['type'].'" is not a valid input type.');
		}

		// Prepare the value for form output
		if ($this->config['securityClean']
			or (isset($attributes['clean']) and $attributes['clean']))
		{
			$attributes['value'] = $this->securityClean($attributes['value']);
		}
		unset($attributes['clean']);

		// Auto assign an ID when none set
		if (empty($attributes['id']) and $this->config['autoId'] === true)
		{
			$attributes['id'] = $this->config['autoIdPrefix'].$attributes['name'];
		}

		// Allow overwrite of default tag used
		$tag = ! empty($attributes['tag']) ? $attributes['tag'] : 'input';
		unset($attributes['tag']);

		$content = false;
		// Make value the tag content for button and textarea tags
		if (in_array($tag, array('button', 'textarea')))
		{
			$content = $attributes['value'];
			unset($attributes['value']);
			if ($attributes['type'] == $tag)
			{
				unset($attributes['type']);
			}
		}

		return html_tag($tag, $attributes, $content);
	}

	/**
	 * Create a input hidden field
	 *
	 * @param   string|array  $name
	 * @param   string        $value
	 * @param   array         $attributes
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	public function hidden($name, $value = '', array $attributes = array())
	{
		$attributes['type'] = 'hidden';
		return $this->input($name, $value, $attributes);
	}

	/**
	 * Create a input text field
	 *
	 * @param   string|array  $name
	 * @param   string        $value
	 * @param   array         $attributes
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	public function text($name, $value = '', array $attributes = array())
	{
		$attributes['type'] = 'text';
		return $this->input($name, $value, $attributes);
	}

	/**
	 * Create a input password field
	 *
	 * @param   string|array  $name
	 * @param   string        $value
	 * @param   array         $attributes
	 * @return  Base|string
	 *
	 * @since  1.0.0
	 */
	public function password($name, $value = '', array $attributes = array())
	{
		$attributes['type'] = 'password';
		return $this->input($name, $value, $attributes);
	}

	/**
	 * Create a textarea field
	 *
	 * @param   string|array  $name
	 * @param   string        $value
	 * @param   array         $attributes
	 * @return  Base|string
	 *
	 * @since  1.0.0
	 */
	public function textarea($name, $value = '', array $attributes = array())
	{
		$attributes['type'] = 'textarea';
		return $this->input($name, $value, $attributes);
	}

	/**
	 * Create a button
	 *
	 * @param   string|array  $name
	 * @param   string        $value
	 * @param   array         $attributes
	 * @return  Base|string
	 *
	 * @since  1.1.0
	 */
	public function button($name, $value = '', array $attributes = array())
	{
		$this->config['button_as_input'] ? $attributes['type'] = 'button' : $attributes['tag'] = 'button';
		return $this->input($name, $value, $attributes);
	}

	/**
	 * Create a input radio field
	 *
	 * @param   string|array  $name
	 * @param   string        $value
	 * @param   bool|string   $checked
	 * @param   array         $attributes
	 * @return  Base|string
	 *
	 * @since  1.0.0
	 */
	public function radio($name, $value = '', $checked = false, array $attributes = array())
	{
		if (func_num_args() >= 3)
		{
			! is_bool($checked) and $checked = $checked === $value;
			$attributes['checked'] = $checked;
		}

		$attributes['type'] = 'radio';
		return $this->input($name, $value, $attributes);
	}

	/**
	 * Create a input checkbox field
	 *
	 * @param   string|array  $name
	 * @param   string        $value
	 * @param   bool|string   $checked
	 * @param   array         $attributes
	 * @return  Base|string
	 *
	 * @since  1.0.0
	 */
	public function checkbox($name, $value = '', $checked = false, array $attributes = array())
	{
		if (func_num_args() >= 3)
		{
			! is_bool($checked) and $checked = $checked === $value;
			$attributes['checked'] = $checked;
		}

		$attributes['type'] = 'checkbox';
		return $this->input($name, $value, $attributes);
	}

	/**
	 * Create a select input
	 *
	 * @param   string|array  $name
	 * @param   string        $value
	 * @param   array         $options
	 * @param   array         $attributes
	 * @return  Base|string
	 * @throws  \InvalidArgumentException
	 *
	 * @since  1.0.0
	 */
	public function select($name, $value = '', array $options = array(), array $attributes = array())
	{
		! isset($attributes['options']) and $attributes['options'] = (array) $options;
		if ( ! isset($attributes['options']) || ! is_array($attributes['options']))
		{
			throw new \InvalidArgumentException('Select element "'.$attributes['name'].'" is either missing the '
				.'"options" or "options" is not array.');
		}

		! isset($attributes['selected']) and $attributes['selected'] = (array) $value;
		$attributes = $this->input($name, '', $attributes);

		if ($attributes === $this)
		{
			return $this;
		}

		unset($attributes['value']);
		$options = $attributes['options'];
		unset($attributes['options']);

		// Get the selected options then unset it from the array
		$selected = ! isset($attributes['selected']) ? array() : array_map(function($a) {
			return (string) $a;
		}, array_values((array) $attributes['selected']));
		unset($attributes['selected']);

		$optgroupAttributes = isset($attributes['optgroupAttributes']) ? $attributes['optgroupAttributes'] : array();
		$optionAttributes   = isset($attributes['optionAttributes']) ? $attributes['optionAttributes'] : array();

		! isset($optgroupAttributes['optionAttributes'])
			and $optgroupAttributes['optionAttributes'] = $optionAttributes;

		$input = PHP_EOL;
		foreach ($options as $key => $val)
		{
			if (is_array($val))
			{
				$input .= $this->optgroup($val, $key, $selected, $optgroupAttributes).PHP_EOL;
			}
			else
			{
				$input .= $this->option(
					$val,
					$key,
					in_array((string) $key, $selected, true),
					$optionAttributes
				).PHP_EOL;
			}
		}

		// Allow overwrite of default tag used
		$tag = ! empty($attributes['tag']) ? $attributes['tag'] : 'select';
		unset($attributes['tag']);

		return html_tag('tag', $attributes, $input);
	}

	/**
	 * Create an option tag within a select input
	 *
	 * @param   string  $value
	 * @param   string  $label
	 * @param   bool    $selected
	 * @param   array   $attributes
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function option($value, $label, $selected, array $attributes)
	{
		if ($this->config['securityClean']
			or (isset($attributes['clean']) and $attributes['clean']))
		{
			$attributes['value'] = $this->securityClean($attributes['value']);
		}
		unset($attributes['clean']);

		$attributes['value'] = $value;
		$attributes['selected'] = $selected;

		// Allow overwrite of default tag used
		$tag = ! empty($attributes['tag']) ? $attributes['tag'] : 'option';
		unset($attributes['tag']);

		return html_tag($tag, $attributes, $label);
	}

	/**
	 * Create an optgroup tag within a select input
	 *
	 * @param   array   $options
	 * @param   string  $label
	 * @param   array   $selected
	 * @param   array   $attributes
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function optgroup(array $options, $label, $selected, array $attributes)
	{
		$input = '';
		foreach ($options as $option => $label)
		{
			if (is_array($label))
			{
				$input .= $this->optgroup($label, $option, $selected, $attributes);
			}
			else
			{
				$input .= $this->option(
					$label,
					$option,
					in_array((string) $option, $selected, true),
					$attributes['optionAttributes']
				);
			}
		}

		// Allow overwrite of default tag used
		$tag = ! empty($attributes['tag']) ? $attributes['tag'] : 'optgroup';
		unset($attributes['tag']);

		return html_tag($tag, $attributes, $label);
	}

	/**
	 * Use string security on value
	 *
	 * @param   string
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	public function securityClean($value)
	{
		return $this->app->security->clean($value);
	}
}
