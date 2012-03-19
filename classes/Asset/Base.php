<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace Fuel\Core\Asset;
use Fuel\Kernel\Application;
use Fuel\Kernel\Data;

/**
 * The Asset class allows you to easily work with your apps assets.
 * It allows you to specify multiple paths to be searched for the
 * assets.
 *
 * You can configure the paths by copying the core/config/asset.php
 * config file into your app/config folder and changing the settings.
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
	 * @var  array  the asset paths to be searched
	 *
	 * @since  1.0.0
	 */
	protected $asset_paths = array(
		'css' => array(),
		'js' => array(),
		'img' => array(),
	);

	/**
	 * @var  array  the sub-folders to be searched
	 *
	 * @since  1.0.0
	 */
	protected $path_folders = array(
		'css' => 'css/',
		'js' => 'js/',
		'img' => 'img/',
	);

	/**
	 * @var  array  holds the groups of assets
	 *
	 * @since  1.0.0
	 */
	protected $groups = array();

	/**
	 * Parse the config and initialize the object instance
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

		// Take out a few config variables
		$paths = isset($this->config['paths']) ? (array) $this->config['paths'] : array();
		unset($this->config['paths']);
		$folders = isset($this->config['folders']) ? (array) $this->config['folders'] : array();
		unset($this->config['folders']);

		// Order of this addition is important, do not change this.
		$this->config = $app->forge('Object_Config', 'asset', $this->config);

		$this->config
			// Set defaults
			->add(array(
				'add_mtime' => true,
				'asset_url' => '/',
				'indent' => str_repeat($this->config['indent_with'] ?: '', $this->config['indent_level'] ?: 0),
				'auto_render' => true,
				'fail_silently' => false,
			))
			// Add validators
			->validators(array());

		// global search paths
		foreach ($paths as $path)
		{
			$this->add_path($path);
		}

		// per-type search paths
		foreach ($folders as $type => $folders)
		{
			foreach ((array) $folders as $path)
			{
				$this->add_path($path, $type);
			}
		}
	}

	/**
	 * Adds the given path to the front of the asset paths array.  It adds paths
	 * in a way so that asset paths are used First in Last Out.
	 *
	 * @param   string  $path  the path to add
	 * @param   string  $type  optional path type (js, css or img)
	 * @return  object  current instance
	 *
	 * @since  1.0.0
	 */
	public function add_path($path, $type = null)
	{
		is_null($type) and $type = $this->path_folders;

		if( is_array($type))
		{
			foreach ($type as $key => $folder)
			{
				is_numeric($key) and $key = $folder;
				array_unshift(
					$this->asset_paths[$key],
					str_replace('../', '', rtrim($path, '/')).'/'.rtrim($folder, '/').'/'
				);
			}
		}
		else
		{
			array_unshift(
				$this->asset_paths[$type],
				str_replace('../', '', rtrim($path, '/')).'/'
			);
		}

		return $this;
	}

	/**
	 * Removes the given path from the asset paths array
	 *
	 * @param   string  $path  the path to remove
	 * @param   string  $type  optional path type (js, css or img)
	 * @return  object  current instance
	 *
	 * @since  1.0.0
	 */
	public function remove_path($path, $type = null)
	{
		is_null($type) and $type = $this->path_folders;

		if( is_array($type))
		{
			foreach ($type as $key => $folder)
			{
				is_numeric($key) and $key = $folder;
				$found = array_search(
						str_replace('../', '', rtrim($path,'/').'/'.rtrim($folder, '/').'/'),
						$this->asset_paths[$key]
				);
				if ($found !== false)
				{
					unset($this->asset_paths[$key][$found]);
				}
			}
		}
		else
		{
			if (($key = array_search(str_replace('../', '', rtrim($path,'/')), $this->asset_paths[$type])) !== false)
			{
				unset($this->asset_paths[$type][$key]);
			}
		}

		return $this;
	}

	/**
	 * Renders the given group.  Each tag will be separated by a line break.
	 * You can optionally tell it to render the files raw.  This means that
	 * all CSS and JS files in the group will be read and the contents included
	 * in the returning value.
	 *
	 * @param   mixed   $group  the group to render
	 * @param   bool    $raw    whether to return the raw file or not
	 * @return  string  the group's output
	 *
	 * @since  1.0.0
	 */
	public function render($group = null, $raw = false)
	{
		is_null($group) and $group = '_default_';

		if (is_string($group))
		{
			isset($this->groups[$group]) and $group = $this->groups[$group];
		}

		is_array($group) or $group = array();

		$css = '';
		$js = '';
		$img = '';
		foreach ($group as $key => $item)
		{
			$type = $item['type'];
			$filename = $item['file'];
			$attr = $item['attr'];

			// only do a file search if the asset is not a URI
			if ( ! preg_match('|^(\w+:)?//|', $filename))
			{
				// and only if the asset is local to the applications base_url
				$asset_url = $this->config['asset_url'];
				if ( ! preg_match('|^(\w+:)?//|', $asset_url)
					or strpos($asset_url, $this->app->env->base_url) === 0)
				{
					if ( ! ($file = $this->find_file($filename, $type)))
					{
						if ( ! $this->config['fail_silently'])
						{
							throw new \OutOfBoundsException('Could not find asset: '.$filename);
						}
						continue;
					}

					$raw or $file = $asset_url.$file.($this->config['add_mtime'] ? '?'.filemtime($file) : '');
				}
				else
				{
					$raw or $file = $asset_url.$filename;
				}
			}
			else
			{
				$file = $filename;
			}

			switch($type)
			{
				case 'css':
					$attr['type'] = 'text/css';
					if ($raw)
					{
						return html_tag('style', $attr, PHP_EOL.file_get_contents($file).PHP_EOL).PHP_EOL;
					}
					if ( ! isset($attr['rel']) or empty($attr['rel']))
					{
						$attr['rel'] = 'stylesheet';
					}
					$attr['href'] = $file;

					$css .= $this->config['indent'].html_tag('link', $attr).PHP_EOL;
				break;
				case 'js':
					$attr['type'] = 'text/javascript';
					if ($raw)
					{
						return html_tag('script', $attr, PHP_EOL.file_get_contents($file).PHP_EOL).PHP_EOL;
					}
					$attr['src'] = $file;

					$js .= $this->config['indent'].html_tag('script', $attr, '').PHP_EOL;
				break;
				case 'img':
					$attr['src'] = $file;
					$attr['alt'] = isset($attr['alt']) ? $attr['alt'] : '';

					$img .= html_tag('img', $attr );
				break;
			}

		}

		// return them in the correct order
		return $css.$js.$img;
	}

	/**
	 * CSS
	 *
	 * Either adds the stylesheet to the group, or returns the CSS tag.
	 *
	 * @param   mixed          $stylesheets  The file name, or an array files.
	 * @param   array          $attr         An array of extra attributes
	 * @param   string         $group        The asset group name
	 * @param   bool           $raw
	 * @return  string|object  Rendered asset or current instance when adding to group
	 *
	 * @since  1.0.0
	 */
	public function css($stylesheets = array(), $attr = array(), $group = null, $raw = false)
	{
		static $temp_group = 1000000;

		if ($group === null)
		{
			$render = $this->config['auto_render'];
			$group = $render ? (string) (++$temp_group) : '_default_';
		}
		else
		{
			$render = false;
		}

		$this->_parse_assets('css', $stylesheets, $attr, $group);

		if ($render)
		{
			return $this->render($group, $raw);
		}

		return $this;
	}

	/**
	 * JS
	 *
	 * Either adds the javascript to the group, or returns the script tag.
	 *
	 * @param   mixed          $scripts  The file name, or an array files.
	 * @param   array          $attr     An array of extra attributes
	 * @param   string         $group    The asset group name
	 * @param   bool           $raw
	 * @return  string|object  Rendered asset or current instance when adding to group
	 *
	 * @since  1.0.0
	 */
	public function js($scripts = array(), $attr = array(), $group = null, $raw = false)
	{
		static $temp_group = 2000000;

		if ( ! isset($group))
		{
			$render = $this->config['auto_render'];
			$group = $render ? (string) (++$temp_group) : '_default_';
		}
		else
		{
			$render = false;
		}

		$this->_parse_assets('js', $scripts, $attr, $group);

		if ($render)
		{
			return $this->render($group, $raw);
		}

		return $this;
	}

	/**
	 * Img
	 *
	 * Either adds the image to the group, or returns the image tag.
	 *
	 * @param   mixed          $images  The file name, or an array files.
	 * @param   array          $attr    An array of extra attributes
	 * @param   string         $group   The asset group name
	 * @return  string|object  Rendered asset or current instance when adding to group
	 *
	 * @since  1.0.0
	 */
	public function img($images = array(), $attr = array(), $group = null)
	{
		static $temp_group = 3000000;

		if ( ! isset($group))
		{
			$render = $this->config['auto_render'];
			$group = $render ? (string) (++$temp_group) : '_default_';
		}
		else
		{
			$render = false;
		}

		$this->_parse_assets('img', $images, $attr, $group);

		if ($render)
		{
			return $this->render($group);
		}

		return $this;
	}

	/**
	 * Find File
	 *
	 * Locates a file in all the asset paths.
	 *
	 * @param   string  $file   The filename to locate
	 * @param   string  $type   The sub-folder to look in (optional)
	 * @param   string  $folder
	 * @return  mixed   Either the path to the file or false if not found
	 *
	 * @since  1.0.0
	 */
	public function find_file($file, $type, $folder = '')
	{
		foreach ($this->asset_paths[$type] as $path)
		{
			empty($folder) or $folder = trim($folder, '/').'/';

			if (is_file($path.$folder.ltrim($file, '/')))
			{
				$file = $path.$folder.ltrim($file, '/');
				strpos($file, DOCROOT) === 0 and $file = substr($file, strlen(DOCROOT));

				return $file;
			}
		}

		return false;
	}

	/**
	 * Get File
	 *
	 * Locates a file in all the asset paths, and return it relative to the docroot
	 *
	 * @param   string  $file  The filename to locate
	 * @param   string  $type  The sub-folder to look in (optional)
	 * @param   string  $folder
	 * @return  mixed   Either the path to the file or false if not found
	 *
	 * @since  1.0.0
	 */
	public function get_file($file, $type, $folder = '')
	{
		if ($file = $this->find_file($file, $type, $folder))
		{
			return $this->config['asset_url'].$file;
		}

		return false;
	}

	/**
	 * Parse Assets
	 *
	 * Pareses the assets and adds them to the group
	 *
	 * @param   string  $type    The asset type
	 * @param   mixed   $assets  The file name, or an array files.
	 * @param   array   $attr    An array of extra attributes
	 * @param   string  $group   The asset group name
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	protected function _parse_assets($type, $assets, $attr, $group)
	{
		if ( ! is_array($assets))
		{
			$assets = array($assets);
		}

		foreach ($assets as $asset)
		{
			$this->groups[$group][] = array(
				'type' => $type,
				'file' => $asset,
				'attr' => (array) $attr
			);
		}
	}
}
