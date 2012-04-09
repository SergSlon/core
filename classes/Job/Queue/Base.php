<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace Fuel\Core\Job\Queue;

/**
 * Base implementation of a Job Queue
 *
 * @package  Fuel\Core
 *
 * @since  2.0.0
 */
class Base
{
	/**
	 * @var  \Fuel\Kernel\Application\Base
	 */
	protected $app;

	/**
	 * @var  Storage\Storable
	 */
	protected $storage;
}
