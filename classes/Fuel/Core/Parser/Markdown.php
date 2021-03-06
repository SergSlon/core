<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace Fuel\Core\Parser;

use Fuel\Kernel\Parser\Parsable;
use dflydev\markdown\MarkdownParser;

/**
 * Parses Markdown templates
 *
 * @package  Fuel\Core
 *
 * @since  1.1.0
 */
class Markdown implements Parsable
{
	/**
	 * @var  \dflydev\markdown\MarkdownParser
	 */
	protected $parser;

	public function getExtension()
	{
		return 'md';
	}

	/**
	 * Returns the Parser lib object
	 *
	 * @return  \dflydev\markdown\MarkdownParser
	 *
	 * @since  1.1.0
	 */
	public function getParser()
	{
		! isset($this->parser) and $parser = new MarkdownParser();
		return $this->parser;
	}

	/**
	 * Parses a file using the given variables
	 *
	 * @param   string  $path
	 * @param   array   $data  @todo currently ignored by MD
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function parseFile($path, array $data = array())
	{
		return $this->parseString(file_get_contents($path), $data);
	}

	/**
	 * Parses a given string using the given variables
	 *
	 * @param   string  $string
	 * @param   array   $data  @todo  currently ignored by MD
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function parseString($string, array $data = array())
	{
		return $this->getParser()->transform($string);
	}
}
