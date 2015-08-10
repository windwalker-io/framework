<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2015 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

include_once __DIR__ . '/../../../vendor/autoload.php';

$session = new \Windwalker\Session\Session;

$session->start();

$session2 = new \Windwalker\Session\Session;

$session2->start();
