<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

// phpcs:disable

use Windwalker\Middleware\Chain\ChainBuilder;
use Windwalker\Middleware\Middleware;

include dirname(__DIR__) . '/vendor/autoload.php';

class TestA extends Middleware
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
class TestB extends Middleware
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
//$b->setNext(new CallbackMiddleware(
//    function($next)
//    {
//        echo "CCCC\n";
//        echo "CCCC\n";
//    }
//));
//
//$a->call();

$c = new ChainBuilder();

$c->add('TestA')
    ->add(new TestB());

$c->execute();
