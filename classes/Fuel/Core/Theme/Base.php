<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace Fuel\Core\Theme;
use Fuel\Kernel\Application;
use Fuel\Kernel\Data;

/**
 * Handles loading theme views and assets.
 *
 * @package  Fuel\Core
 *
 * @since  1.1.0
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
	 * @var  \Fuel\Core\Asset\Base  Asset instance for this theme instance
	 *
	 * @since  1.2
	 */
	public $asset = null;

	/**
	 * @var  array  Possible locations for themes
	 *
	 * @since  1.1
	 */
	protected $paths = array();

	/**
	 * @var  \Fuel\Kernel\View\Viewable  View instance for this theme instance template
	 *
	 * @since  1.2
	 */
	public $template = null;

	/**
	 * @var  array  Currently active theme
	 *
	 * @since  1.1
	 */
	protected $active = array(
		'name' => null,
		'path' => null,
		'asset_base' => false,
		'asset_path' => false,
		'info' => array(),
	);

	/**
	 * @var  array  Fallback theme
	 *
	 * @since  1.1
	 */
	protected $fallback = array(
		'name' => null,
		'path' => null,
		'asset_base' => false,
		'asset_path' => false,
		'info' => array(),
	);

	/**
	 * @var  array  Storage for defined template partials
	 *
	 * @since  1.2
	 */
	protected $partials = array();

	/**
	 * Sets up the theme object.  If a config is given, it will not use the config
	 * file.
	 *
	 * @param   array  $config  Optional config override
	 *
	 * @since  1.1
	 */
	public function __construct($config = null)
	{
		$this->config = $config;
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
		$this->config = $app->forge('Object_Config', 'theme', $this->config);

		$this->config
			// Set defaults
			->add(array(
				'active' => 'default',
				'fallback' => 'default',
				'paths' => array(),
				'assets_folder' => 'assets',
				'view_ext' => '.php',
				'require_info_file' => false,
				'info_file_name' => 'theme.info',
				'info_file_type' => 'json',
			))
			// Add validators
			->validators(array());

		// define the default theme paths...
		$this->add_paths($this->config['paths']);

		// create a unique asset instance for this theme instance...
		$this->asset = $app->forge('Asset', array('paths' => array()));

		// and set the active and the fallback theme
		$this->active($this->config['active']);
		$this->fallback($this->config['fallback']);
	}

	/**
	 * Sets the currently active theme.  Will return the currently active
	 * theme.  It will throw a \ThemeException if it cannot locate the theme.
	 *
	 * @param   string  $theme  Theme name to set active
	 * @return  array   The theme array
	 *
	 * @since  1.1
	 */
	public function active($theme = null)
	{
		return $this->set_theme($theme, 'active');
	}

	/**
	 * Sets the fallback theme.  This theme will be used if a view or asset
	 * cannot be found in the active theme.  Will return the fallback
	 * theme.  It will throw a \ThemeException if it cannot locate the theme.
	 *
	 * @param   string  $theme  Theme name to set active
	 * @return  array   The theme array
	 *
	 * @since  1.1
	 */
	public function fallback($theme = null)
	{
		return $this->set_theme($theme, 'fallback');
	}

	/**
	 * Loads a view from the currently active theme, the fallback theme, or
	 * via the standard FuelPHP cascading file system for views
	 *
	 * @param   string  $view         View name
	 * @param   array   $data         View data
	 * @param   bool    $auto_filter  Auto filter the view data
	 * @return  \Fuel\Kernel\View\Viewable  New View object
	 *
	 * @since  1.1
	 */
	public function view($file, $data = array(), $auto_filter = null)
	{
		if ($this->active['path'] === null)
		{
			throw new \RuntimeException('You must set an active theme.');
		}

		$view = $this->app->forge('View', null, $data, $auto_filter);
		$view->set_filename($this->find_file($file), true);

		return $view;
	}

	/**
	 * Fetch a Presenter's View path from the theme
	 *
	 * @param   string  $class
	 * @param   string  $method
	 * @param   array   $data
	 * @return  \Fuel\Core\Presenter\Base
	 *
	 * @since  1.0.0
	 */
	public function presenter($class, $method = 'view', array $data = array())
	{
		$class = $this->app->get_class($class);
		$presenter = new $class($method, $data);

		// Some Reflection trickery to prevent the default set_filename usage
		$prop = new \ReflectionProperty($presenter, '_path');
		$prop->setAccessible(true);
		$path = $prop->getValue($presenter);
		$prop->setValue($presenter, null);

		// Set the app and the Theme View path
		$presenter->_set_app($this->app);
		$presenter->set_filename($this->find_file($path ?: $presenter->default_path()), true);

		return $presenter;
	}

	/**
	 * Loads an asset from the currently loaded theme.
	 *
	 * @param   string  $path  Relative path to the asset
	 * @return  string  Full asset URL or path if outside docroot
	 *
	 * @since  1.1
	 */
	public function asset_path($path)
	{
		if ($this->active['path'] === null)
		{
			throw new \RuntimeException('You must set an active theme.');
		}

		if ($this->active['asset_base'])
		{
			return $this->active['asset_base'].$path;
		}
		else
		{
			return $this->active['path'].$path;
		}
	}

	/**
	 * Sets a template for a theme
	 *
	 * @param   string  $template Name of the template view
	 * @return  \Fuel\Kernel\View\Viewable
	 *
	 * @since  1.1
	 */
	public function set_template($template)
	{
		// make sure the template is a View
		if (is_string($template))
		{
			$this->template = $this->view($template);
		}
		else
		{
			$this->template = $template;
		}

		// return the template view for chaining
		return $this->template;
	}

	/**
	 * Get the template view so it can be manipulated
	 *
	 * @return  string|\Fuel\Kernel\View\Viewable
	 * @throws  \RuntimeException
	 *
	 * @since  1.1
	 */
	public function get_template()
	{
		// make sure the partial entry exists
		if (empty($this->template))
		{
			throw new \RuntimeException('No valid template could be found. Use set_template() to define a page template.');
		}

		// return the template
		return $this->template;
	}

	/**
	 * Render the partials and the theme template
	 *
	 * @return  string|\Fuel\Kernel\View\Viewable
	 * @throws  \RuntimeException
	 *
	 * @since  1.1
	 */
	public function render()
	{
		// make sure the partial entry exists
		if (empty($this->template))
		{
			throw new \RuntimeException('No valid template could be found. Use set_template() to define a page template.');
		}

		// pre-process all defined partials
		foreach ($this->partials as $key => $partials)
		{
			$output = '';
			foreach ($partials as $index => $partial)
			{
				// render the partial
				$output .= $partial->render();
			}

			// store the rendered output
			$this->partials[$key] = $output;
		}

		// do we have a template view?
		if (empty($this->template))
		{
			throw new \ThemeException('No valid template could be found. Use set_template() to define a page template.');
		}

		// assign the partials to the template
		$this->template->set('partials', $this->partials, false);

		// return the template
		return $this->template;
	}

	/**
	 * Sets a partial for the current template
	 *
	 * @param   string  $section    Name of the partial section in the template
	 * @param   string|\Fuel\Kernel\View\Viewable  $view  View, or name of the view
	 * @param   bool    $overwrite  If true overwrite any already defined partials for this section
	 * @return  \Fuel\Kernel\View\Viewable
	 *
	 * @since  1.1
	 */
	public function set_partial($section, $view, $overwrite = false)
	{
		// make sure the partial entry exists
		array_key_exists($section, $this->partials) or $this->partials[$section] = array();

		// make sure the partial is a view
		if (is_string($view))
		{
			$name = $view;
			$view = $this->view($view);
		}
		else
		{
			$name = 'partial_'.count($this->partials[$section]);
		}

		// store the partial
		if ($overwrite)
		{
			$this->partials[$section] = array($name => $view);
		}
		else
		{
			$this->partials[$section][$name] = $view;
		}

		// return the partial view object for chaining
		return $this->partials[$section][$name];
	}

	/**
	 * Get a partial so it can be manipulated
	 *
	 * @param   string  $section  Name of the partial section in the template
	 * @param   string  $view     name of the view
	 * @return  \Fuel\Kernel\View\Viewable
	 * @throws  \OutOfBoundsException
	 *
	 * @since  1.1
	 */
	public function get_partial($section, $view)
	{
		// make sure the partial entry exists
		if ( ! array_key_exists($section, $this->partials) or ! array_key_exists($view, $this->partials[$section]))
		{
			throw new \OutOfBoundsException(sprintf('No partial named "%s" can be found in the "%s" section.', $view, $section));
		}

		return $this->partials[$section][$view];
	}

	/**
	 * Adds the given path to the theme search path.
	 *
	 * @param   string  $path  Path to add
	 * @return  void
	 *
	 * @since  1.1
	 */
	public function add_path($path)
	{
		$this->paths[] = rtrim($path, '/\\').'/';
	}

	/**
	 * Adds the given paths to the theme search path.
	 *
	 * @param   array  $paths  Paths to add
	 * @return  void
	 *
	 * @since  1.1
	 */
	public function add_paths(array $paths)
	{
		array_walk($paths, array($this, 'add_path'));
	}

	/**
	 * Gets an option for the active theme.
	 *
	 * @param   string  $option   Option to get
	 * @param   mixed   $default  Default value
	 * @return  mixed
	 *
	 * @since  1.1
	 */
	public function option($option, $default = null)
	{
		if ( ! isset($this->active['info']['options'][$option]))
		{
			return $default;
		}

		return $this->active['info']['options'][$option];
	}

	/**
	 * Sets an option for the active theme.
	 *
	 * NOTE: This does NOT update the theme.info file.
	 *
	 * @param   string  $option   Option to get
	 * @param   mixed   $value    Value
	 * @return  $this
	 *
	 * @since  1.1
	 */
	public function set_option($option, $value)
	{
		$this->active['info']['options'][$option] = $value;

		return $this;
	}

	/**
	 * Finds the given theme by searching through all of the theme paths.  If
	 * found it will return the path, else it will return `false`.
	 *
	 * @param   string  $theme  Theme to find
	 * @return  string|bool  Path or false if not found
	 *
	 * @since  1.1
	 */
	public function find($theme)
	{
		foreach ($this->paths as $path)
		{
			if (is_dir($path.$theme))
			{
				return $path.$theme.'/';
			}
		}

		return false;
	}

	/**
	 * Gets an array of all themes in all theme paths, sorted alphabetically.
	 *
	 * @return  array
	 *
	 * @since  1.1
	 */
	public function all()
	{
		$themes = array();
		foreach ($this->paths as $path)
		{
			foreach(glob($path.'*', GLOB_ONLYDIR) as $theme)
			{
				$themes[] = basename($theme);
			}
		}
		sort($themes);

		return $themes;
	}

	/**
	 * Get a value from the info array
	 *
	 * @param   string|null   $var
	 * @param   mixed         $default
	 * @param   string|array  $theme
	 * @return  mixed
	 *
	 * @since  1.1
	 */
	public function info($var = null, $default = null, $theme = null)
	{
		// if no theme is given
		if ($theme === null)
		{
			// if no var to search is given return the entire info array
			if ($var === null)
			{
				return $this->active['info'];
			}

			// find the value in the active theme info
			if (array_get_dot_key($var, $this->active['info'], $value))
			{
				return $value;
			}
			// and if not found, check the fallback
			elseif (array_get_dot_key($var, $this->fallback['info'], $value))
			{
				return $value;
			}
		}
		// or if we have a specific theme
		else
		{
			// fetch the info from that theme
			$info = $this->all_info($theme);

			if ($var === null)
			{
				return $info;
			}

			return array_get_dot_key($var, $info, $return) ? $return : $default;
		}

		// not found, return the given default value
		return $default;
	}

	/**
	 * Reads in the theme.info file for the given (or active) theme.
	 *
	 * @param   string  $theme  Name of the theme (null for active)
	 * @return  array   Theme info array
	 * @throws  \OutOfBoundsException
	 * @throws  \RuntimeException
	 * @throws  \InvalidArgumentException
	 *
	 * @since  1.1
	 */
	public function all_info($theme = null)
	{
		if ($theme === null)
		{
			$theme = $this->active;
		}

		if (is_array($theme))
		{
			$path = $theme['path'];
			$name = $theme['name'];
		}
		else
		{
			$path = $this->find($theme);
			$name = $theme;
			$theme = array(
				'name' => $name,
				'path' => $path
			);
		}

		if ( ! $path)
		{
			throw new \RuntimeException(sprintf('Could not find theme "%s".', $theme));
		}

		if (($file = $this->find_file($this->config['info_file_name'], array($theme))) == $this->config['info_file_name'])
		{
			if ($this->config['require_info_file'])
			{
				throw new \OutOfBoundsException(sprintf('Theme "%s" is missing "%s".', $name, $this->config['info_file_name']));
			}
			else
			{
				return array();
			}
		}

		$type = strtolower($this->config['info_file_type']);
		switch ($type)
		{
			case 'ini':
				$info = parse_ini_file($file, true);
			break;

			case 'json':
				$info = json_decode(file_get_contents($file), true);
			break;

			case 'yaml':
				$info = $this->app->forge('Format', file_get_contents($file), 'yaml')->to_array();
			break;

			case 'php':
				$info = include($file);
			break;

			default:
				throw new \InvalidArgumentException(sprintf('Invalid info file type "%s".', $type));
		}

		return $info;
	}

	/**
	 * Find the absolute path to a file in a set of Themes.  You can optionally
	 * send an array of themes to search.  If you do not, it will search active
	 * then fallback (in that order).
	 *
	 * @param   string  $view    name of the view to find
	 * @param   array   $themes  optional array of themes to search
	 * @return  string  absolute path to the view
	 * @throws  \OutOfBoundsException  when not found
	 *
	 * @since  1.1
	 */
	protected function find_file($view, $themes = null)
	{
		if ($themes === null)
		{
			$themes = array($this->active, $this->fallback);
		}

		foreach ($themes as $theme)
		{
			$ext   = pathinfo($view, PATHINFO_EXTENSION) ?
				'.'.pathinfo($view, PATHINFO_EXTENSION) : $this->config['view_ext'];
			$file  = (pathinfo($view, PATHINFO_DIRNAME) !== '.'
					? str_replace('\\', '/', pathinfo($view, PATHINFO_DIRNAME)).'/'
					: '')
				.pathinfo($view, PATHINFO_FILENAME);
			if (empty($theme['find_file']))
			{
				if (is_file($path = $theme['path'].$file.$ext))
				{
					return $path;
				}
			}
			else
			{
				if (file_exists($path = 'fuel://themes/'.$theme['path'].$file.$ext))
				{
					return $path;
				}
			}
		}

		// not found, return the viewname to fall back to the standard View processing
		return $view;
	}

	/**
	 * Sets a  theme.
	 *
	 * @param   string  $theme  Theme name to set active
	 * @param   string  $type   name of the internal theme array to set
	 * @return  array   The theme array
	 *
	 * @since  1.1
	 */
	protected function set_theme($theme = null, $type = 'active')
	{
		// remove the defined theme asset paths from the asset instance
		empty($this->active['asset_path']) or $this->asset->remove_path($this->active['asset_path']);
		empty($this->fallback['asset_path']) or $this->asset->remove_path($this->fallback['asset_path']);

		// set the fallback theme
		if ($theme !== null)
		{
			$this->{$type} = $this->create_theme_array($theme);
		}

		// add the asset paths to the asset instance
		empty($this->active['asset_path']) or $this->asset->add_path($this->active['asset_path']);
		empty($this->fallback['asset_path']) or $this->asset->add_path($this->fallback['asset_path']);

		return $this->{$type};
	}

	/**
	 * Creates a theme array by locating the given theme and setting all of the
	 * option.  It will throw a \ThemeException if it cannot locate the theme.
	 *
	 * @param   string  $theme  Theme name to set active
	 * @return  array   The theme array
	 * @throws  \OutOfBoundsException
	 * @throws  \RuntimeException
	 *
	 * @since  1.1
	 */
	protected function create_theme_array($theme)
	{
		if ( ! is_array($theme))
		{
			if ( ! $path = $this->find($theme))
			{
				throw new \OutOfBoundsException(sprintf('Theme "%s" could not be found.', $theme));
			}

			$theme = array(
				'name' => $theme,
				'path' => $path,
			);
		}
		else
		{
			if ( ! isset($theme['name']) or ! isset($theme['path']))
			{
				throw new \RuntimeException('Theme name and path must be given in array config.');
			}
		}

		// load the theme info file
		if ( ! isset($theme['info']))
		{
			$theme['info'] = $this->all_info($theme);
		}

		if ( ! isset($theme['asset_base']))
		{
			// determine the asset location and base URL
			$assets_folder = rtrim($this->config['assets_folder'], '\\/').'/';

			// all theme files are inside the docroot
			if (strpos($theme['path'], DOCROOT) === 0 and is_dir($theme['path'].$assets_folder))
			{
				$theme['asset_path'] = $theme['path'].$assets_folder;
				$theme['asset_base'] = str_replace(DOCROOT, '', $theme['asset_path']);
			}

			// theme views and templates are outside the docroot
			else
			{
				$theme['asset_base'] = $assets_folder.$theme['name'].'/';
			}
		}

		if ( ! isset($theme['asset_path']) and strpos($theme['asset_base'], '://') === false)
		{
			$theme['asset_path'] = DOCROOT.$theme['asset_base'];
		}
		else
		{
			$theme['asset_path'] = false;
		}

		// always uses forward slashes (DS is a backslash on Windows)
		$theme['asset_base'] = str_replace('\\', '/', $theme['asset_base']);

		return $theme;
	}
}
