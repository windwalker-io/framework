<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

include_once __DIR__ . '/../../../vendor/autoload.php';

// show($_SESSION);

$session = new \Windwalker\Session\Session();

$session->start();

show($_SESSION);

$session->set('a', 'b');
$session->addFlash('a', 'b');

show($_SESSION);
