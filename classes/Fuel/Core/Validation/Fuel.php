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
		foreach ($validators as $key => $validator)
		{
			$this->validate($key, $validator);
		}

		// Add the Validatable as a RuleSet
		$this->addRuleSet($v);

		return $this;
	}
}
