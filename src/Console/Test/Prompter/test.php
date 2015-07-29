<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

include_once __DIR__ . '/../../../../vendor/autoload.php';

with(new \Windwalker\Console\Prompter\SelectPrompter(null, array(1,2,3)))->ask('123:');
