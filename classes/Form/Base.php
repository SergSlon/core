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

	protected $contents = array();

	protected $output = false;

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
		$this->app = $app;

		// Check if already created
		try
		{
			$this->config = clone $app->get_object('Config', 'cookie');
		}
		catch (\RuntimeException $e)
		{
			$this->config = $app->forge('Config');
		}

		$this->config
			// Set defaults
			->add(array(
				'direct_output' => false,
				'security_clean' => true,
				'auto_id' => true,
				'auto_id_prefix' => 'form_',
				'button_as_input' => false,
				'valid_input_types' => array(
					'button', 'checkbox', 'color', 'date', 'datetime',
					'datetime-local', 'email', 'file', 'hidden', 'image',
					'month', 'number', 'password', 'radio', 'range',
					'reset', 'search', 'submit', 'tel', 'text', 'time',
					'url', 'week'
				),
			))
			// Add validators
			->validators(array(
				'direct_output' => 'is_bool',
				'auto_id' => 'is_bool',
				'security_clean' => 'is_bool',
				'valid_input_types' => 'is_array',
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
	public function form(array $attributes = array())
	{
		$attributes = ! is_array($attributes) ? array('action' => $attributes) : $attributes;

		// If there is still no action set, Form-post
		if( ! array_key_exists('action', $attributes) or $attributes['action'] === null)
		{
			$attributes['action'] = $this->app->get_object('Uri')->main();
		}
		// If not a full URL, create one
		elseif ( ! strpos($attributes['action'], '://'))
		{
			$attributes['action'] = $this->app->get_object('Uri')->create($attributes['action']);
		}

		// Default charset to the one set in the environment
		if ( ! isset($attributes['accept-charset']))
		{
			$attributes['accept-charset'] = strtolower($this->app->env->encoding);
		}

		// If method is empty, use POST
		if ( ! isset($attributes['method']))
		{
			$attributes['method'] = $this->config['form_method'] ?: 'post';
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
	public function form_close()
	{
		return '</form>';
	}

	/**
	 * Create a fieldset open tag
	 *
	 * @param   array   array with tag attribute settings
	 * @param   string  string for the fieldset legend
	 * @return  string|Base
	 *
	 * @since  1.1.0
	 */
	public function fieldset($attributes = array(), $legend = null)
	{
		if ( ! $this->config['direct_output'])
		{
			$this->contents[] = array('fieldset', $attributes, $legend);
			return $this;
		}

		$fieldset_open = '<fieldset ' . array_to_attr($attributes) . '>';

		! is_null($legend) and $attributes['legend'] = $legend;
		if ( ! empty($attributes['legend']))
		{
			$fieldset_open.= PHP_EOL."<legend>".$attributes['legend']."</legend>";
		}

		return $fieldset_open;
	}

	/**
	 * Create a fieldset close tag
	 *
	 * @return string|Base
	 */
	public function fieldset_close()
	{
		if ( ! $this->config['direct_output'])
		{
			$this->contents[] = array('fieldset_close');
			return $this;
		}

		return '</fieldset>';
	}

	public function label($label, $for = null, array $attributes = array()) {}

	/**
	 * Create a form input
	 *
	 * @param   string|array  $name  either fieldname or attributes array
	 * @param   string        $value
	 * @param   array         $attributes
	 * @return  string|Base
	 *
	 * @since  1.0.0
	 */
	public function input($name, $value = null, array $attributes = array())
	{
		if ( ! $this->config['direct_output'])
		{
			$this->contents[] = array('input', $name, $value, $attributes);
			return $this;
		}

		if (is_array($name))
		{
			$attributes += $name;
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
		if ( ! in_array($attributes['type'], $this->config['valid_input_types']))
		{
			throw new \InvalidArgumentException('"'.$attributes['type'].'" is not a valid input type.');
		}

		// Prepare the value for form output
		if ($this->config['security_clean']
			or (isset($attributes['clean']) and $attributes['clean']))
		{
			$attributes['value'] = $this->security_clean($attributes['value']);
			unset($attributes['dont_clean']);
		}

		// Auto assign an ID when none set
		if (empty($attributes['id']) and $this->config['auto_id'] === true)
		{
			$attributes['id'] = $this->config['auto_id_prefix'].$attributes['name'];
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

		$output = html_tag($tag, $attributes, $content);

		return $output;
	}

	/**
	 * Create a input hidden field
	 *
	 * @param   string|array  $name
	 * @param   string        $value
	 * @param   array         $attributes
	 * @return  Base|string
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
	 * @return  Base|string
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
			$checked ? $attributes['checked'] = 'checked' : $attributes['checked'] = null;
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
			$checked ? $attributes['checked'] = 'checked' : $attributes['checked'] = null;
		}

		$attributes['type'] = 'checkbox';
		return $this->input($name, $value, $attributes);
	}

	public function select($name, $value = '', array $options = array(), array $attributes = array()) {}

	public function option($value = '', $lable = '', array $attributes = array()) {}

	public function optgroup($value = '', $lable = '', array $attributes = array()) {}

	public function raw_html($html) {}

	/**
	 * Use string security on value
	 *
	 * @param   string
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	public function security_clean($value)
	{
		return $this->app->security->clean($value);
	}

	/**
	 * Renders the contents array into HTML output
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function render()
	{
		$output = '';

		$this->config['direct_output'] = true;

		$pre_render = $this->config['field_pre_render'];
		$post_render = $this->config['field_post_render'];

		foreach ($this->contents as $c)
		{
			$method = array_shift($c);
			$pre_render and list($method, $c) = $pre_render($method, $c);

			$field   = call_user_func_array(array($this, $method), $c);

			$post_render and $field = $post_render($field);

			$output .= $field;
		}

		$this->config['direct_output'] = false;

		return $output;
	}

	/**
	 * PHP magic method to turn this object into a string
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
