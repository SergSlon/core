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
 * Interface for Migration objects
 *
 * @package  Fuel\Core
 *
 * @since  2.0.0
 */
interface Migratable
{
	/**
	 * Assigns the action being performed when migrating up
	 *
	 * @param   Closure  $up
	 * @return  Migratable
	 */
	public function up(Closure $up);

	/**
	 * Assigns the action being performed when migrating down
	 *
	 * @param   Closure  $down
	 * @return  Migratable
	 */
	public function down(Closure $down);

	/**
	 * Assigns the migration this one is based on
	 *
	 * @param   string  $id
	 * @return  Migratable
	 */
	public function parent($id);

	/**
	 * Keep track of the tables modified by this migration
	 *
	 * @param   array  $tables
	 * @return  Migratable
	 */
	public function modifies(array $tables);

	/**
	 * Checks whether this migration can be run based on parent() & modifies()
	 *
	 * @param   array           $migrations
	 * @param   string          $id
	 * @param   Container\Base  $container
	 * @return  Migratable
	 * @throws  Exception  when migration didn't validate
	 */
	public function validate(array & $migrations, $id, Container\Base $container);

	/**
	 * Run a migration up or down based on the param
	 *
	 * @param   int  $direction  >0 for up, <0 for down
	 * @return  Migratable
	 */
	public function __invoke($direction);
}
