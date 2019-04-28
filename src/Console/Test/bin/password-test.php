<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

/*
 * When Password Test are not implement, we test it manually.
 */
$autoload = __DIR__ . '/../../../../vendor/autoload.php';

if (!is_file($autoload)) {
    $autoload = __DIR__ . '/../../vendor/autoload.php';
}

include_once $autoload;

$prompter = new \Windwalker\Console\Prompter\PasswordPrompter('Password: ');

echo $prompter->ask();
