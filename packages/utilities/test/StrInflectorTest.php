<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test;

use PHPUnit\Framework\TestCase;
use Symfony\Component\String\Inflector\EnglishInflector;
use Windwalker\Utilities\StrInflector;

/**
 * Test for the StrInflector class.
 *
 * @link   http://en.wikipedia.org/wiki/English_plural
 * @since  2.0
 */
class StrInflectorTest extends TestCase
{
    public function testInflectorInstance()
    {
        StrInflector::toSingular('words');

        self::assertInstanceOf(
            EnglishInflector::class,
            StrInflector::$inflector
        );
    }
}
