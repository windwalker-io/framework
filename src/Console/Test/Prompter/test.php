<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

include_once __DIR__ . '/../../../../vendor/autoload.php';

with(new \Windwalker\Console\Prompter\SelectPrompter(null, [1, 2, 3]))->ask('123:');

// \Windwalker\Console\Prompter\Prompter::selector('TEXT: ', ['a' => 'aaa', 'b', 'c']);
