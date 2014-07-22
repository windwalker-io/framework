<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

include_once __DIR__ . '/../../../../../autoload.php';

/*
$r = new \Windwalker\Router\Route('aaa/bbb/:id', array('_controller' => 'Goo\\Foo\\Get'), ['get', 'post']);

$router = new \Windwalker\Router\Router;

$router
	->addRoute('root', '/', array('_controller' => 'Root\\Get'))
	->addRoute('aaa', 'aaa/bbb/:id', array('_controller' => 'Goo\\Foo\\Get'), ['get', 'post'])
	->setMethod('post');

$v = $router->match('/');
*/


$router = new \Windwalker\Router\RestRouter;

$router->addMap('aaa/bbb/:id', 'Foo\\Yoo\\');

$v = $router->setMethod('GET')->match('aaa/bbb/34');


/*
$r = new \Windwalker\Router\Route('aaa/:bbb/:id', ['_controller' => 'Goo\\Foo\\Get']);

$router = new \Windwalker\Router\Router;

$router->addRoute($r->setName('goo'));

$v = $router->buildRoute('goo', ['bbb' => 'haha']);
*/

show($v, 12);
