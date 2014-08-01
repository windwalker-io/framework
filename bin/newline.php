<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

use Windwalker\Application\AbstractCliApplication;

include_once __DIR__ . '/../../../autoload.php';

define('WINDWALKER_ROOT', realpath(__DIR__ . '/..'));

/**
 * Class Build to build subtrees.
 *
 * @since 1.0
 */
class Newline extends AbstractCliApplication
{
	/**
	 * Method to run the application routines.  Most likely you will want to instantiate a controller
	 * and execute it, or perform some sort of task directly.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function doExecute()
	{
		$root = $this->io->getArgument(0, WINDWALKER_ROOT . '/src');

		$dirs = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

		$count = 0;

		foreach ($dirs as $file)
		{
			/** @var $file \SplFileInfo */
			if (!$file->isFile() || $file->getExtension() != 'php')
			{
				continue;
			}

			$content = file_get_contents($file);

			$length = strlen($content);

			$lastChar = $content[$length - 1];

			if ($lastChar == "\n")
			{
				continue;
			}

			$count++;

			$this->out('Add new line to: ' . $file);

			switch ($lastChar)
			{
				case ' ' :
				case "\r" :
					$content[$length - 1] = "\n";
					break;

				case '}' :
					$content .= "\n";
					break;
			}

			file_put_contents($file, $content);
		}

		if ($count)
		{
			$this->out($count . ' files add new line.');
		}
		else
		{
			$this->out('No file found.');
		}
	}
}

$app = new Newline;

$app->execute();
