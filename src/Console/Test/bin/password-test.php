<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

/*
 * When Password Test are not implement, we test it manually.
 */
$autoload = __DIR__ . '/../../../../vendor/autoload.php';

if (!is_file($autoload))
{
	$autoload = __DIR__ . '/../../vendor/autoload.php';
}

include_once $autoload;

$prompter = new \Windwalker\Console\Prompter\PasswordPrompter('Password: ');

echo $prompter->ask();
