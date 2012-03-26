<?php

namespace Fuel\Core\Job\Queue\Storage;

interface Storable
{
	public function get_jobs();

	public function save_jobs();
}
