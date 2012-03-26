<?php

namespace Fuel\Core\Job;

class Job
{
	/**
	 * @var  int  timestamp after which this job is executed
	 *
	 * @since  2.0.0
	 */
	public $after;

	/**
	 * @var  int  number of seconds after which the job is to be repeated, null for no repeat
	 *
	 * @since  2.0.0
	 */
	public $period;

	/**
	 * @var  string  error message if it failed on the last attempt
	 */
	public $error;

	/**
	 * @var  callback  function to execute as the job
	 *
	 * @since  2.0.0
	 */
	public $callback;

	/**
	 * @var  array  params to pass to the callback/task method
	 *
	 * @since  2.0.0
	 */
	public $params = array();

	/**
	 * @var  \Fuel\Core\Job\Queue\Base
	 */
	public $queue;

	/**
	 * Execute the job
	 *
	 * @param   array  $params
	 * @return  bool
	 * @throws  \Exception  of any type thrown by
	 *
	 * @since  2.0.0
	 */
	public function __invoke(array $params = null)
	{
		is_array($params) and $this->params = $params;

		// Duck out if it failed on the last attempt, if no execution time was set or if it hasn't passed yet
		if ($this->error or ! $this->after or time() < $this->after)
		{
			return false;
		}

		// Execute the job, save on success
		if ($this->callback)
		{
			$tasks = array($this->callback);
		}
		else
		{
			$tasks = array();
			$methods = get_class_methods($this);
			foreach ($methods as $m)
			{
				(substr($m, 0, 5) === 'task_') and $tasks[] = array($this, $m);
			}
		}

		// Run all tasks, throw generic exception when false is returned
		foreach ($tasks as $task)
		{
			if (call_user_func_array($task, $this->params) === false)
			{
				throw new Exception('Job failed without throwing an exception.');
			}
		}

		// Everything succeeded, mark as finished or set time for next job execution
		$this->after = $this->period ? time() + $this->period : null;

		return true;
	}
}
