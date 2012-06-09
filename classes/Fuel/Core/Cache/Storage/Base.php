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
 * Base abstract for storing Cache values
 *
 * @package  Fuel\Core
 *
 * @since  1.0.0
 */
abstract class Base
{
	/**
	 * @const  string  Tag used for opening & closing cache properties
	 *
	 * @since  1.0.0
	 */
	const PROPS_TAG = 'Fuel_Cache_Properties';

	/**
	 * Prepend the cache properties
	 *
	 * @param   \Fuel\Core\Cache\Base  $cache
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	protected function prepare_cache(Cache\Base $cache)
	{
		$properties = array(
			'created'        => $cache->created,
			'lifetime'       => $cache->lifetime,
			'dependencies'   => $cache->dependencies,
			'format_driver'  => get_class($cache->format_driver),
		);

		return '{{'.static::PROPS_TAG.'}}'.json_encode($properties).'{{/'.static::PROPS_TAG.'}}'
			.$cache->format_driver()->encode($cache->content);
	}

	/**
	 * Remove the prepended cache properties and save them in class properties
	 *
	 * @param   \Fuel\Core\Cache\Base  $cache
	 * @param   string                 $payload
	 * @throws  \DomainException
	 *
	 * @since  1.0.0
	 */
	protected function parse_cache(Cache\Base $cache, $payload)
	{
		$properties_end = strpos($payload, '{{/'.self::PROPS_TAG.'}}');
		if ($properties_end === false)
		{
			throw new \DomainException('Cache has bad formatting');
		}

		$content = substr($payload, $properties_end + strlen('{{/'.static::PROPS_TAG.'}}'));
		$props = substr(substr($payload, 0, $properties_end), strlen('{{'.static::PROPS_TAG.'}}'));
		$props = json_decode($props, true);
		if (empty($props))
		{
			throw new \DomainException('Cache properties retrieval failed');
		}

		$cache->set_created($props['created']);
		$cache->set_lifetime($props['lifetime']);
		$cache->set_dependencies($props['dependencies']);
		$cache->set_content($cache->format_driver()->decode($content), $props['format_driver']);
	}

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
	abstract public function get(Cache\Base $cache);

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
	abstract public function set(Cache\Base $cache);

	/**
	 * Should delete this cache instance, should also run reset() afterwards
	 *
	 * @param   \Fuel\Core\Cache\Base  $cache  object to store
	 * @return  bool
	 *
	 * @since  1.0.0
	 */
	abstract public function delete(Cache\Base $cache);

	/**
	 * Should check all dependencies against the creation timestamp.
	 *
	 * @param   \Fuel\Core\Cache\Base  $cache  object to check the dependencies of
	 * @return  bool
	 *
	 * @since  1.0.0
	 */
	abstract public function check_dependencies(Cache\Base $cache);

	/**
	 * Flushes the whole cache for a specific storage type or just a part of it when $section is set
	 * (might not work with all storage drivers), defaults to the default storage type
	 *
	 * @param   string
	 * @return  bool
	 *
	 * @since  1.0.0
	 */
	abstract public function delete_all($section = null);
}
