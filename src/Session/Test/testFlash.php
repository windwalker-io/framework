<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

include_once __DIR__ . '/../../../vendor/autoload.php';

// show($_SESSION);

$session = new \Windwalker\Session\Session;

$session->start();

show($_SESSION);

$session->set('a', 'b');
$session->addFlash('a', 'b');

show($_SESSION);
