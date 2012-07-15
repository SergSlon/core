<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace Fuel\Core\Notifier;

use Fuel\Kernel\Notifier\Notifiable;

/**
 * Trait to allow access to a Notifier
 *
 * @package  Fuel\Core
 *
 * @since  2.0.0
 */
trait NotifyTrait
{
	/**
	 * @var  string|\Fuel\Kernel\Notifier\Notifiable  name of the notifier to use, defaults to app (= null)
	 */
	public $notifier;

	/**
	 * Notify the notifier
	 *
	 * @param   string       $event
	 * @param   null|object  $source
	 * @param   string       $method  expects __METHOD__ as input
	 * @return  NotifyTrait
	 *
	 * @since  2.0.0
	 */
	public function notify($event, $source = null, $method = '')
	{
		if ( ! $this->notifier instanceof Notifiable)
		{
			/** @var  \Fuel\Kernel\Application\Base  $app  support either $_app or $app property */
			$app = property_exists($this, '_app') ? $this->_app : $this->app;
			if (is_string($this->notifier))
			{
				try
				{
					$this->notifier = $app->getObject('Notifier', $this->notifier);
				}
				catch (\RuntimeException $e)
				{
					$this->notifier = $app->forge(array('Notifier', $this->notifier));
				}
			}
			elseif (is_null($this->notifier))
			{
				$this->notifier = $app->notifier;
			}
		}

		// Notify the notifier
		$this->notifier->notify($event, $source, $method);

		return $this;
	}
}
