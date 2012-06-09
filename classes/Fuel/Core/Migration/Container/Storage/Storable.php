<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace Fuel\Core\Migration\Container\Storage;

/**
 * Storage interface for migration meta-data drivers
 *
 * @package  Fuel\Core
 *
 * @since  2.0.0
 */
interface Storable
{
	/**
	 * Fetch the migrations that have been ran from the Database
	 *
	 * @param   string  $package
	 * @return  array
	 */
	public function get_migrated($package);

	/**
	 * Marks a migration ID as ran but with status yet unsaved
	 *
	 * @param   string  $package
	 * @param   string  $migration_id
	 * @param   int     $direction
	 * @return  Storable
	 */
	public function set_migrated($package, $migration_id, $direction);

	/**
	 * Runs through all the migrations that were set and have to be saved to/deleted from the database
	 *
	 * @return  Storable
	 */
	public function flush();
}
