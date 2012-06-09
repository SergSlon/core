<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace Fuel\Core\Migration;
use Closure;

/**
 * Base implementation of Migratable
 *
 * @package  Fuel\Core
 *
 * @since  2.0.0
 */
class Base implements Migratable
{
	/**
	 * @var  \Closure
	 */
	protected $up;

	/**
	 * @var  \Closure
	 */
	protected $down;

	/**
	 * @var  string  migration ID that this one depends on
	 */
	public $parent;

	/**
	 * @var  array  database tables/structures modified by this migration
	 */
	public $tables = array();

	/**
	 * Assigns the action being performed when migrating up
	 *
	 * @param   Closure  $up
	 * @return  Migratable
	 */
	public function up(Closure $up)
	{
		$this->up = $up;
		return $this;
	}

	/**
	 * Assigns the action being performed when migrating down
	 *
	 * @param   Closure  $down
	 * @return  Migratable
	 */
	public function down(Closure $down)
	{
		$this->down = $down;
		return $this;
	}

	/**
	 * Assigns the migration this one is based on
	 *
	 * @param   string  $id
	 * @return  Migratable
	 */
	public function parent($id)
	{
		$this->parent = $id;
		return $this;
	}

	/**
	 * Keep track of the tables modified by this migration
	 *
	 * @param   array  $tables
	 * @return  Migratable
	 */
	public function modifies(array $tables)
	{
		$this->tables = $tables;
		return $this;
	}

	/**
	 * Checks whether this migration can be run based on parent() & modifies()
	 *
	 * @param   array           $migrations
	 * @param   string          $id
	 * @param   Container\Base  $container
	 * @return  void
	 * @throws  Exception  when migration didn't validate
	 */
	public function validate(array & $migrations, $id, Container\Base $container)
	{
		reset($migrations);
		if ($this->parent and $this->tables)
		{
			// Skip all migrations prior to this one's parent
			while (strcmp(key($migrations), $this->parent) <= 0)
			{
				next($migrations);
			}
			// Check all migrations between the parent and this one itself for conflicts
			while (strcmp(key($migrations), $id) < 0)
			{
				// Load the object if it's still a path
				$key = key($migrations);
				is_string($migrations[$key]) and $migrations[$key] = require $migrations[$key];

				// Check an intersection exist, when that's the case it means fatalities
				if (array_intersect($this->tables, $migrations[$key]->tables))
				{
					throw new Exception('A migration modifying the same tables was insert between this one and its
						parent. Failing migration: '.$id);
				}
			}
		}
	}

	/**
	 * Run a migration up or down based on the param
	 *
	 * @param   int  $direction  >0 for up, <0 for down
	 * @return  bool
	 */
	public function __invoke($direction)
	{
		if ($direction > 0)
		{
			$success = $this->up ? call_user_func($this->up) : null;
			return $success or is_null($success);
		}
		elseif ($direction < 0)
		{
			$success = $this->down ? call_user_func($this->down) : null;
			return $success or is_null($success);
		}

		return true;
	}

}
