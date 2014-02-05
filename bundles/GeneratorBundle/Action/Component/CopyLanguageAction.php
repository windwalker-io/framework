<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace GeneratorBundle\Action\Component;

use GeneratorBundle\Action\AbstractAction;
use CodeGenerator\Controller\TaskController;
use Windwalker\DI\Container;
use Windwalker\String\String;

/**
 * Class ImportSqlAction
 *
 * @since 1.0
 */
class CopyLanguageAction extends AbstractAction
{
	/**
	 * doExecute
	 *
	 * @return  mixed
	 */
	public function doExecute()
	{
		try
		{
			$lanDir = new \DirectoryIterator($this->config['dir.src'] . '/language');
		}
		catch (\UnexpectedValueException $e)
		{
			return;
		}

		// Each languages
		foreach ($lanDir as $dir)
		{
			if ($lanDir->isDot() || $lanDir->isFile())
			{
				continue;
			}

			$this->handleINIFile($dir->getBasename());
		}
	}

	/**
	 * handleINIFile
	 *
	 * @param string $dir
	 *
	 * @return  void
	 */
	protected function handleINIFile($dir)
	{
		$src  = $this->config['dir.src'];
		$dest = $this->config['dir.dest'];
		$fileName = $dir . '.' . $this->config['element'] . '%s.ini';

		$mainINI = $this->findIniBySuffix($dir, 'main');
		$sysINI  = $this->findIniBySuffix($dir, 'sys');

		// Main file
		$targetFile = $dest . '/language/' . $dir . '/' . sprintf($fileName, '');

		if (strpos(file_get_contents($targetFile), '; ' . $this->config['replace.controller.item.name.cap']) === false)
		{
			$mainINI = $this->getSubsystemText($mainINI);

			$fp = fopen($targetFile, 'a+');
			fputs($fp, "\n\n\n" . $mainINI);
			fclose($fp);

			$this->controller->out('Write subsystem ini to: ' . $targetFile);
		}

		// Sys file
		$targetFile = $dest . '/language/' . $dir . '/' . sprintf($fileName, '.sys');

		if (strpos(file_get_contents($targetFile), '; ' . $this->config['replace.controller.item.name.cap']) === false)
		{
			$sysINI  = $this->getSubsystemText($sysINI);

			$fp = fopen($targetFile, 'a+');
			fputs($fp, "\n\n\n" . $sysINI);
			fclose($fp);

			$this->controller->out('Write subsystem ini to: ' . $targetFile);
		}
	}

	/**
	 * findIniBySuffix
	 *
	 * @param string $dir
	 * @param string $suffix
	 *
	 * @return  mixed
	 */
	protected function findIniBySuffix($dir, $suffix = 'main')
	{
		try
		{
			$files = new \FilesystemIterator($this->config['dir.src'] . '/language/' . $dir);
		}
		catch (\UnexpectedValueException $e)
		{
			exit('No such file: ' . $this->config['dir.src'] . '/language' . $dir);
		}


		foreach ($files as $file)
		{
			$name = $file->getBasename();

			$extract = explode('.', $name);

			if ($suffix == 'main' && count($extract) == 5)
			{
				return $file;
			}
			elseif (isset($extract[4]) && $extract[4] == $suffix)
			{
				return $file;
			}
		}

		return null;
	}

	/**
	 * getSubsystemText
	 *
	 * @param \SplFileinfo $file
	 *
	 * @return  string
	 */
	protected function getSubsystemText(\SplFileinfo $file)
	{
		$text = file_get_contents($file);

		$text = substr($text, strpos($text, '; {{controller.item.name.cap}}') - strlen($text));

		return String::parseVariable($text, $this->config['replace']);
	}
}
