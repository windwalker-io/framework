<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

include_once __DIR__ . '/../../../../vendor/autoload.php';

with(new \Windwalker\Console\Prompter\SelectPrompter(null, array(1,2,3)))->ask('123:');
