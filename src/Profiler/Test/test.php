<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

// phpcs:disable

include_once __DIR__ . '/../../../vendor/autoload.php';

$benchmark = new \Windwalker\Profiler\Benchmark();

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
    $t = new Test();

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
