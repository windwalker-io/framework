<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

include_once __DIR__ . '/../../../vendor/autoload.php';

// show($_SESSION);

$session = new \Windwalker\Session\Session;

$session->start();

show($_SESSION);

$session->set('a', 'b');
$session->addFlash('a', 'b');

show($_SESSION);
