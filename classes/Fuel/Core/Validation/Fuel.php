<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace Fuel\Core\Validation;

use Fuel\Kernel\Application;
use Fuel\Validation\Base;

/**
 * Validation class
 *
 * @package  Fuel\Core
 *
 * @since  1.0.0
 */
class Fuel extends Base
{
	/**
	 * @var  \Fuel\Kernel\Application\Base
	 *
	 * @since  2.0.0
	 */
	public $app;

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

		// Fetch the classes to use from the DiC
		$this->config['valueClass'] = $app->dic->getClass('Validation.Value');
		$this->config['errorClass'] = $app->dic->getClass('Validation.Error');

		// Attempt to fetch a language-key prefix from config, default to 'validation.'
		$this->config['languagePrefix'] = rtrim($app->getConfig('validation.languagePrefix', 'validation'), '.').'.';

		// When a language file is given: load it
		if (isset($this->config['langFile']))
		{
			$app->getLanguage()->load($this->config['langFile']);
		}
	}

	/**
	 * Add validators from Validatable extension, which is also added as a RuleSet
	 *
	 * @param   Validatable  $v
	 * @return  Fuel
	 *
	 * @since  2.0.0
	 */
	public function addValidatable(Validatable $v)
	{
		// Add the validators from the Validatable
		$validators = $v->_validation();
		foreach ($validators as $validation)
		{
			list($key, $validator, $label) = array_pad($validation, 3, null);
			$this->validate($key, $validator, $label);
		}

		// Add the Validatable as a RuleSet
		$this->addRuleSet($v);

		return $this;
	}

	/**
	 * Get an error message for an error key
	 *
	 * @param   string  $error
	 * @param   mixed   $default
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function getMessage($error, $default = null)
	{
		if (isset($this->messages[$error]))
		{
			return $this->messages[$error];
		}

		$languagePrefix = isset($this->config['languagePrefix']) ? $this->config['languagePrefix'] : '';
		return $this->app->getLanguage($languagePrefix, $error, $default);
	}
}
