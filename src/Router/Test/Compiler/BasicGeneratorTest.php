<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Router\Test\Compiler;

use Windwalker\Router\Compiler\BasicGenerator;

/**
 * Test class of BasicGenerator
 *
 * @since 2.0
 */
class BasicGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * regexList
     *
     * @return  array
     */
    public function regexList()
    {
        return [
            [
                'flower/(id)',
                ['id' => 25],
                'flower/25',
                __LINE__,
            ],
            [
                'flower/(id)/(alias)',
                ['id' => 25, 'alias' => 'sakura'],
                'flower/25/sakura',
                __LINE__,
            ],
            [
                'flower/(id)/(alias)',
                ['alias' => 'sakura'],
                'flower/(id)/sakura',
                __LINE__,
            ],
            [
                'flower/(id)-(alias)',
                ['id' => 25, 'alias' => 'sakura'],
                'flower/25-sakura',
                __LINE__,
            ],
            [
                'flower(/id)',
                ['id' => 25, 'alias' => 'sakura'],
                'flower/25?alias=sakura',
                __LINE__,
            ],
            [
                'flower(/id)',
                ['alias' => 'sakura'],
                'flower?alias=sakura',
                __LINE__,
            ],
            [
                'flower(/id,alias)',
                ['id' => 25, 'alias' => 'sakura'],
                'flower/25/sakura',
                __LINE__,
            ],
            [
                'flower(/foo,bar,baz)',
                ['foo' => 2014, 'bar' => 9, 'baz' => 27],
                'flower/2014/9/27',
                __LINE__,
            ],
            [
                'flower/(*tags)',
                ['id' => 25, 'tags' => ['sakura', 'rose', 'olive']],
                'flower/sakura/rose/olive?id=25',
                __LINE__,
            ],
            [
                'flower/(*tags)/(alias)',
                ['id' => 25, 'alias' => 'wind', 'tags' => ['sakura', 'rose', 'olive']],
                'flower/sakura/rose/olive/wind?id=25',
                __LINE__,
            ],
        ];
    }

    /**
     * Method to test generate().
     *
     * @return void
     *
     * @covers        \Windwalker\Router\Compiler\BasicGenerator::generate
     *
     * @dataProvider  regexList
     */
    public function testGenerate($pattern, $data, $expected, $line)
    {
        $this->assertEquals($expected, BasicGenerator::generate($pattern, $data), 'Fail at: ' . $line);
    }
}
