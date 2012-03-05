<?php

namespace Fuel\Core\Migration\Container;
use Fuel\Kernel\Application;
use Fuel\Core\Migration;

class Base
{
	/**
	 * @var  \Fuel\Kernel\Application\Base  app that created this request
	 *
	 * @since  2.0.0
	 */
	public $app;

	/**
	 * @var  \Fuel\Kernel\Data\Config
	 */
	public $config;

	/**
	 * @var  array  list of migrations that have been run
	 */
	protected $migrated = array();

	/**
	 * @var  array  list of migrations that have ben ran but not been updated in the DB
	 */
	protected $_unsaved_migrated = array();

	/**
	 * @var  array  list of available migrations
	 */
	protected $migrations = array();

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

		// Check if already created
		try
		{
			$this->config = clone $app->get_object('Config', 'migrations');
		}
		catch (\RuntimeException $e)
		{
			$this->config = $app->forge('Config');
		}

		$this->config
			// Set defaults
			->add(array(
				'table_name'  => 'migrations',
			))
			// Add validators
			->validators(array(
				'table_name'  => function($table)
				{
					return is_string($table) and preg_match('#^[a-z0-9_]+$#uiD', $table) > 0;
				},
			));
	}

	/**
	 * Fetch the migrations that have been ran from the Database
	 *
	 * @param   string  $package
	 * @return  array
	 */
	public function & get_migrated($package)
	{
		if ( ! isset($this->migrated[$package]))
		{
			// @todo make this actually work
			$this->migrated[$package] = $this->app->get_object('db')
				->select()
				->from($this->config->get('table', 'migrations'))
				->where('package', '=', $package)
				->order_by('migration_id', 'ASC')
				->execute()
				->as_array('migration_id', 'migration_id');
		}

		return $this->migrated[$package];
	}

	/**
	 * Get all the available migrations from a package
	 *
	 * @param   string  $package
	 * @return  array
	 */
	public function & get_migrations($package)
	{
		if ( ! isset($this->migrations[$package]))
		{
			$glob = $this->app->env->loader->package($package)->glob('resources/migrations', '*_*.php');

			$migrations = $glob;
			foreach ($glob as $path => $files)
			{
				foreach ($files as $file)
				{
					$basename = basename($file);
					($pos = strpos($basename, '_')) and $basename = substr($basename, 0, $pos);
					$migrations[$basename] = $path.$file;
				}
			}
			ksort($migrations, SORT_STRING);

			$this->migrations[$package] = $migrations;
		}

		return $this->migrations[$package];
	}

	/**
	 * Makes sure all updates up to current have been ran, optionally moves current to latest created migration
	 *
	 * @param   string       $package  package to run migrations on
	 * @param   bool|string  $latest   false to keep current, true for the newest or specific
	 * @return  Base
	 */
	public function update($package, $latest = false)
	{
		$migrated = $this->get_migrated($package);
		end($migrated);
		$current = key($migrated);

		if ($latest === false)
		{
			$endpoint = $current;
		}
		elseif ($latest === true)
		{
			$migrations = $this->get_migrations($package);
			end($migrations);
			$endpoint = key($migrations);
		}
		else
		{
			$endpoint = $latest;
		}

		if (strcmp($endpoint, $current) < 0)
		{
			throw new \InvalidArgumentException('The endpoint given to update() must be larger than or equal to the
				last ran migration.');
		}

		return $this->run($package, $endpoint, 1);
	}

	/**
	 * Updates migrations to latest
	 *
	 * @param   string  $package  package to run migrations on
	 * @return  Base
	 */
	public function latest($package)
	{
		return $this->update($package, true);
	}

	/**
	 * Downgrades current to new endpoint
	 *
	 * @param   string  $package   package to run migrations on
	 * @param   string  $endpoint  migration ID smaller than or equal to current migration
	 * @return  Base
	 */
	public function downgrade($package, $endpoint)
	{
		$migrated = $this->get_migrated($package);
		end($migrated);
		$current = key($migrated);

		if (strcmp($endpoint, $current) > 0)
		{
			throw new \InvalidArgumentException('The endpoint given to downgrade() must be smaller than or equal to the
				last ran migration.');
		}

		return $this->run($package, $endpoint, -1);
	}

	/**
	 * Run migrations for package to the given endpoint
	 *
	 * @param   string  $package
	 * @param   string  $endpoint
	 * @param   int     $direction
	 * @return  Base
	 * @throws  \Exception
	 */
	protected function run($package, $endpoint, $direction)
	{
		$migrated    = $this->get_migrated($package);
		$migrations  = $this->get_migrations($package);
		$direction < 0 and $migrations = array_reverse($migrations);

		try
		{
			foreach ($migrations as $id => & $migration)
			{
				if ($direction > 0 and strcmp($id, $endpoint) <= 0 and ! isset($migrated[$id]))
				{
					is_string($migration) and $migration = require $migration;
					$migration->validate($package, $id, $this);
					if ($migration($direction))
					{
						$this->set_migrated($package, $id, $direction);
					}
					else
					{
						throw new Migration\Exception('Migration with ID "'.$id.'" failed to migrate up.');
					}
				}
				elseif ($direction < 0 and strcmp($id, $endpoint) > 0 and isset($migrated[$id]))
				{
					if ($migration($direction))
					{
						$this->set_migrated($package, $id, $direction);
					}
					else
					{
						throw new Migration\Exception('Migration with ID "'.$id.'" failed to migrate up.');
					}
				}
			}
			$this->flush_migrated();
		}
		catch(\Exception $e)
		{
			// Make sure finished migrations are marked as such
			$this->flush_migrated();
			throw $e;
		}

		return $this;
	}

	/**
	 * Marks a migration ID as ran but with status yet unsaved
	 *
	 * @param   string  $package
	 * @param   string  $id
	 * @param   int     $direction
	 * @return  void
	 */
	public function set_migrated($package, $id, $direction)
	{
		$this->_unsaved_migrated[$package][] = array($id => $direction);
	}

	/**
	 * Runs through all the migrations that were ran and have to be saved to the database
	 *
	 * @return  void
	 */
	public function flush_migrated()
	{
		foreach ($this->_unsaved_migrated as $package => $ids)
		{
			foreach ($ids as $id => $direction)
			{
				// @todo make this actually work
				if ($direction > 0)
				{
					$this->app->get_object('db')
						->insert($this->config->get('table', 'migrations'))
						->set(array(
							'package' => $package,
							'migration_id' => $id,
						))
						->execute();
				}
				elseif ($direction < 0)
				{
					$this->app->get_object('db')
						->delete()
						->from($this->config->get('table', 'migrations'))
						->where('package', '=', $package)
						->where('migration_id', '=', $id)
						->execute();
				}
			}
		}
	}
}
