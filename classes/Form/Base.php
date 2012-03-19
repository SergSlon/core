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
use Fuel\Kernel\View;

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
	 * @var  array  methods and variables to call for delayed generation
	 */
	protected $contents = array();

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
	public function _set_app(Application\Base $app)
	{
		$this->app = $app;
		$this->config = $app->forge('Object_Config', 'form', $this->config);

		$this->config
			// Set defaults
			->add(array(
				'direct_output' => false,
				'security_clean' => true,
				'parser' => 'Parser.Form',
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

				// General templates
				'tpl_form' => null,
				'tpl_fieldset' => null,
				'tpl_hidden' => null,
				'tpl_label' => null,
				'tpl_raw_html' => null,

				// Specific type templates
				'tpl_button' => null,
				'tpl_checkbox' => null,
				'tpl_password' => null,
				'tpl_radio' => null,
				'tpl_select' => null,
				'tpl_text' => null,
				'tpl_textarea' => null,

				// Subtemplates for select
				'tpl_option' => null,
				'tpl_optgroup' => null,
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
	 * @return  array
	 *
	 * @since  2.0.0
	 */
	protected function input($name, $value = null, array $attributes = array())
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

		if ( ! $this->config['direct_output'])
		{
			$this->contents[] = array('input', $attributes);
			return $this;
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

		return array(
			'tag' => $tag,
			'attributes' => $attributes,
			'content' => $content,
		);
	}

	/**
	 * Templates method output
	 *
	 * @param   string  $tpl
	 * @param   array   $vars
	 * @return  string
	 */
	protected function _template($tpl, array $vars)
	{
		if ($tpl = $this->config['tpl_'.$tpl])
		{
			if ($tpl instanceof View\Viewable)
			{
				foreach ($vars as $k => $v)
				{
					$tpl->{$k} = $v;
				}
				return $tpl;
			}

			$parser = $this->app->get_object($this->config['parser']);
			return $parser->parse_string($tpl, $vars);
		}

		return html_tag($vars['tag'], $vars['attributes'], $vars['content']);
	}

	/**
	 * Accept outside input to create fields
	 *
	 * @param   Inputable  $input
	 * @return  Base
	 *
	 * @since  2.0.0
	 */
	public function add(Inputable $input)
	{
		$inputs = $input->_form();

		foreach ($inputs as $i)
		{
			if ($i['type'] === 'select')
			{
				$this->select($i);
			}
			elseif ( ! empty($i['type']))
			{
				$this->input($i);
			}
		}

		return $this;
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
		$input = $this->input($name, $value, $attributes);

		return $input === $this ? $this : $this->_template('hidden', $input);
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
		$input = $this->input($name, $value, $attributes);

		return $input === $this ? $this : $this->_template('text', $input);
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
		$input = $this->input($name, $value, $attributes);

		return $input === $this ? $this : $this->_template('password', $input);
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
		$input = $this->input($name, $value, $attributes);

		return $input === $this ? $this : $this->_template('textarea', $input);
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
		$input = $this->input($name, $value, $attributes);

		return $input === $this ? $this : $this->_template('button', $input);
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
		$input = $this->input($name, $value, $attributes);

		return $input === $this ? $this : $this->_template('radio', $input);
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
		$input = $this->input($name, $value, $attributes);

		return $input === $this ? $this : $this->_template('checkbox', $input);
	}

	/**
	 * Create a select input
	 * @todo properly refactor this to work with input() method for preprocessing
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

		$input = PHP_EOL;
		foreach ($options as $key => $val)
		{
			if (is_array($val))
			{
				$input .= $this->optgroup($val, $key, $selected, $attributes);
			}
			else
			{
				$input .= $this->option($val, $key, in_array((string) $key, $selected, true), $attributes);
			}
		}

		return html_tag($tag, $attributes, $input);
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
		if ($this->config['prep_value'] and empty($attributes['dont_prep']))
		{
			$value = $this->security_clean($value);
			$label = $this->security_clean($label);
		}

		$attrs = array('value' => $value, 'selected' => $selected);
		isset($attributes['option_attributes']) and $attrs += $attributes['option_attributes'];

		// Allow overwrite of default tag used
		$tag = ! empty($attrs['tag']) ? $attrs['tag'] : 'option';
		unset($attrs['tag']);

		return html_tag($tag, $attrs, $label).PHP_EOL;
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
				$input .= $this->option($label, $option, in_array((string) $option, $selected, true), $attributes);
			}
		}

		$attrs = array();
		isset($attributes['optgroup_attributes']) and $attrs += $attributes['optgroup_attributes'];

		// Allow overwrite of default tag used
		$tag = ! empty($attrs['tag']) ? $attrs['tag'] : 'optgroup';
		unset($attrs['tag']);

		return html_tag($tag, $attrs, $label).PHP_EOL;
	}

	/**
	 * Just unedited HTML
	 *
	 * @param   string  $html
	 * @return  Base
	 *
	 * @since  2.0.0
	 */
	public function raw_html($html)
	{
		if ( ! $this->config['direct_output'])
		{
			$this->contents[] = array('raw_html', $html);
			return $this;
		}

		return $html;
	}

	/**
	 * Set a template
	 *
	 * @param   string  $type
	 * @param   string|\Fuel\Kernel\View\Viewable  $template
	 * @return  Base
	 *
	 * @since  2.0.0
	 */
	public function set_template($type, $template)
	{
		$type = 'tpl_'.$type;
		! $template instanceof View\Viewable and $template = strval($template);
		$this->config[$type] = $template;

		return $this;
	}

	/**
	 * Fetch a template when set, null for no template set
	 *
	 * @param   string  $type
	 * @return  null|string|\Fuel\Kernel\View\Viewable
	 *
	 * @since  2.0.0
	 */
	public function get_template($type)
	{
		return $this->config[$type];
	}

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

			$field = call_user_func_array(array($this, $method), $c);

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
