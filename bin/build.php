<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\Application\AbstractCliApplication;

include_once __DIR__ . '/../vendor/autoload.php';

define('JPATH_ROOT', realpath(__DIR__ . '/..'));

/**
 * Class Build to build subtrees.
 *
 * @since 1.0
 */
class Build extends AbstractCliApplication
{
	/**
	 * Property lastOutput.
	 *
	 * @var  mixed
	 */
	protected $lastOutput = null;

	/**
	 * Property lastReturn.
	 *
	 * @var  mixed
	 */
	protected $lastReturn = null;

	/**
	 * Property master.
	 *
	 * @var  string
	 */
	protected $master = null;

	/**
	 * Property tag.
	 *
	 * @var  string
	 */
	protected $tag = null;

	/**
	 * Property subtrees.
	 *
	 * @var  array
	 */
	protected $subtrees = array(
		'data'       => 'Data',
		'datamapper' => 'DataMapper',
		'middleware' => 'Middleware',
		'compare'    => 'Compare',
		'database'   => 'Database'
	);

	/**
	 * Method to run this application.
	 *
	 * @return  void
	 */
	protected function doExecute()
	{
		$this->tag = $tag = $this->getArgument(0) or $this->stop('Please give me tag name.');

		$test = $this->input->get('t') ?: $this->input->get('test');

		$this->master = $master = $test ? 'test' : 'master';

		$this->exec('git fetch origin');

		$this->exec('git branch -D ' . $master);

		$this->exec('git checkout -b ' . $master);

		$this->exec('git merge staging');

		$this->exec('git tag -d ' . $tag);

		$this->exec('git push origin :refs/tags/' . $tag);

		$this->exec('git tag ' . $tag);

		$this->exec(sprintf('git push origin %s %s:%s staging:staging', $tag, $master, $master));

		foreach ($this->subtrees as $subtree => $namespace)
		{
			$this->splitTree($subtree, $namespace);
		}

		$this->out()->out('Split finish.');
	}

	/**
	 * Split Git subTree.
	 *
	 * @param string $subtree
	 * @param string $namespace
	 *
	 * @return  void
	 */
	protected function splitTree($subtree, $namespace)
	{
		$this->exec('git subtree split -P src/' . $namespace . ' -b sub-' . $subtree);

		$this->exec(sprintf('git branch -D master-%s', $subtree));

		$this->exec(sprintf('git remote add %s git@github.com:ventoviro/windwalker-%s.git', $subtree, $subtree));

		$this->exec(sprintf('git fetch %s', $subtree));

		$this->exec(sprintf('git checkout -b master-%s --track %s/master', $subtree, $subtree));

		$this->exec(sprintf('git merge sub-%s', $subtree));

		$this->exec(sprintf('git push %s master-%s:master', $subtree, $subtree));

		$this->exec(sprintf('git tag -d %s', $this->tag));

		$this->exec(sprintf('git tag %s', $this->tag));

		$this->exec(sprintf('git push %s %s', $subtree, $this->tag));

		$this->exec('git checkout ' . $this->master);
	}

	/**
	 * Exec a command.
	 *
	 * @param string $command
	 * @param array  $arguments
	 * @param array  $options
	 *
	 * @return  string
	 */
	protected function exec($command, $arguments = array(), $options = array())
	{
		$arguments = implode(' ', (array) $arguments);
		$options = implode(' ', (array) $options);

		$command = sprintf('%s %s %s', $command, $arguments, $options);

		$this->out('>> ' . $command);

		$return = exec(trim($command), $this->lastOutput, $this->lastReturn);

		$this->out($return);
	}

	/**
	 * getArgument
	 *
	 * @param integer $offset
	 * @param mixed   $default
	 *
	 * @return  mixed
	 */
	protected function getArgument($offset, $default = null)
	{
		if (! empty($this->input->args[$offset]))
		{
			return $this->input->args[$offset];
		}

		return $default;
	}

	/**
	 * stop
	 *
	 * @param string $msg
	 *
	 * @return  void
	 */
	protected function stop($msg = null)
	{
		if ($msg)
		{
			$this->out($msg);
		}

		$this->close();
	}
}

$app = new Build;

$app->execute();
