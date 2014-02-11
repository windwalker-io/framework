<?php
/**
 * Part of windwalker-middleware project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

include dirname(__DIR__) . '/vendor/autoload.php';

class TestA extends \Windwalker\Middleware\Middleware
{
	/**
	 * call
	 *
	 * @return  mixed
	 */
	public function call()
	{
		echo "AAAA\n";

		$this->next->call();

		echo "AAAA\n";
	}
}

class TestB extends \Windwalker\Middleware\Middleware
{
	/**
	 * call
	 *
	 * @return  mixed
	 */
	public function call()
	{
		echo "BBBB\n";

		$this->next->call();

		echo "BBBB\n";
	}
}

//$a = new TestA;
//$b = new TestB;
//
//$a->setNext($b);
//$b->setNext(new \Windwalker\Middleware\CallbackMiddleware(
//	function($next)
//	{
//		echo "CCCC\n";
//		echo "CCCC\n";
//	}
//));
//
//$a->call();

$c = new \Windwalker\Middleware\Chain\MiddlewareChain;

$c->add('TestA')
	->add(new TestB);

$c->call();
