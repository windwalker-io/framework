<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

$autoload = __DIR__ . '/../../../../vendor/autoload.php';

if (!is_file($autoload))
{
	$autoload = __DIR__ . '/../../vendor/autoload.php';
}

include_once $autoload;

$console = new \Windwalker\Console\Console;

$console->execute();

die;

$console->setName('Example Console')
	->setVersion('1.2.3')
	->setUsage('console.php <commands> <arguments> [-h|--help] [-q|--quiet]')
	->setDescription('Hello World')
	->setHelp(
<<<HELP
Hello, this is an example console, if you want to do something, see above:

$ foo bar -h => foo bar --help

---------

$ foo bar yoo -q => foo bar yoo --quiet
HELP
	);

$console->setHandler(
	function($command)
	{
		$prompter = new \Windwalker\Console\Prompter\BooleanPrompter;

		$result = $prompter->ask('Do you wan to do this [Y/n]: ');

		var_dump($result);
	}
);

$console->execute();
