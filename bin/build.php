<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later. see LICENSE
 */

use Windwalker\Application\AbstractCliApplication;

include_once __DIR__ . '/../vendor/autoload.php';

define('WINDWALKER_ROOT', realpath(__DIR__ . '/..'));

/**
 * Class Build to build subtrees.
 *
 * @since 1.0
 */
class Build extends AbstractCliApplication
{
	/**
	 * Property organization.
	 *
	 * @var  string
	 */
	protected $organization = 'ventoviro';

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
		// 'authenticate' => 'Authenticate',
		'authentication' => 'Authentication',
		'cache'      => 'Cache',
		'compare'    => 'Compare',
		'console'    => 'Console',
		'controller' => 'Controller',
		'crypt'      => 'Crypt',
		'data'       => 'Data',
		'database'   => 'Database',
		'datamapper' => 'DataMapper',
		'di'         => 'DI',
		'dom'        => 'Dom',
		'environment' => 'Environment',
		'event'      => 'Event',
		'filesystem' => 'Filesystem',
		'filter'     => 'Filter',
		'form'       => 'Form',
		'html'       => 'Html',
		'http'       => 'Http',
		'io'         => 'IO',
		'language'   => 'Language',
		'loader'     => 'Loader',
		'middleware' => 'Middleware',
		'model'      => 'Model',
		'profiler'   => 'Profiler',
		'query'      => 'Query',
		'record'     => 'Record',
		'registry'   => 'Registry',
		'renderer'   => 'Renderer',
		'router'     => 'Router',
		'session'    => 'Session',
		'string'     => 'String',
		'test'       => 'Test',
		'uri'        => 'Uri',
		'utilities'  => 'Utilities',
		'validator'  => 'Validator',
		'view'       => 'View',
	);

	/**
	 * Method to run this application.
	 *
	 * @return  boolean
	 */
	protected function doExecute()
	{
		if ($this->io->getOption('h'))
		{
			return $this->help();
		}

		$this->tag = $tag = $this->io->getOption('t') ? : $this->io->getOption('tag');

		$branch = $this->io->getOption('b') ?: $this->io->getOption('branch', 'test');

		if ($this->tag && !$this->io->getOption('no-replace'))
		{
			$this->replaceDocblockTags();
		}

		$this->branch = $branch;

		$this->exec('git fetch origin');

		$this->exec('git branch -D ' . $branch);

		$this->exec('git checkout -b ' . $branch);

		$this->exec('git merge master');

		if ($this->tag)
		{
			$this->exec('git tag -d ' . $tag);

			$this->exec('git push origin :refs/tags/' . $tag);

			$this->exec('git tag ' . $tag);

			$this->exec(sprintf('git push %s %s', $branch, $this->tag));
		}

		$this->exec(sprintf('git push origin %s %s:%s master:master', $tag, $branch, $branch));

		$allows = $this->io->getArguments();

		foreach ($this->subtrees as $subtree => $namespace)
		{
			if ($allows && !in_array($subtree, $allows))
			{
				continue;
			}

			$this->splitTree($subtree, $namespace);
		}

		$this->exec('git checkout master');

		$this->out()->out('Split finish.');

		return true;
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
		$this->exec(sprintf('git remote add %s git@github.com:%s/windwalker-%s.git', $subtree, $this->organization, $subtree));

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
			$this->exec('git checkout sub-' . $subtree);

			$this->exec(sprintf('git tag -d %s', $this->tag));

			$this->exec(sprintf('git push %s :refs/tags/%s', $subtree, $this->tag));

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
	 * help
	 *
	 * @return  boolean
	 */
	protected function help()
	{
		$help = <<<HELP
Windwalker Build Command.

Will run subtree split and push every packages to it's repos.

Usage: php build.php [packages] [-t] [-b=test] [-f] [--dry-run] [--no-replace]

-t | --tags     Git tag of this build, will push to main repo and every subtree.
-b | --branch   Get branch to push, will  push to main repo and every subtree.
-f | --force    Override commits or not.
--dry-run       Do not real push, just run the subtree split process.
--no-replace    Do not replace the docblock variables.

HELP;

		$this->out($help);

		return true;
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

	/**
	 * replaceDocblockTags
	 *
	 * @return  void
	 */
	protected function replaceDocblockTags()
	{
		$this->out('Replacing Docblock');

		$files = new RecursiveIteratorIterator(new \RecursiveDirectoryIterator(WINDWALKER_ROOT . '/src', \FilesystemIterator::SKIP_DOTS));

		/** @var \SplFileInfo $file */
		foreach ($files as $file)
		{
			if ($file->isDir() || $file->getExtension() != 'php')
			{
				continue;
			}

			$content = file_get_contents($file->getPathname());

			$content = str_replace(
				array('{DEPLOY_VERSION}', '{ORGANIZATION}'),
				array($this->tag, 'LYRASOFT'),
				$content
			);

			file_put_contents($file->getPathname(), $content);
		}

		$this->exec('git checkout master');
		$this->exec(sprintf('git commit -am "Prepare for %s release."', $this->tag));
		$this->exec('git push origin master');
	}
}

$app = new Build;

$app->execute();
