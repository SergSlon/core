<?php

namespace Fuel\Core\Job\Queue;

class Base
{
	/**
	 * @var  \Fuel\Kernel\Application\Base
	 */
	protected $app;

	/**
	 * @var  Storage\Storable
	 */
	protected $storage;
}
