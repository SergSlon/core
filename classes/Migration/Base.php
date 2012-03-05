<?php

namespace Fuel\Core\Migration;
use Closure;

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
	 * @param   Container\Base  $container
	 * @return  Migratable
	 * @throws  Exception  when migration didn't validate
	 */
	public function validate(Container\Base $container)
	{
		// @todo implement this to check the container
		// fail when migrations were ran after parent that modified the same tables

		return $this;
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
