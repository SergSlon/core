<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace Fuel\Core\Cache\Storage;
use Fuel\Core\Cache;

/**
 * Interface for storing Cache values
 *
 * @package  Fuel\Core
 *
 * @since  1.0.0
 */
interface Storable
{
	/**
	 * Take care of the storage engine specific reading. Needs to set the Cache properties:
	 * - created
	 * - expiration
	 * - dependencies
	 * - contents
	 * - content_handler
	 *
	 * @param   \Fuel\Core\Cache\Base  $cache  object to store
	 * @return  bool
	 *
	 * @since  1.0.0
	 */
	public function get(Cache\Base $cache);

	/**
	 * Take care of the storage engine specific writing. Needs to write the Cache properties:
	 * - created
	 * - expiration
	 * - dependencies
	 * - contents
	 * - content_handler
	 *
	 * @param   \Fuel\Core\Cache\Base  $cache  object to store
	 * @return  bool
	 *
	 * @since  1.0.0
	 */
	public function set(Cache\Base $cache);

	/**
	 * Should delete this cache instance, should also run reset() afterwards
	 *
	 * @param   \Fuel\Core\Cache\Base  $cache  object to store
	 * @return  bool
	 *
	 * @since  1.0.0
	 */
	public function delete(Cache\Base $cache);

	/**
	 * Should check all dependencies against the creation timestamp.
	 *
	 * @param   \Fuel\Core\Cache\Base  $cache  object to check the dependencies of
	 * @return  bool
	 *
	 * @since  1.0.0
	 */
	public function check_dependencies(Cache\Base $cache);

	/**
	 * Flushes the whole cache for a specific storage type or just a part of it when $section is set
	 * (might not work with all storage drivers), defaults to the default storage type
	 *
	 * @param   string
	 * @return  bool
	 *
	 * @since  1.0.0
	 */
	public function delete_all($section = null);
}
