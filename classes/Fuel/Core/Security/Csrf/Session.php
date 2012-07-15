<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace Fuel\Core\Security\Csrf;

use Fuel\Kernel\Application;
use Fuel\Aliases\Security\Csrf\Base;

/**
 * Session ID based CSRF checking
 *
 * @package  Fuel\Core
 *
 * @since  2.0.0
 */
class Session extends Base
{
	/**
	 * @var  string  token key used in cookie
	 *
	 * @since  2.0.0
	 */
	protected $tokenKey = 'csrfToken';

	/**
	 * @var  \Fuel\Core\Session\Base
	 *
	 * @since  2.0.0
	 */
	protected $session;

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
		parent::_setApp($app);

		$this->tokenKey = $app->getConfig('security.csrfTokenKey', $this->tokenKey);
		$this->session = $app->getObject('Session', $app->getConfig('security.csrfSession', null));
	}

	/**
	 * Only do forced rotations, no automatic
	 *
	 * @param   bool  $forceReset
	 * @return  Session
	 *
	 * @since  2.0.0
	 */
	public function updateToken($forceReset = false)
	{
		if ($forceReset)
		{
			$this->session->rotateId();
		}

		return $this;
	}

	/**
	 * Get the Session ID as the token
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function getToken()
	{
		return $this->session->getId();
	}

	/**
	 * Check the submitted token against the Session ID
	 *
	 * @param   null|string  $value
	 * @return  bool
	 *
	 * @since  2.0.0
	 */
	public function checkToken($value = null)
	{
		$value = $value ?: $this->app->getActiveRequest()->input->getParam($this->tokenKey, null);
		return $value === $this->getToken();
	}
}
