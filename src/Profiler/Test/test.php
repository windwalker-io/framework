<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

include_once __DIR__ . '/../../../vendor/autoload.php';

$benchmark = new \Windwalker\Profiler\Benchmark;

class Test
{
	public function f1()
	{
		md5(uniqid());
	}

	public static function f2()
	{
		md5(uniqid());
	}
}

$f1 = function()
{
	$t = new Test;

	$t->f1();
};

$f2 = function()
{
	Test::f2();
};

echo $benchmark->setTimeFold(\Windwalker\Profiler\Benchmark::SECOND)
	->addTask('test1', $f1)
	->addTask('test2', $f2)
	->run(1000)
	->renderResults(6, 'asc');
