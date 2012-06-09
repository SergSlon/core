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
use Fuel\Core\Migration\Container;

/**
 * Storage interface for migration meta-data drivers
 *
 * @package  Fuel\Core
 *
 * @since  2.0.0
 */
class Base implements Storable
{
	/**
	 * @var  \Fuel\Core\Migration\Container\Base  parent container object
	 */
	protected $container;

	/**
	 * @var  array  list of migrations that have ben ran but not been updated in the DB
	 */
	protected $unsaved_migrated = array();

	/**
	 * Constructor
	 *
	 * @param  \Fuel\Core\Migration\Container\Base  $container
	 */
	public function __construct(Container\Base $container)
	{
		$this->container = $container;
	}

	/**
	 * Fetch the migrations that have been ran from the Database
	 *
	 * @param   string  $package
	 * @return  array
	 */
	public function get_migrated($package)
	{
		// @todo make this actually work
		return $this->container->app->get_object('db')
			->select()
			->from($this->container->config->get('table', 'migrations'))
			->where('package', '=', $package)
			->order_by('migration_id', 'ASC')
			->execute()
			->as_array('migration_id', 'migration_id');
	}

	/**
	 * Marks a migration ID as ran but with status yet unsaved
	 *
	 * @param   string  $package
	 * @param   string  $migration_id
	 * @param   int     $direction
	 * @return  Storable
	 */
	public function set_migrated($package, $migration_id, $direction)
	{
		$this->unsaved_migrated[$package][] = array($migration_id => $direction);
	}

	/**
	 * Runs through all the migrations that were set and have to be saved to/deleted from the database
	 *
	 * @return  Storable
	 */
	public function flush()
	{
		foreach ($this->unsaved_migrated as $package => $ids)
		{
			foreach ($ids as $id => $direction)
			{
				// @todo make this actually work
				if ($direction > 0)
				{
					$this->container->app->get_object('db')
						->insert($this->container->config->get('table', 'migrations'))
						->set(array(
							'package' => $package,
							'migration_id' => $id,
						))
						->execute();
				}
				elseif ($direction < 0)
				{
				// @todo make this actually work
					$this->container->app->get_object('db')
						->delete()
						->from($this->container->config->get('table', 'migrations'))
						->where('package', '=', $package)
						->where('migration_id', '=', $id)
						->execute();
				}
			}
		}
	}
}
