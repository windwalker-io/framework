<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
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
        sha1(uniqid());
    }
}

$f1 = function () {
    $t = new Test;

    $t->f1();
};

$f2 = function () {
    Test::f2();
};

echo $benchmark
    ->setTimeFormat(\Windwalker\Profiler\Benchmark::MICRO_SECOND)
    ->addTask('test1', $f1)
    ->addTask('test2', $f2)
    ->execute(10000)
    ->render(6, 'asc');
