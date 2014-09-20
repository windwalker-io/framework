<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

use Windwalker\Application\AbstractCliApplication;

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
	protected $branch = null;

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
		'application' => 'Application',
		// 'cache'      => 'Cache',
		'compare'    => 'Compare',
		'compat'     => 'Compat',
		'console'    => 'Console',
		'controller' => 'Controller',
		// 'crypt'      => 'Crypt',
		'data'       => 'Data',
		'database'   => 'Database',
		'datamapper' => 'DataMapper',
		// 'date'       => 'Date',
		'di'         => 'DI',
		'dom'        => 'Dom',
		// 'event'      => 'Event',
		'filesystem' => 'Filesystem',
		'filter'     => 'Filter',
		'form'       => 'Form',
		'html'       => 'Html',
		'io'         => 'IO',
		// 'language'   => 'Language',
		// 'loader'     => 'Loader',
		'middleware' => 'Middleware',
		// 'model'      => 'Model',
		// 'profiler'   => 'Profiler',
		'query'      => 'Query',
		// 'record'     => 'Record',
		'registry'   => 'Registry',
		// 'renderer'   => 'Renderer',
		// 'router'     => 'Router',
		// 'session'    => 'Session',
		'string'     => 'String',
		'test'       => 'Test',
		'uri'        => 'Uri',
		'utilities'  => 'Utilities',
		'validator'  => 'Validator',
		// 'view'       => 'View',
	);

	/**
	 * Method to run this application.
	 *
	 * @return  void
	 */
	protected function doExecute()
	{
		$this->tag = $tag = $this->io->getOption('t') ? : $this->io->getOption('tag');

		$branch = $this->io->getOption('b') ?: $this->io->getOption('branch', 'test');

		$this->branch = $branch;

		$this->exec('git fetch origin');

		$this->exec('git branch -D ' . $branch);

		$this->exec('git checkout -b ' . $branch);

		$this->exec('git merge staging');

		if ($this->tag)
		{
			$this->exec('git tag -d ' . $tag);

			$this->exec('git push origin :refs/tags/' . $tag);

			$this->exec('git tag ' . $tag);
		}

		$this->exec(sprintf('git push origin %s %s:%s staging:staging', $tag, $branch, $branch));

		$allows = $this->io->getArguments();

		foreach ($this->subtrees as $subtree => $namespace)
		{
			if ($allows && !in_array($subtree, $allows))
			{
				continue;
			}

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
		$this->out()->out(sprintf('@ Start subtree split (%s)', $subtree))
			->out('---------------------------------------');

		// Do split
		$this->exec('git subtree split -P src/' . $namespace . ' -b sub-' . $subtree);

		// Create a new branch
		$this->exec(sprintf('git branch -D %s-%s', $this->branch, $subtree));

		// Add remote repo
		$this->exec(sprintf('git remote add %s git@github.com:ventoviro/windwalker-%s.git', $subtree, $subtree));

		$force = $this->io->getOption('f') ? : $this->io->getOption('force', false);

		$force = $force ? ' -f' : false;

		if (!$force)
		{
			$this->exec(sprintf('git fetch %s', $subtree));

			$this->exec(sprintf('git checkout -b %s-%s --track %s/%s', $this->branch, $subtree, $subtree, $this->branch));

			$this->exec(sprintf('git merge sub-%s', $subtree));
		}

		$this->exec(sprintf('git push %s sub-%s:%s ' . $force, $subtree, $subtree, $this->branch));

		if ($this->tag)
		{
			$this->exec(sprintf('git tag -d %s', $this->tag));

			$this->exec(sprintf('git tag %s', $this->tag));

			$this->exec(sprintf('git push %s %s', $subtree, $this->tag));
		}

		$this->exec('git checkout ' . $this->branch);
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

		if ($this->io->getOption('dry-run'))
		{
			return '';
		}

		$return = exec(trim($command), $this->lastOutput, $this->lastReturn);

		$this->out($return);
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
