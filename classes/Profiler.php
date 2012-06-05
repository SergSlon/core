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

use Fuel\Kernel\Environment;

/**
 * Profiler class
 *
 * The Profiler class collects information about your application being run from various sources.
 *
 * @package  Fuel\Core
 *
 * @since  1.0.0
 */
class Profiler
{
	/**
	 * @var  \Fuel\Kernel\Environment
	 *
	 * @since  2.0.0
	 */
	protected $env;

	/**
	 * Magic Fuel method that is the setter for the current Environment
	 *
	 * @param   \Fuel\Kernel\Environment  $env
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function _setEnv(Environment $env)
	{
		$this->env = $env;
	}

	/**
	 * Fetch the time that has elapsed since Environment init
	 *
	 * @return  float
	 *
	 * @since  2.0.0
	 */
	public function timeElapsed()
	{
		return microtime(true) - $this->env->getVar('initTime');
	}

	/**
	 * Fetch the mem usage change since Environment init
	 *
	 * @param   bool  $peak  whether to report the peak usage instead of the current
	 * @return  float
	 *
	 * @since  2.0.0
	 */
	public function memUsage($peak = false)
	{
		$usage = $peak ? memory_get_peak_usage() : memory_get_usage();
		return $usage - $this->env->getVar('initMem');
	}

	/**
	 * Collects observed events from applications or a specific applications
	 *
	 * @param   string|array|null  $apps
	 * @return  array
	 * @throws  \RuntimeException
	 *
	 * @since  2.0.0
	 */
	public function events($apps = null)
	{
		$apps = is_null($apps) ? array_keys($this->env->loader->apps) : (array) $apps;
		$events = array(
			'0.000000000000000' => array('app' => 'ENV', 'event' => 'init'),
			strval(microtime(true) - $this->env->getVar('initTime')) => array('event' => 'NOW'),
		);
		foreach ($apps as $app)
		{
			if ( ! isset($this->env->loader->apps[$app]))
			{
				throw new \RuntimeException('Application "'.$app.'" unknown in Profiler::get_event().');
			}

			$appEvents = $this->env->loader->apps[$app]->notifier()->observed();
			foreach ($appEvents as $timestamp => $event)
			{
				$events[$timestamp] = array('app' => $app, 'event' => $event);
			}
		}
		ksort($events);

		return $events;
	}
}
