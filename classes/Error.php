<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace Fuel\Core;

use Fuel\Kernel\Application;
use Exception;

/**
 * Error
 *
 * Deals with showing Exceptions and PHP errors.
 *
 * @package  Fuel\Core
 *
 * @since  2.0.0
 */
class Error extends \Fuel\Kernel\Error
{
	/**
	 * @var  array  add names for the error levels
	 *
	 * @since  1.0.0
	 */
	public $levels = array(
		0                  => 'Error',
		E_ERROR            => 'Error',
		E_WARNING          => 'Warning',
		E_PARSE            => 'Parsing Error',
		E_NOTICE           => 'Notice',
		E_CORE_ERROR       => 'Core Error',
		E_CORE_WARNING     => 'Core Warning',
		E_COMPILE_ERROR    => 'Compile Error',
		E_COMPILE_WARNING  => 'Compile Warning',
		E_USER_ERROR       => 'User Error',
		E_USER_WARNING     => 'User Warning',
		E_USER_NOTICE      => 'User Notice',
		E_STRICT           => 'Runtime Notice'
	);

	/**
	 * Show non fatal error
	 *
	 * @param   \Exception  $e
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function showNonFatal(Exception $e)
	{
		try
		{
			echo $this->app->forge('View',
				$this->app->config->get('errors.viewError', 'error/prod.php'),
				$this->prepareException($e, false),
				null,
				false
			);
		}
		catch (Exception $e)
		{
			parent::showNonFatal($e);
		}
	}

	/**
	 * Show fatal error
	 *
	 * @param   \Exception  $e
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function showFatal(Exception $e)
	{
		$data = $this->prepareException($e, false);
		$data['nonFatal'] = $this->nonFatalCache;

		$view_fatal = $this->app->config->get('errors.viewFatal', 'error/500_prod.php');
		if ($view_fatal)
		{
			exit($this->app->forge('View',
				$this->app->config->get('errors.viewFatal', 'error/500_prod.php'),
				$data,
				null,
				false
			));
		}
		else
		{
			parent::showFatal($e);
		}
	}

	/**
	 * Prepares Exception data for passage to the Viewable
	 *
	 * @param   \Exception  $e
	 * @param   bool        $fatal
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	protected function prepareException(Exception $e, $fatal = true)
	{
		// Convert exception to data array for error View
		$data = array();
		$data['env']         = $this->app->env;
		$data['type']        = get_class($e);
		$data['severity']    = $e->getCode();
		$data['message']     = $e->getMessage();
		$data['filePath']    = $e->getFile();
		$data['errorLine']   = $e->getLine();
		$data['backtrace']   = $e->getTrace();

		// Translate severity int to string
		$data['severity'] = ( ! isset($this->levels[$data['severity']]))
			? $data['severity']
			: $this->levels[$data['severity']];

		// Unset unnecessary backtrace entries
		foreach ($data['backtrace'] as $key => $trace)
		{
			if ( ! isset($trace['file']))
			{
				unset($data['backtrace'][$key]);
			}
			elseif ($trace['file'] == __FILE__)
			{
				unset($data['backtrace'][$key]);
			}
		}

		$data['debugLines'] = $this->app->env->debug()->fileLines($data['filePath'], $data['errorLine'], $fatal);
		$data['origFilePath'] = $data['filePath'];
		$data['filePath'] = $this->app->env->cleanPath($data['filePath']);
		$data['filePath'] = str_replace("\\", "/", $data['filePath']);

		return $data;
	}
}
