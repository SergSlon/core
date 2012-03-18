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
use Fuel\Kernel\Application;
use Fuel\Core\Cache;

/**
 * File based Cache Storage implementation
 *
 * @package  Fuel\Core
 *
 * @since  1.0.0
 */
class File extends Base
{
	/**
	 * @var  \Fuel\Kernel\Application\Base  app that created this request
	 *
	 * @since  2.0.0
	 */
	protected $app;

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
	}

	/**
	 * Returns the path to a Cache object
	 *
	 * @param   \Fuel\Core\Cache\Base|string  $id
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	protected function path($id)
	{
		$path = str_replace('.', '/', ($id instanceof Cache\Base ? $id->id : strval($id)));
		return $path;
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
	public function get(Cache\Base $cache)
	{
		$id_path = $this->path($cache);
		$file = $cache->config['path'].$id_path.'.cache';
		if ( ! file_exists($file))
		{
			return false;
		}

		$handle = fopen($file, 'r');
		if ( ! $handle)
		{
			return false;
		}

		// wait for a lock
		while( ! flock($handle, LOCK_EX));

		// read the cache
		$payload = fread($handle, filesize($file));

		// release the lock
		flock($handle, LOCK_UN);

		// close the file
		fclose($handle);

		try
		{
			$this->parse_cache($cache, $payload);
		}
		catch (\DomainException $e)
		{
			return false;
		}

		return true;
	}

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
	public function set(Cache\Base $cache)
	{
		$payload = $this->prepare_cache($cache);
		$id_path = $this->path($cache);

		// create directory if necessary
		$subdirs = explode('/', $id_path);
		if (count($subdirs) > 1)
		{
			array_pop($subdirs);
			$test_path = $cache->config['path'].implode('/', $subdirs);

			// check if specified subdir exists
			if ( ! @is_dir($test_path))
			{
				// create non existing dir
				if ( ! @mkdir($test_path, 0755, true))
				{
					return false;
				}
			}
		}

		// write the cache
		$file    = $cache->config['path'].$id_path.'.cache';
		$handle  = fopen($file, 'c');

		if ( ! $handle)
		{
			return false;
		}

		// wait for a lock
		while ( ! flock($handle, LOCK_EX));

		// write the session data
		fwrite($handle, $payload);

		//release the lock
		flock($handle, LOCK_UN);

		// close the file
		fclose($handle);

		return true;
	}

	/**
	 * Should delete this cache instance, should also run reset() afterwards
	 *
	 * @param   \Fuel\Core\Cache\Base  $cache  object to store
	 * @return  bool
	 *
	 * @since  1.0.0
	 */
	public function delete(Cache\Base $cache)
	{
		$file = $cache->config['path'].$this->path($cache).'.cache';
		$cache->reset();
		return @unlink($file);
	}

	/**
	 * Should check all dependencies against the creation timestamp.
	 *
	 * @param   \Fuel\Core\Cache\Base  $cache  object to check the dependencies of
	 * @return  bool
	 *
	 * @since  1.0.0
	 */
	public function check_dependencies(Cache\Base $cache)
	{
		$dependencies = $cache->dependencies;
		foreach ($dependencies as $dep)
		{
			if (file_exists($file = $cache->config['path'].$this->path($dep).'.cache'))
			{
				$filemtime = filemtime($file);
				if ($filemtime === false || $filemtime > $cache->created)
				{
					return false;
				}
			}
			elseif (file_exists($dep))
			{
				$filemtime = filemtime($file);
				if ($filemtime === false || $filemtime > $cache->created)
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * Flushes the whole cache for a specific storage type or just a part of it when $section is set
	 * (might not work with all storage drivers), defaults to the default storage type
	 *
	 * @param   string                 $section
	 * @param   \Fuel\Core\Cache\Base  $cache
	 * @return  bool
	 *
	 * @since  1.0.0
	 */
	public function delete_all($section = null, Cache\Base $cache = null)
	{
		$path = $cache ? $cache->config['path'] : $this->app->config['cache.path'];
		if ( ! $path)
		{
			return false;
		}
		$section = $this->path($section);

		return $this->app->get_object('File')->delete_dir($path.$section, true, false);
	}
}
