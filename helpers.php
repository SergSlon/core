<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

/**
 * Takes an array of attributes and turns it into a string for an html tag
 *
 * @param   array  $attr
 * @return  string
 *
 * @since  1.0.0
 */
if ( ! function_exists('array_to_attr'))
{
	function array_to_attr($attr)
	{
		$attr_str = '';

		if ( ! is_array($attr))
		{
			$attr = (array) $attr;
		}

		foreach ($attr as $property => $value)
		{
			// Ignore null and false values
			if (is_null($value) or $value === false)
			{
				continue;
			}

			// If the key is numeric then it must be something like selected="selected"
			if (is_numeric($property))
			{
				$property = $value;
			}
			// or if the value is true than it must be that way as well
			elseif ($value === true)
			{
				$value = $property;
			}

			$attr_str .= $property.'="'.$value.'" ';
		}

		// We strip off the last space for return
		return trim($attr_str);
	}
}

/**
 * Create a XHTML tag
 *
 * @param   string        $tag      The tag name
 * @param   array|string  $attr     The tag attributes
 * @param   string|bool   $content  The content to place in the tag, or false for no closing tag
 * @return  string
 *
 * @since  1.0.0
 */
if ( ! function_exists('html_tag'))
{
	function html_tag($tag, $attr = array(), $content = false)
	{
		$has_content = (bool) ($content !== false and $content !== null);
		$html = '<'.$tag;

		$html .= ( ! empty($attr)) ? ' '.(is_array($attr) ? array_to_attr($attr) : $attr) : '';
		$html .= $has_content ? '>' : ' />';
		$html .= $has_content ? $content.'</'.$tag.'>' : '';

		return $html;
	}
}
