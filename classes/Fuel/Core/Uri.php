<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace Fuel\Core;

use Fuel\Kernel\Application;

/**
 * Uri utility class
 *
 * @package  Fuel\Core
 *
 * @since  1.0.0
 */
class Uri
{
	/**
	 * @var  \Fuel\Kernel\Application\Base  app that created this request
	 *
	 * @since  2.0.0
	 */
	public $app;

	/**
	 * @var  string
	 *
	 * @since  2.0.0
	 */
	protected $scheme;

	/**
	 * @var  string
	 *
	 * @since  2.0.0
	 */
	protected $user;

	/**
	 * @var  string
	 *
	 * @since  2.0.0
	 */
	protected $hostname;

	/**
	 * @var  int
	 *
	 * @since  2.0.0
	 */
	protected $port;

	/**
	 * @var  string
	 *
	 * @since  2.0.0
	 */
	protected $path;

	/**
	 * @var  array
	 *
	 * @since  2.0.0
	 */
	protected $pathArray = array();

	/**
	 * @var  string
	 *
	 * @since  2.0.0
	 */
	protected $extension;

	/**
	 * @var  array
	 *
	 * @since  2.0.0
	 */
	protected $query = array();

	/**
	 * Constructor deconstructs a given URI
	 *
	 * @param   string|array  $uri
	 * @throws  \InvalidArgumentException
	 *
	 * @since  2.0.0
	 */
	public function __construct($uri = '')
	{
		if (is_string($uri))
		{
			// Fetch the scheme prefix, and when present also the hostname
			if (($pos = strpos($uri, '://')) !== false)
			{
				$this->scheme = substr($uri, 0, $pos) ?: null;
				$uri = substr($uri, $pos + 3);

				if (($pos = strpos($uri, '@')) !== false
					and ($pos < strpos($uri, '/') or strpos($uri, '/') === false))
				{
					$this->user = substr($uri, 0, $pos);
					$uri = substr($uri, $pos + 1);
				}

				if (($pos = strpos($uri, ':')) !== false
					and ($pos < ($spos = strpos($uri, '/')) or $spos === false))
				{
					$this->port = intval(substr($uri, $pos + 1, $spos - $pos - 1));
					$uri = substr($uri, 0 , $pos).substr($uri, $spos);
				}

				if (($pos = strpos($uri, '/')) !== false)
				{
					$this->hostname = substr($uri, 0, $pos);
					$uri = substr($uri, $pos + 1);
				}
				else
				{
					$this->hostname = $uri;
					$uri = '';
				}
			}

			$this->setPath($uri);
		}
		elseif (is_array($uri))
		{
			isset($uri['scheme'])
				and $this->setScheme($uri['scheme']);
			isset($uri['username'])
				and $this->setUser($uri['username'], isset($uri['password']) ? $uri['password'] : null);
			isset($uri['hostname'])
				and $this->setHostname($uri['hostname']);
			isset($uri['port'])
				and $this->setHostname($uri['port']);
			isset($uri['segments'])
				and $this->setSegments($uri['segments']);
			isset($uri['path'])
				and $this->setPath($uri['path']);
			isset($uri['extension'])
				and $this->setExtension($uri['extension']);
			isset($uri['query'])
				and $this->setQuery($uri['query']);
		}
		else
		{
			throw new \InvalidArgumentException('Constructor takes either a string or an array.');
		}
	}

	/**
	 * Magic Fuel method that is the setter for the current app
	 *
	 * @param   \Fuel\Kernel\Application\Base  $app
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function _setApp(Application\Base $app)
	{
		$this->app = $app;

		// When path was relative use the current input as scheme & hostname
		if (is_null($this->scheme) and is_null($this->hostname))
		{
			$input = ($req = $app->getActiveRequest()) ? $req->input : $app->env->input;

			$this->scheme = $input->getScheme();
			$this->hostname = $input->getServer('SERVER_NAME');

			// when no extension was given, use application default
			is_null($this->extension)
				and $this->extension = $app->getConfig('extension', null);

			// when no port was given, default to current port
			if (is_null($this->port) and ($port = $input->getServer('SERVER_PORT')))
			{
				$this->setPort($port);
			}
		}
	}

	/**
	 * Change the URI's scheme
	 *
	 * @param   string  $scheme
	 * @return  Uri
	 *
	 * @since  2.0.0
	 */
	public function setScheme($scheme)
	{
		$this->scheme = strval($scheme);
		return $this;
	}

	/**
	 * Fetch the URI's scheme
	 *
	 * @param   bool  $withPostfix
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function getScheme($withPostfix = false)
	{
		return $this->scheme.(($withPostfix and $this->scheme) ? '://' : '');
	}

	/**
	 * Set the username and optionally a password
	 *
	 * @param   string  $username
	 * @param   string  $password
	 * @return  Uri
	 *
	 * @since  2.0.0
	 */
	public function setUser($username, $password = null)
	{
		$this->user = strval($username).($password ? ':'.strval($password) : '');
		return $this;
	}

	/**
	 * Returns 'username' or 'username:password' when given in the URI
	 *
	 * @param   bool  $withSuffix
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function getUser($withSuffix = false)
	{
		return $this->user.(($this->user and $withSuffix) ? '@' : '');
	}

	/**
	 * Get the username from the user value
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function getUsername()
	{
		return ($pos = strpos($this->user, ':')) ? substr($this->user, 0, $pos) : $this->user;
	}

	/**
	 * Get the password from the user value
	 *
	 * @return  null|string
	 *
	 * @since  2.0.0
	 */
	public function getPassword()
	{
		return ($pos = strpos($this->user, ':')) ? substr($this->user, $pos + 1) : null;
	}

	/**
	 * Change the URI's hostname
	 *
	 * @param   string  $hostname
	 * @return  Uri
	 *
	 * @since  2.0.0
	 */
	public function setHostname($hostname)
	{
		$this->hostname = strval($hostname);
		return $this;
	}

	/**
	 * Fetch the URI's hostname
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function getHostname()
	{
		return $this->hostname;
	}

	/**
	 * Change the URI's port number
	 *
	 * @param   string  $port
	 * @return  Uri
	 *
	 * @since  2.0.0
	 */
	public function setPort($port)
	{
		$this->port = intval($port);
		return $this;
	}

	/**
	 * Fetch the URI's hostname
	 *
	 * @param   bool  $withPrefix
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function getPort($withPrefix = false)
	{
		return (( ! is_null($this->port) and $withPrefix) ? ':' : '').$this->port;
	}

	/**
	 * Set the path based on an array
	 *
	 * @param   array  $segments
	 * @return  Uri
	 */
	public function setSegments(array $segments)
	{
		return $this->setPath(implode('/', array_filter($segments)));
	}

	/**
	 * Returns the desired segment, all segments (when called without args) or $default if it does not exist.
	 *
	 * @param   int     $segment  The segment number (1-based index)
	 * @param   mixed   $default  Default value to return
	 * @return  string|array
	 *
	 * @since  2.0.0
	 */
	public function getSegment($segment = null, $default = null)
	{
		if (func_num_args() === 0)
		{
			return $this->pathArray;
		}
		elseif (isset($this->pathArray[$segment - 1]))
		{
			return $this->pathArray[$segment - 1];
		}

		return __val($default);
	}

	/**
	 * Change the path (including extension when given)
	 *
	 * @param   string  $path
	 * @return  Uri
	 *
	 * @since  2.0.0
	 */
	public function setPath($path)
	{
		// Remove a prefixed slash when present
		$path = ltrim($path, '/');

		// Fetch the URI query
		if ($pos = strpos($path, '?'))
		{
			$this->addQuery(substr($path, $pos + 1), $this->query);
			$path = substr($path, 0, $pos);
		}

		$extension = pathinfo($path, PATHINFO_EXTENSION);
		if ( ! empty($extension))
		{
			// Detect extension
			$this->extension = $extension;
			$extension and $path = substr($path, 0, -(strlen($this->extension) + 1));
		}

		// Whatever is left is the path
		$this->path = $path;
		$this->pathArray = array_filter(explode('/', $path));

		return $this;
	}

	/**
	 * Returns the path as a string (without extension or query)
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function getPath()
	{
		return '/'.$this->path;
	}

	/**
	 * Change the URI's extension
	 *
	 * @param   string  $extension
	 * @return  Uri
	 *
	 * @since  2.0.0
	 */
	public function setExtension($extension)
	{
		$this->extension = strval($extension);
		return $this;
	}

	/**
	 * Returns the URI's extension
	 *
	 * @param   bool  $prefixDot
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function getExtension($prefixDot = false)
	{
		return (($prefixDot and $this->extension) ? '.' : '').$this->extension;
	}

	/**
	 * Change the URI's query
	 *
	 * @param   string|array  $query
	 * @return  Uri
	 * @throws  \InvalidArgumentException
	 *
	 * @since  2.0.0
	 */
	public function setQuery($query)
	{
		$this->query = array();
		return $this->addQuery($query);
	}

	/**
	 * Add variables to the URI's query
	 *
	 * @param   string|array  $query
	 * @return  Uri
	 * @throws  \InvalidArgumentException
	 *
	 * @since  2.0.0
	 */
	public function addQuery($query)
	{
		if (is_string($query))
		{
			parse_str($query, $this->query);
		}
		elseif (is_array($query))
		{
			$this->query += $query;
		}
		else
		{
			throw new \InvalidArgumentException('Query must be either a string or an array.');
		}

		return $this;
	}

	/**
	 * Returns the desired query value, all values (when called without args) or $default if it does not exist.
	 *
	 * @param   int     $key      keyname in the URI's query vars
	 * @param   mixed   $default  Default value to return
	 * @return  string|array
	 *
	 * @since  2.0.0
	 */
	public function getQuery($key = null, $default = null)
	{
		if (func_num_args() === 0)
		{
			return $this->query;
		}
		elseif (array_get_dot_key($key, $this->query, $return))
		{
			return $return;
		}

		return __val($default);
	}

	/**
	 * Fetch the query params as a string
	 *
	 * @param   bool  $withPrefix
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function getQueryString($withPrefix = false)
	{
		return (($withPrefix and $this->getQuery()) ? '?' : '').http_build_query($this->getQuery());
	}

	/**
	 * Replace keys (enclosed as an HTML tag) with given values
	 *
	 * @param   array  $values
	 * @param   bool   $includeQuery
	 * @return  Uri
	 *
	 * @since  2.0.0
	 */
	public function replace(array $values = array(), $includeQuery = true)
	{
		// Get the query to check before replacing (or set to false to disable feature)
		$query = $includeQuery ? $this->getQueryString() : false;

		// Replace all values
		foreach ($values as $key => $val)
		{
			// Expect the keys enclosed as an HTML tag
			$key = '<'.$key.'>';

			// Replace in hostname/path/extension
			$this->setScheme(str_replace($key, $val, $this->scheme));
			if (strpos($this->user, '<') !== false)
			{
				$username = str_replace($key, $val, $this->getUsername());
				$password = str_replace($key, $val, $this->getPassword()) ?: null;
				$this->setUser($username, $password);
			}
			$this->setHostname(str_replace($key, $val, $this->hostname));
			$this->path = str_replace($key, $val, $this->path);
			$this->extension = str_replace($key, $val, $this->extension);

			// Replace in QueryString when applicable
			if ($query)
			{
				$query = str_replace(urlencode($key), urlencode($val), $query);
			}
		}
		$this->pathArray = array_filter(explode('/', $this->path));

		// When the query was parsed, parse it back into an array
		if ($query)
		{
			$this->setQuery($query);
		}

		return $this;
	}

	/**
	 * Get the full URI based on this query
	 *
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	public function get()
	{
		return $this->getScheme(true).
			$this->getUser(true).
			$this->getHostname().
			$this->getPort(true).
			$this->getPath().
			$this->getExtension(true).
			$this->getQueryString(true);
	}

	/**
	 * Returns the URI string
	 *
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	public function __toString()
	{
		return $this->get();
	}
}
