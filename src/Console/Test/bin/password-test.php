<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
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
