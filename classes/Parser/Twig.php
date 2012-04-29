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
use Fuel\Kernel\Application;
use Fuel\Kernel\Parser\Parsable;
use Exception;
use Twig_Environment;
use Twig_Loader_Filesystem;
use Twig_Loader_String;

/**
 * Twig template Parser.
 *
 * @package  Fuel\Core
 *
 * @since  1.1.0
 */
class Twig implements Parsable
{
	/**
	 * @var  \Fuel\Kernel\Application\Base  app that created this request
	 *
	 * @since  2.0.0
	 */
	protected $app;

	/**
	 * @var  \Twig_Environment
	 *
	 * @since  1.1.0
	 */
	protected $parser;

	/**
	 * @var  \Twig_Loader_String
	 *
	 * @since  2.0.0
	 */
	protected $loader_string;

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
	}

	/**
	 * Returns the expected file extension
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function extension()
	{
		return 'twig';
	}

	/**
	 * Returns the Parser lib object
	 *
	 * @return  \Twig_Environment
	 *
	 * @since  1.1.0
	 */
	public function parser()
	{
		// @todo replace this with config
		! isset($this->parser) and $this->parser = new Twig_Environment(null, array(
			'debug'                => false,
			'charset'              => 'utf-8',
			'base_template_class'  => 'Twig_Template',
			'cache'                => $this->app->config('cache.path').'_twig/',
			'auto_reload'          => true,
			'strict_variables'     => false,
			'autoescape'           => false,
			'optimizations'        => -1,
		));
		return $this->parser;
	}

	/**
	 * Parses a file using the given variables
	 *
	 * @param   string  $path
	 * @param   array   $data
	 * @return  string
	 * @throws  \Exception
	 *
	 * @since  2.0.0
	 */
	public function parse_file($path, array $data = array())
	{
		// Extract View name/extension (ex. "template.twig")
		$view_name = pathinfo($path, PATHINFO_BASENAME);

		// Use Twig filesystem loader
		$this->parser()->setLoader(new Twig_Loader_Filesystem(array(pathinfo($path, PATHINFO_DIRNAME))));

		try
		{
			return $this->parser()->loadTemplate($view_name)->render($data);
		}
		catch (Exception $e)
		{
			// Delete the output buffer & re-throw the exception
			ob_end_clean();
			throw $e;
		}
	}

	/**
	 * Parses a given string using the given variables
	 *
	 * @param   string  $template
	 * @param   array   $data
	 * @return  string
	 * @throws  \Exception
	 *
	 * @since  2.0.0
	 */
	public function parse_string($template, array $data = array())
	{
		try
		{
			$this->loader_string or $this->loader_string = new Twig_Loader_String();
			$this->parser()->setLoader($this->loader_string);
			return $this->parser()->loadTemplate($template)->render($data);
		}
		catch (Exception $e)
		{
			// Delete the output buffer & re-throw the exception
			ob_end_clean();
			throw $e;
		}
	}
}
