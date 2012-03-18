<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace Fuel\Core\Cache;
use Fuel\Kernel\Application;
use Closure;

/**
 * Base implementation for caching values
 *
 * @package  Fuel\Core
 *
 * @since  1.0.0
 */
class Base
{
	/**
	 * @var  \Fuel\Kernel\Application\Base  app that created this request
	 *
	 * @since  2.0.0
	 */
	protected $app;

	/**
	 * @var  \Fuel\Kernel\Data\Config
	 *
	 * @since  2.0.0
	 */
	public $config;

	/**
	 * @var  \Fuel\Core\Cache\Storage\Storable  handles storing values
	 *
	 * @since  2.0.0
	 */
	protected $storage_driver;

	/**
	 * @var  \Fuel\Core\Cache\Format\Formatable  handles and formats the cache's contents
	 *
	 * @since  1.0.0
	 */
	protected $format_driver;

	/**
	 * @var  string  the cache's name, either string or md5'd serialization of something else
	 *
	 * @since  1.0.0
	 */
	protected $id = null;

	/**
	 * @var  int  timestamp of creation of the cache
	 *
	 * @since  1.0.0
	 */
	protected $created = null;

	/**
	 * @var  int  number of seconds this cache will last
	 *
	 * @since  2.0.0
	 */
	protected $lifetime = null;

	/**
	 * @var  array  contains identifiers of other caches this one depends on
	 *
	 * @since  1.0.0
	 */
	protected $dependencies = array();

	/**
	 * @var  mixed  the contents stored in this Cache
	 *
	 * @since  1.0.0
	 */
	protected $content = null;

	/**
	 * Constructor
	 *
	 * @param  string  $id      the identifier for this cache
	 * @param  array   $config  additional config values
	 *
	 * @since  1.0.0
	 */
	public function __construct($id, $config)
	{
		$this->id = $id;
	}

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
			$this->config = clone $app->get_object('Config', 'cache');
		}
		catch (\RuntimeException $e)
		{
			$this->config = $app->forge('Config');
		}

		$this->config
			// Set defaults
			->add(array(
				'path'  => $this->app->loader->path().'resources/cache/',
				'storage' => 'File',
				'format' => array(
					'string' => 'String',
				),
			))
			// Add validators
			->validators(array(
				'path'    => 'is_dir',
				'format'  => 'is_array'
			));

		$this->storage_driver = isset($config['storage'])
			? $app->get_object('Cache_Storage.'.$config['storage'])
			: $app->get_object('Cache_Storage');
	}

	/**
	 * Converts the identifier to a string when necessary:
	 * A int is just converted to a string, all others are serialized and then md5'd
	 *
	 * @param   mixed
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	public static function stringify_id($identifier)
	{
		$hash = (( ! is_string($identifier) and ! is_int($identifier))
			or (preg_match('/^([a-z0-9_\.\-]*)$/iuD', $identifier) === 0));

		return $hash ? '_hashes.'.md5(serialize($identifier)) : strval($identifier);
	}

	/**
	 * Resets all properties except for the identifier, should be run by default when a delete() is triggered
	 *
	 * @return  Base
	 *
	 * @since  1.0.0
	 */
	public function reset()
	{
		$this->content        = null;
		$this->created        = null;
		$this->lifetime       = null;
		$this->dependencies   = array();
		$this->format_driver  = null;

		return $this;
	}

	/**
	 * Store a value in the cache
	 *
	 * @param   mixed  $content       The content to be cached
	 * @param   int    $lifetime      The time in seconds until the cache will expire, <= 0 means no expiration
	 * @param   array  $dependencies  array of names on which this cache depends for validity
	 * @return  Base
	 *
	 * @since  1.0.0
	 */
	public function set($content = null, $lifetime = null, array $dependencies = null)
	{
		$contents = __val($content);

		isset($lifetime) and $this->lifetime = $lifetime;
		isset($contents) and $this->set_content($contents);
		isset($dependencies) and $this->dependencies = $dependencies;

		$this->created = time();

		// Create expiration timestamp when other then null
		if ( ! is_null($this->lifetime))
		{
			if ( ! is_numeric($this->lifetime))
			{
				throw new \DomainException('Expiration must be a valid number.');
			}
		}

		// Convert dependency identifiers to string when set
		foreach ($this->dependencies as $key => $id)
		{
			$this->dependencies[$key] = $this->stringify_id($id);
		}

		// Turn everything over to the storage specific method
		$this->storage_driver->set($this);

		return $this;
	}

	/**
	 * Fetch a value from the Cache
	 *
	 * @param   bool  $use_expiration
	 * @return  mixed
	 * @throws  Exception\NotFound|Exception\Expired
	 *
	 * @since  1.0.0
	 */
	public function get($use_expiration = true)
	{
		if ( ! $this->storage_driver->get($this))
		{
			throw new Exception\NotFound($this->id);
		}

		if ($use_expiration)
		{
			if ( ! is_null($this->lifetime) and ($this->created + $this->lifetime) < time())
			{
				$this->storage_driver->delete($this);
				throw new Exception\Expired($this->id);
			}

			// Check dependencies and handle as expired on failure
			if ( ! $this->storage_driver->check_dependencies($this))
			{
				$this->storage_driver->delete($this);
				throw new Exception\Expired($this->id);
			}
		}

		return $this->get_content();
	}

	/**
	 * Does get() & set() in one call that takes a Closure to generate the contents
	 *
	 * @param   \Closure  $callback      To execute when cache wasn't found
	 * @param   int|null  $lifetime      Cache expiration in seconds
	 * @param   array     $dependencies  Contains the identifiers of caches this one will depend on
	 * @return  mixed
	 *
	 * @since  1.0.0
	 */
	public function call(Closure $callback, $lifetime = null, $dependencies = array())
	{
		try
		{
			$this->get();
		}
		catch (Exception\NotFound $e)
		{
			$this->set($callback(), $lifetime, $dependencies);
		}

		return $this->get_content();
	}

	/**
	 * Set the contents with optional handler instead of the default
	 *
	 * @param   mixed   $content
	 * @param   string  $formatter
	 * @return  Base
	 *
	 * @since  1.0.0
	 */
	public function set_content($content, $formatter = null)
	{
		$this->set_format_driver($formatter);
		$this->content = $this->format_driver()->encode($content);
		return $this;
	}

	/**
	 * Fetches content
	 *
	 * @return  mixed
	 *
	 * @since  1.0.0
	 */
	public function get_content()
	{
		return $this->format_driver()->decode($this->content);
	}

	/**
	 * Decides a content handler that makes it possible to write non-strings to a file
	 *
	 * @param   string|null  $formatter
	 * @return  Base
	 *
	 * @since  2.0.0
	 */
	protected function set_format_driver($formatter)
	{
		! is_null($formatter) and $this->format_driver = $this->app->get_object('Cache_Format.'.$formatter);
		return $this;
	}

	/**
	 * Gets the format driver
	 *
	 * @return  Format\Formatable
	 *
	 * @since  2.0.0
	 */
	public function format_driver()
	{
		if (empty($this->format_driver))
		{
			$type = is_object($this->content) ? get_class($this->content) : gettype($this->content);
			$driver = $this->app->config['format.'.$type] ?: '';

			$this->format_driver = $driver
				? $this->app->get_object('Cache_Format.'.$driver)
				: $this->app->get_object('Cache_Format');
		}

		return $this->format_driver;
	}
}
