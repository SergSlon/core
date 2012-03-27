<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace Fuel\Core\Presenter;
use Fuel\Kernel\Application;
use Classes\View;

/**
 * Presenter
 *
 * Extension of Viewable that allows you to add methods to a View that get executed before
 * it is parsed.
 * This is a reimplementation of ViewModel.
 *
 * @package  Fuel\Core
 *
 * @since  1.0.0
 */
abstract class Base extends View\Base
{
	/**
	 * @var  string|null  method to be run upon the Presenter, nothing will be ran when null
	 *
	 * @since  2.0.0
	 */
	protected $_method;

	/**
	 * Constructor
	 *
	 * @param  array  $data
	 */
	public function __construct($method = 'view', array $data = array())
	{
		$this->_method = $method;

		parent::__construct(null, $data);
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
		parent::_set_app($app);
		$this->before();
	}

	/**
	 * Generates the View path based on the Presenter classname
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function default_path()
	{
		$class = get_class($this);
		if (($pos = strpos($class, 'Presenter\\')) !== false)
		{
			$class = substr($class, $pos + 10);
		}

		return str_replace('\\', '/', strtolower($class));
	}

	/**
	 * Method to do general Presenter setup
	 *
	 * @since  1.0.0
	 */
	public function before() {}

	/**
	 * Default method that'll be run upon the Presenter
	 *
	 * @since  1.0.0
	 */
	abstract public function view();

	/**
	 * Method to do general Presenter finishing up
	 *
	 * @since  1.0.0
	 */
	public function after() {}

	/**
	 * Extend parse() to execute the Presenter methods
	 *
	 * @param null $method
	 * @return string
	 *
	 * @since  1.0.0
	 */
	protected function parse($method = null)
	{
		// Run a specific given method and finish up with after()
		if ($method !== null)
		{
			$this->{$method}();
			$this->after();
		}
		// Run this Presenter's main method, finish up with after() and prevent is from being run again
		elseif (isset($this->_method))
		{
			$this->{$this->_method}();
			$this->_method = null;
			$this->after();
		}

		if ( ! isset($this->_path) and ! isset($this->_template))
		{
			$this->set_filename($this->default_path());
		}

		return parent::parse();
	}
}
