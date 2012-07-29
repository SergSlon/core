<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace Fuel\Core\Request;

use Fuel\Kernel\Request\Exception;
use Fuel\Aliases\Request\Base;

/**
 * Request class for executing cURL requests.
 *
 * @package  Fuel\Core
 *
 * @since  1.1.0
 */
class Curl extends Base
{
	/**
	 * @var  string  URL to serve as the resource for the CURL request
	 *
	 * @since  1.1.0
	 */
	protected $resource = '';

	/**
	 * @var  array  of HTTP request headers
	 *
	 * @since  1.1.0
	 */
	protected $headers = array();

	/**
	 * @var  string  HTTP request method
	 *
	 * @since  1.1.0
	 */
	protected $method = 'GET';

	/**
	 * @var  array  HTTP request parameters
	 *
	 * @since  1.1.0
	 */
	protected $params = array();

	/**
	 * @var  array  cURL options
	 *
	 * @since  1.1.0
	 */
	protected $options = array(
		CURLOPT_TIMEOUT         => 30,
		CURLOPT_RETURNTRANSFER  => true,
		CURLOPT_FAILONERROR     => false,
	);

	/**
	 * Constructor
	 *
	 * @param   string  $resource
	 * @param   array   $input
	 * @throws  \RuntimeException
	 *
	 * @since  2.0.0
	 */
	public function __construct($resource = '', $input = array())
	{
		// check if we have libcurl available
		if ( ! function_exists('curl_init'))
		{
			throw new \RuntimeException('Your PHP installation doesn\'t have cURL enabled.');
		}

		! is_array($resource) and $options['resource'] = $resource;

		foreach ($input as $var => $setting)
		{
			if (method_exists($this, $method = 'set'.ucfirst($var)))
			{
				$this->{$method}($setting);
			}
			elseif (property_exists($this, $var))
			{
				$this->{$var} = $setting;
			}
		}
	}

	/**
	 * Get the resource URL
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function getResource()
	{
		// Get any URL query params
		$query = array();
		if ($this->method == 'GET')
		{
			$query += $this->params;
		}

		// Build the URL
		$resource = $this->resource.strpos($this->resource, '?') === false ? '?' : '&';
		$resource .= str_replace('%3A', ':', is_string($query) ? $query : http_build_query($query));

		return $resource;
	}

	/**
	 * Create cURL resource
	 *
	 * @param   string  $resource
	 * @return  resource
	 *
	 * @since  1.1.0
	 */
	public function getConnection($resource)
	{
		// Only set follow location if not running securely
		if ( ! ini_get('safe_mode') && ! ini_get('open_basedir'))
		{
			if ( ! isset($this->options[CURLOPT_FOLLOWLOCATION]))
			{
				$this->setOption(CURLOPT_FOLLOWLOCATION, true);
			}
		}

		// Create connection and set options
		$connection = curl_init($resource);

		// Set the method, headers & options on the connection
		$options = $this->options;
		$options[CURLOPT_CUSTOMREQUEST]  = $this->method;
		$options[CURLOPT_HTTPHEADER]     = $this->headers;
		$options[CURLOPT_HEADER]         = true;
		curl_setopt_array($connection, $options);

		return $connection;
	}

	/**
	 * Set the HTTP request method
	 *
	 * @param   string  $method
	 * @return  Curl
	 *
	 * @since  2.0.0
	 */
	public function setMethod($method)
	{
		$this->method = strtoupper($method);
		return $this;
	}

	/**
	 * Set the request parameters
	 *
	 * @param   array  $params
	 * @return  Curl
	 *
	 * @since  2.0.0
	 */
	public function setParams(array $params)
	{
		$this->params = $params + $this->params;
		return $this;
	}

	/**
	 * Set single request parameters
	 *
	 * @param   string  $param
	 * @param   string  $value
	 * @return  Curl
	 *
	 * @since  2.0.0
	 */
	public function setParam($param, $value)
	{
		$this->params[$param] = $value;
		return $this;
	}

	/**
	 * Set the request headers
	 *
	 * @param   array  $headers
	 * @return  Curl
	 *
	 * @since  2.0.0
	 */
	public function setHeaders(array $headers)
	{
		$this->headers = $headers + $this->headers;
		return $this;
	}

	/**
	 * Sets a single request header
	 *
	 * @param   string  $header
	 * @return  Curl
	 *
	 * @since  2.0.0
	 */
	public function setHeader($header)
	{
		$this->headers[] = $header;
		return $this;
	}

	/**
	 * Set the cURL request options
	 *
	 * @param   array  $options
	 * @return  Curl
	 *
	 * @since  2.0.0
	 */
	public function setOptions(array $options)
	{
		$this->options = $options + $this->options;
		return $this;
	}

	/**
	 * Sets a single cURL option
	 *
	 * @param   string  $option
	 * @param   string  $value
	 * @return  Curl
	 *
	 * @since  2.0.0
	 */
	public function setOption($option, $value)
	{
		$this->options[$option] = $value;
		return $this;
	}

	/**
	 * Set authentication to an http server
	 *
	 * @param   string  $username
	 * @param   string  $password
	 * @param   int     $type
	 * @return  Curl
	 *
	 * @since  1.1.0
	 */
	public function setHttpLogin($username, $password, $type = CURLAUTH_ANY)
	{
		$this->setOptions(array(
			CURLOPT_HTTPAUTH  => $type,
			CURLOPT_USERPWD   => $username.':'.$password,
		));

		return $this;
	}

	public function encode(array $params, $type = 'queryString')
	{
		return http_build_query($this->params, null, '&');
	}

	/**
	 * Executes the cURL request
	 *
	 * @return  Curl
	 *
	 * @since  2.0.0
	 */
	public function execute()
	{
		$this->activate();

		$connection = $this->getConnection($this->getResource());
		if ($this->method !== 'GET')
		{
			curl_setopt($connection, CURLOPT_POSTFIELDS, $this->encode($this->params));
		}

		// Execute the request & and hide all output
		$body = curl_exec($connection);
		$info = curl_getinfo($connection);
		$mime = isset($this->headers['Accept']) ? $this->headers['Accept'] : 'text/plain';
		$httpCode = isset($info['http_code']) ? $info['http_code'] : 200;

		if ($body === false)
		{
			throw new Exception\Base(curl_errno($connection).': '.curl_error($connection));
		}

		// split the body into raw headers & actual body
		$rawHeaders = explode("\n", str_replace("\r", '', substr($body, 0, $info['header_size'])));
		$body = substr($body, $info['header_size']);

		// Turn into exception when an error HTTP status code was returned
		if ($httpCode >= 400)
		{
			throw $this->app->forge('Request.Exception.'.$httpCode, $body, $httpCode);
		}

		// Convert the header data into an array
		$headers = array();
		foreach ($rawHeaders as $header)
		{
			$header = explode(':', $header, 2);
			if (isset($header[1]))
			{
				$headers[trim($header[0])] = trim($header[1]);
			}
		}

		// Turn the whole thing into a response object
		$this->response = $this->app->forge('Response', $body, $httpCode, $headers);

		// Close & deactivate request
		curl_close($connection);
		$this->deactivate();

		return $this;
	}
}
