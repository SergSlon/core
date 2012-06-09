<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace Fuel\Core\Job\Queue\Storage;

/**
 * Interface for Job Queue Storage drivers
 *
 * @package  Fuel\Core
 *
 * @since  2.0.0
 */
interface Storable
{
	public function get_jobs();

	public function save_jobs();
}
