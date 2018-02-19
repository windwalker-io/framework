<?php
/**
 * Part of ww4 project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT.
 * @license    Please see LICENSE file.
 */

namespace Windwalker\Utilities\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\Environment\PhpHelper;
use Windwalker\Test\Traits\BaseAssertionTrait;
use Windwalker\Utilities\Arr;

/**
 * The ArrTest class.
 *
 * @since  3.2
 */
class ArrTest extends TestCase
{
    use BaseAssertionTrait;

    /**
     * testToArray
     *
     * @param $input
     * @param $recursive
     * @param $expect
     *
     * @return  void
     *
     * @dataProvider  providerTestToArray
     */
    public function testToArray($input, $recursive, $expect)
    {
        $this->assertEquals($expect, Arr::toArray($input, $recursive));
    }

    /**
     * Data provider for object inputs
     *
     * @return  array
     *
     * @since   2.0
     */
    public function providerTestToArray()
    {
        return [
            'string' => [
                'foo',
                false,
                ['foo'],
            ],
            'array' => [
                ['foo'],
                false,
                ['foo'],
            ],
            'array_recursive' => [
                [
                    'foo' => [
                        (object)['bar' => 'bar'],
                        (object)['baz' => 'baz'],
                    ],
                ],
                true,
                [
                    'foo' => [
                        ['bar' => 'bar'],
                        ['baz' => 'baz'],
                    ],
                ],
            ],
            'iterator' => [
                ['foo' => new \ArrayIterator(['bar' => 'baz'])],
                true,
                ['foo' => ['bar' => 'baz']],
            ],
        ];
    }

    /**
     * testToObject
     *
     * @return  void
     *
     * @dataProvider providerTestToObject
     */
    public function testToObject($input, $expect, $message)
    {
        self::assertEquals($expect, Arr::toObject($input), $message);
    }

    /**
     * providerTestToObject
     *
     * @return  array
     */
    public function providerTestToObject()
    {
        return [
            'single object' => [
                [
                    'integer' => 12,
                    'float' => 1.29999,
                    'string' => 'A Test String',
                ],
                (object)[
                    'integer' => 12,
                    'float' => 1.29999,
                    'string' => 'A Test String',
                ],
                'Should turn array into single object',
            ],
            'multiple objects' => [
                [
                    'first' => [
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                    'second' => [
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                    'third' => [
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                ],
                (object)[
                    'first' => (object)[
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                    'second' => (object)[
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                    'third' => (object)[
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                ],
                'Should turn multiple dimension array into nested objects',
            ],
            'single object with class' => [
                [
                    'integer' => 12,
                    'float' => 1.29999,
                    'string' => 'A Test String',
                ],
                (object)[
                    'integer' => 12,
                    'float' => 1.29999,
                    'string' => 'A Test String',
                ],
                'Should turn array into single object',
            ],
            'multiple objects with class' => [
                [
                    'first' => [
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                    'second' => [
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                    'third' => [
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                ],
                (object)[
                    'first' => (object)[
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                    'second' => (object)[
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                    'third' => (object)[
                        'integer' => 12,
                        'float' => 1.29999,
                        'string' => 'A Test String',
                    ],
                ],
                'Should turn multiple dimension array into nested objects',
            ],
        ];
    }

    /**
     * testDef
     *
     * @param array|object $array
     * @param string       $key
     * @param string       $value
     * @param mixed        $expected
     *
     * @return  void
     *
     * @dataProvider providerTestDef
     */
    public function testDef($array, $key, $value, $expected)
    {
        self::assertEquals($expected, $return = Arr::def($array, $key, $value));

        if (is_object($array)) {
            self::assertSame($array, $return);
            self::assertEquals($array, $expected);
        }
    }

    /**
     * providerTestDef
     *
     * @return  array
     */
    public function providerTestDef()
    {
        return [
            [
                ['foo' => 'bar'],
                'foo',
                'yoo',
                ['foo' => 'bar'],
            ],
            [
                ['foo' => 'bar'],
                'baz',
                'goo',
                ['foo' => 'bar', 'baz' => 'goo'],
            ],
            [
                (object)['foo' => 'bar'],
                'foo',
                'yoo',
                (object)['foo' => 'bar'],
            ],
            [
                (object)['foo' => 'bar'],
                'baz',
                'goo',
                (object)['foo' => 'bar', 'baz' => 'goo'],
            ],
        ];
    }

    /**
     * testHas
     *
     * @return  void
     */
    public function testHas()
    {
        self::assertTrue(Arr::has(['foo' => 'bar'], 'foo'));
        self::assertFalse(Arr::has(['foo' => 'bar'], 'yoo'));
        self::assertTrue(Arr::has(['foo' => ['bar' => 'yoo']], 'foo.bar'));
        self::assertTrue(Arr::has(['foo' => new \ArrayObject(['bar' => 'yoo'])], 'foo.bar'));
        self::assertTrue(Arr::has(['foo' => ['bar' => 'yoo']], 'foo/bar', '/'));
        self::assertFalse(Arr::has(['foo' => ['bar' => 'yoo']], ''));
    }

    /**
     * testCollapse
     *
     * @return  void
     */
    public function testCollapse()
    {
        $array = [
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9],
        ];

        self::assertEquals([1, 2, 3, 4, 5, 6, 7, 8, 9], Arr::collapse($array));

        $array = [
            (object)[1, 2, 3],
            4,
            5,
            6,
            [7, 8, 9],
        ];

        if (version_compare(PHP_VERSION, '7.0', '<') && !PhpHelper::isHHVM()) {
            self::assertEquals([4, 5, 6, 7, 8, 9], Arr::collapse($array));
        } else {
            self::assertEquals([1, 2, 3, 4, 5, 6, 7, 8, 9], Arr::collapse($array));
        }
    }

    /**
     * testFlatten
     *
     * @return  void
     */
    public function testFlatten()
    {
        $array = [
            'flower' => 'sakura',
            'olive' => 'peace',
            'pos1' => [
                'sunflower' => 'love',
            ],
            'pos2' => [
                'cornflower' => 'elegant',
                'pos3' => [
                    'olive',
                ],
            ],
        ];

        $flatted = Arr::flatten($array);

        $this->assertEquals($flatted['pos1.sunflower'], 'love');

        $flatted = Arr::flatten($array, '/');

        $this->assertEquals($flatted['pos1/sunflower'], 'love');

        // Test depth
        $flatted = Arr::flatten($array, '/', 0);

        $this->assertEquals($flatted['pos2/pos3/0'], 'olive');

        $flatted = Arr::flatten($array, '/', 1);

        $this->assertEquals($flatted['pos2']['pos3'], ['olive']);

        $flatted = Arr::flatten($array, '/', 2);

        $this->assertEquals($flatted['pos2/pos3'], ['olive']);

        $flatted = Arr::flatten($array, '/', 3);

        $this->assertEquals($flatted['pos2/pos3/0'], 'olive');

        $array = new \ArrayObject(
            [
                'Apple' => [
                    ['name' => 'iPhone 6S', 'brand' => 'Apple'],
                ],
                'Samsung' => [
                    ['name' => 'Galaxy S7', 'brand' => 'Samsung'],
                ],
            ]
        );

        $expected = [
            'Apple.0' => ['name' => 'iPhone 6S', 'brand' => 'Apple'],
            'Samsung.0' => ['name' => 'Galaxy S7', 'brand' => 'Samsung'],
        ];

        $this->assertEquals($expected, Arr::flatten($array, '.', 2));
    }

    /**
     * testGet
     *
     * @return  void
     */
    public function testGet()
    {
        $data = [
            'flower' => 'sakura',
            'olive' => 'peace',
            'pos1' => [
                'sunflower' => 'love',
            ],
            'pos2' => [
                'cornflower' => 'elegant',
            ],
            'array' => [
                'A',
                'B',
                'C',
            ],
        ];

        $this->assertEquals('sakura', Arr::get($data, 'flower'));
        $this->assertEquals('love', Arr::get($data, 'pos1.sunflower'));
        $this->assertEquals('default', Arr::get($data, 'pos1.notexists', 'default'));
        $this->assertEquals('default', Arr::get($data, '', 'default'));
        $this->assertEquals('love', Arr::get($data, 'pos1/sunflower', null, '/'));
        $this->assertEquals($data['array'], Arr::get($data, 'array'));
        $this->assertNull(Arr::get($data, ['not', 'exists']));

        $data = (object)[
            'flower' => 'sakura',
            'olive' => 'peace',
            'pos1' => (object)[
                'sunflower' => 'love',
            ],
            'pos2' => new \ArrayObject(
                [
                    'cornflower' => 'elegant',
                ]
            ),
            'array' => (object)[
                'A',
                'B',
                'C',
            ],
        ];

        $this->assertEquals('sakura', Arr::get($data, 'flower'));
        $this->assertEquals('love', Arr::get($data, 'pos1.sunflower'));
        $this->assertEquals('default', Arr::get($data, 'pos1.notexists', 'default'));
        $this->assertEquals('elegant', Arr::get($data, 'pos2.cornflower'));
        $this->assertEquals('love', Arr::get($data, 'pos1/sunflower', null, '/'));
        $this->assertEquals($data->array, Arr::get($data, 'array'));
        $this->assertNull(Arr::get($data, 'not.exists'));
    }

    /**
     * testSet
     *
     * @return  void
     */
    public function testSet()
    {
        $data = [];

        // One level
        $return = Arr::set($data, 'flower', 'sakura');

        $this->assertEquals('sakura', $return['flower']);

        // Multi-level
        $return = Arr::set($data, 'foo.bar', 'test');

        $this->assertEquals('test', $return['foo']['bar']);

        // Separator
        $return = Arr::set($data, 'foo/bar', 'play', '/');

        $this->assertEquals('play', $return['foo']['bar']);

        // Type
        $return = Arr::set($data, 'cloud/fly', 'bird', '/', 'stdClass');

        $this->assertEquals('bird', $return['cloud']->fly);

        // False
        Arr::set($data, '', 'goo');

        // Fix path
        $return = Arr::set($data, 'double..separators', 'value');

        $this->assertEquals('value', $return['double']['separators']);

        static::assertExpectedException(
            function () use ($data) {
                Arr::set($data, 'a.b', 'c', '.', 'Non\Exists\Class');
            },
            \InvalidArgumentException::class,
            'Type or class: Non\Exists\Class not exists'
        );
    }

    /**
     * testRemove
     *
     * @param array|object $array
     * @param array|object $expected
     * @param int|string   $offset
     * @param string       $separator
     *
     * @dataProvider providerTestRemove
     */
    public function testRemove($array, $expected, $offset, $separator)
    {
        $actual = Arr::remove($array, $offset, $separator);

        self::assertEquals($expected, $actual);

        if (is_object($array)) {
            self::assertSame($array, $actual);
            self::assertTrue(is_object($actual));
        }
    }

    /**
     * providerTestRemove
     *
     * @return  array
     */
    public function providerTestRemove()
    {
        return [
            [
                [1, 2, 3],
                [0 => 1, 2 => 3],
                1,
                '.',
            ],
            [
                [1, 2, 3],
                [1, 2, 3],
                5,
                '.',
            ],
            [
                [1, 2, 3],
                [1, 2, 3],
                '',
                '.',
            ],
            [
                ['foo' => 'bar', 'baz' => 'yoo'],
                ['baz' => 'yoo'],
                'foo',
                '.',
            ],
            [
                ['foo' => 'bar', 'baz' => 'yoo'],
                ['foo' => 'bar', 'baz' => 'yoo'],
                'haa',
                '.',
            ],
            [
                ['foo' => 'bar', 'baz' => ['joo' => 'hoo']],
                ['foo' => 'bar', 'baz' => []],
                'baz.joo',
                '.',
            ],
            [
                (object)['foo' => 'bar', 'baz' => 'yoo'],
                (object)['baz' => 'yoo'],
                'foo',
                '.',
            ],
            [
                (object)['foo' => 'bar', 'baz' => 'yoo'],
                (object)['foo' => 'bar', 'baz' => 'yoo'],
                'haa',
                '.',
            ],
            [
                (object)['foo' => 'bar', 'baz' => ['joo' => 'hoo']],
                (object)['foo' => 'bar', 'baz' => []],
                'baz/joo',
                '/',
            ],
        ];
    }

    /**
     * testKeep
     *
     * @return  void
     */
    public function testOnly()
    {
        $array = [
            'Lycoris' => 'energetic',
            'Sunflower' => 'worship',
            'Zinnia' => 'robust',
            'Lily' => 'love',
        ];

        self::assertEquals(
            ['Lycoris' => 'energetic', 'Zinnia' => 'robust'],
            Arr::only($array, ['Lycoris', 'Zinnia'])
        );

        self::assertEquals(
            ['Lycoris' => 'energetic'],
            Arr::only($array, ['Lycoris'])
        );

        self::assertEquals(
            (object)['Lycoris' => 'energetic', 'Zinnia' => 'robust'],
            Arr::only((object)$array, ['Lycoris', 'Zinnia'])
        );

        self::assertEquals(
            (object)['Lycoris' => 'energetic'],
            Arr::only((object)$array, ['Lycoris'])
        );

        $this->expectException(\InvalidArgumentException::class);

        Arr::only('string', ['test']);
    }

    /**
     * testFind
     *
     * @return  void
     */
    public function testFind()
    {
        $data = [
            [
                'id' => 1,
                'title' => 'Julius Caesar',
                'data' => (object)['foo' => 'bar'],
            ],
            [
                'id' => 2,
                'title' => 'Macbeth',
                'data' => [],
            ],
            [
                'id' => 3,
                'title' => 'Othello',
                'data' => 123,
            ],
            [
                'id' => 4,
                'title' => 'Hamlet',
                'data' => true,
            ],
        ];

        $results = Arr::find(
            $data, function ($value, $key) {
            return $value['title'] === 'Julius Caesar' || $value['id'] == 4;
        }
        );

        $this->assertEquals([$data[0], $data[3]], $results);

        // Keep key
        $results = Arr::find(
            $data, function ($value, &$key) {
            $key++;

            return $value['title'] === 'Julius Caesar' || $value['id'] == 4;
        }, true
        );

        $this->assertEquals([1 => $data[0], 4 => $data[3]], $results);

        // Offset limit
        $results = Arr::find(
            $data, function ($value, &$key) {
            $key++;

            return $value['title'] === 'Julius Caesar' || $value['id'] == 4;
        }, false, 0, 1
        );

        $this->assertEquals([$data[0]], $results);

        // Offset limit
        $results = Arr::find(
            $data, function ($value, &$key) {
            $key++;

            return $value['title'] === 'Julius Caesar' || $value['id'] == 4;
        }, false, 1, 1
        );

        $this->assertEquals([$data[3]], $results);

        // Test global function
        self::assertEquals(['foo' => 'bar'], Arr::find(['foo' => 'bar', 'baz' => ''], 'strlen', true));
    }

    /**
     * testFindFirst
     *
     * @return  void
     */
    public function testFindFirst()
    {
        $data = [
            [
                'id' => 1,
                'title' => 'Julius Caesar',
                'data' => (object)['foo' => 'bar'],
            ],
            [
                'id' => 2,
                'title' => 'Macbeth',
                'data' => [],
            ],
            [
                'id' => 3,
                'title' => 'Othello',
                'data' => 123,
            ],
            [
                'id' => 4,
                'title' => 'Hamlet',
                'data' => true,
            ],
        ];

        $result = Arr::findFirst(
            $data, function ($value, $key) {
            return $value['title'] === 'Julius Caesar' || $value['id'] == 4;
        }
        );

        $this->assertEquals($data[0], $result);

        $result = Arr::findFirst(
            $data, function ($value, $key) {
            return $value['title'] === 'No exists';
        }
        );

        $this->assertNull($result);
    }

    /**
     * testReject
     *
     * @return  void
     */
    public function testReject()
    {
        $data = [
            [
                'id' => 1,
                'title' => 'Julius Caesar',
                'data' => (object)['foo' => 'bar'],
            ],
            [
                'id' => 2,
                'title' => 'Macbeth',
                'data' => [],
            ],
            [
                'id' => 3,
                'title' => 'Othello',
                'data' => 123,
            ],
            [
                'id' => 4,
                'title' => 'Hamlet',
                'data' => true,
            ],
        ];

        $results = Arr::reject(
            $data, function ($value, $key) {
            return $value['title'] === 'Julius Caesar' || $value['id'] == 4;
        }
        );

        $this->assertEquals([$data[1], $data[2]], $results);
    }

    /**
     * testTakeout
     *
     * @return  void
     */
    public function testTakeout()
    {
        $array = [
            'one' => 1,
            'two' => 2,
            'three' => 3,
        ];

        self::assertEquals(2, Arr::takeout($array, 'two'));
        self::assertEquals(['one' => 1, 'three' => 3], $array);

        $array = [
            'one' => 1,
            'two' => ['two' => 2, 'three' => 3],
        ];

        self::assertEquals(2, Arr::takeout($array, 'two.two'));
        self::assertEquals(['one' => 1, 'two' => ['three' => 3]], $array);

        $array = [
            'one' => 1,
            'two' => 2,
            'three' => 3,
        ];

        self::assertEquals('default', Arr::takeout($array, 'foo', 'default'));
        self::assertEquals(['one' => 1, 'two' => 2, 'three' => 3], $array);

        $array = (object)[
            'one' => 1,
            'two' => 2,
            'three' => 3,
        ];

        self::assertEquals(2, Arr::takeout($array, 'two'));
    }

    /**
     * testSort
     *
     * @param $data
     * @param $expected
     * @param $condition
     * @param $descending
     *
     * @return  void
     *
     * @dataProvider providerTestSort
     */
    public function testSort($data, $expected, $condition, $descending)
    {
        $return = Arr::sort($data, $condition, $descending);

        self::assertEquals($expected, $return);
    }

    /**
     * providerTestSort
     *
     * @return  array
     */
    public function providerTestSort()
    {
        return [
            'simple array' => [
                [
                    ['name' => 'Car', 'price' => 200],
                    ['name' => 'Bike', 'price' => 100],
                    ['name' => 'Motor', 'price' => 150],
                ],
                [
                    1 => ['name' => 'Bike', 'price' => 100],
                    2 => ['name' => 'Motor', 'price' => 150],
                    0 => ['name' => 'Car', 'price' => 200],
                ],
                'price',
                false,
            ],
            'simple array use callback' => [
                [
                    2 => ['name' => 'Car', 'price' => 200],
                    3 => ['name' => 'Bike', 'price' => 100],
                    4 => ['name' => 'Motor', 'price' => 150],
                ],
                [
                    2 => ['name' => 'Car', 'price' => 200],
                    4 => ['name' => 'Motor', 'price' => 150],
                    3 => ['name' => 'Bike', 'price' => 100],
                ],
                function ($item, $key) {
                    return $item['price'];
                },
                true,
            ],
            'simple objects' => [
                [
                    (object)['name' => 'Car', 'price' => 200],
                    (object)['name' => 'Bike', 'price' => 100],
                    (object)['name' => 'Motor', 'price' => 150],
                ],
                [
                    1 => (object)['name' => 'Bike', 'price' => 100],
                    2 => (object)['name' => 'Motor', 'price' => 150],
                    0 => (object)['name' => 'Car', 'price' => 200],
                ],
                'price',
                false,
            ],
            'simple objects use callback' => [
                [
                    (object)['name' => 'Bike', 'price' => 100],
                    (object)['name' => 'Car', 'price' => 200],
                    (object)['name' => 'Motor', 'price' => 150],
                ],
                [
                    1 => (object)['name' => 'Car', 'price' => 200],
                    0 => (object)['name' => 'Bike', 'price' => 100],
                    2 => (object)['name' => 'Motor', 'price' => 150],
                ],
                function ($item, $key) {
                    return strlen($item->name);
                },
                false,
            ],
        ];
    }

    /**
     * testInvert
     *
     * @param $data
     * @param $expected
     *
     * @return  void
     *
     * @dataProvider providerTestInvert
     */
    public function testInvert($data, $expected)
    {
        self::assertEquals($expected, Arr::invert($data));
    }

    /**
     * providerTestInvert
     *
     * @return  array
     */
    public function providerTestInvert()
    {
        return [
            'Case 1' => [
                [
                    'Sakura' => ['1000', '1500', '1750'],
                    'Olive' => ['3000', '4000', '5000', '6000'],
                ],
                [
                    '1000' => 'Sakura',
                    '1500' => 'Sakura',
                    '1750' => 'Sakura',
                    '3000' => 'Olive',
                    '4000' => 'Olive',
                    '5000' => 'Olive',
                    '6000' => 'Olive',
                ],
            ],
            'Case 2' => [
                [
                    'Sakura' => [1000, 1500, 1750],
                    'Olive' => [2750, 3000, 4000, 5000, 6000],
                    'Sunflower' => [2000, 2500],
                    'Unspecified' => [],
                ],
                [
                    '1000' => 'Sakura',
                    '1500' => 'Sakura',
                    '1750' => 'Sakura',
                    '2750' => 'Olive',
                    '3000' => 'Olive',
                    '4000' => 'Olive',
                    '5000' => 'Olive',
                    '6000' => 'Olive',
                    '2000' => 'Sunflower',
                    '2500' => 'Sunflower',
                ],
            ],
            'Case 3' => [
                [
                    'Sakura' => [1000, 1500, 1750],
                    'valueNotAnArray' => 2750,
                    'withNonScalarValue' => [2000, [1000, 3000]],
                ],
                [
                    '1000' => 'Sakura',
                    '1500' => 'Sakura',
                    '1750' => 'Sakura',
                    '2000' => 'withNonScalarValue',
                ],
            ],
        ];
    }

    /**
     * Method to test pivot().
     *
     * @param array $data
     * @param array $expected
     *
     * @return void
     *
     * @dataProvider providerTestPivot
     */
    public function testPivot($data, $expected)
    {
        $this->assertEquals($expected, Arr::pivot($data));
    }

    /**
     * seedTestTranspose
     *
     * @return array
     */
    public function providerTestPivot()
    {
        return [
            [
                // data
                [
                    'Jones' => [123, 223],
                    'Arthur' => ['Lancelot', 'Jessica'],
                ],
                // expected
                [
                    ['Jones' => 123, 'Arthur' => 'Lancelot'],
                    ['Jones' => 223, 'Arthur' => 'Jessica'],
                ],
            ],
        ];
    }

    /**
     * testIsAssociative
     *
     * @return  void
     */
    public function testIsAssociative()
    {
        self::assertTrue(Arr::isAssociative(['foo' => 'bar', 'baz']));
        self::assertFalse(Arr::isAssociative(['bar', 'baz']));
        self::assertTrue(Arr::isAssociative([2 => 'bar', 1 => 'baz']));
    }

    /**
     * testAccessible
     *
     * @return  void
     */
    public function testAccessible()
    {
        self::assertTrue(Arr::accessible([]));
        self::assertTrue(Arr::accessible(new \ArrayObject()));

//		$array = new class implements \ArrayAccess
//		{
//			public function offsetExists($offset)
//			{
//			}
//
//			public function offsetGet($offset)
//			{
//			}
//
//			public function offsetSet($offset, $value)
//			{
//			}
//
//			public function offsetUnset($offset)
//			{
//			}
//		};

//		self::assertTrue(Arr::accessible($array));
        self::assertFalse(Arr::accessible(new \EmptyIterator()));
        self::assertFalse(Arr::accessible(new \stdClass()));
    }

    /**
     * testGroup
     *
     * @param $source
     * @param $key
     * @param $expected
     * @param $forceArray
     *
     * @dataProvider  providerTestGroup
     */
    public function testGroup($source, $key, $expected, $forceArray)
    {
        self::assertEquals($expected, Arr::group($source, $key, $forceArray));
    }

    /**
     * providerTestGroup
     *
     * @return  array
     */
    public function providerTestGroup()
    {
        return [
            'A scalar array' => [
                // Source
                [
                    1 => 'a',
                    2 => 'b',
                    3 => 'b',
                    4 => 'c',
                    5 => 'a',
                    6 => 'a',
                ],
                // Key
                null,
                // Expected
                [
                    'a' => [1, 5, 6],
                    'b' => [2, 3],
                    'c' => 4,
                ],
                false,
            ],
            'A scalar array force child array' => [
                // Source
                [
                    1 => 'a',
                    2 => 'b',
                    3 => 'b',
                    4 => 'c',
                    5 => 'a',
                    6 => 'a',
                ],
                // Key
                null,
                // Expected
                [
                    'a' => [1, 5, 6],
                    'b' => [2, 3],
                    'c' => [4],
                ],
                true,
            ],
            'An array of associative arrays' => [
                // Source
                [
                    1 => ['id' => 41, 'title' => 'boo'],
                    2 => ['id' => 42, 'title' => 'boo'],
                    3 => ['title' => 'boo'],
                    4 => ['id' => 42, 'title' => 'boo'],
                    5 => ['id' => 43, 'title' => 'boo'],
                ],
                // Key
                'id',
                // Expected
                [
                    41 => ['id' => 41, 'title' => 'boo'],
                    42 => [
                        ['id' => 42, 'title' => 'boo'],
                        ['id' => 42, 'title' => 'boo'],
                    ],
                    43 => ['id' => 43, 'title' => 'boo'],
                ],
                false,
            ],
            'An array of associative arrays force child array' => [
                // Source
                [
                    1 => ['id' => 41, 'title' => 'boo'],
                    2 => ['id' => 42, 'title' => 'boo'],
                    3 => ['title' => 'boo'],
                    4 => ['id' => 42, 'title' => 'boo'],
                    5 => ['id' => 43, 'title' => 'boo'],
                ],
                // Key
                'id',
                // Expected
                [
                    41 => [['id' => 41, 'title' => 'boo']],
                    42 => [
                        ['id' => 42, 'title' => 'boo'],
                        ['id' => 42, 'title' => 'boo'],
                    ],
                    43 => [['id' => 43, 'title' => 'boo']],
                ],
                true,
            ],
            'An array of objects' => [
                // Source
                [
                    1 => (object)['id' => 41, 'title' => 'boo'],
                    2 => (object)['id' => 42, 'title' => 'boo'],
                    3 => (object)['title' => 'boo'],
                    4 => (object)['id' => 42, 'title' => 'boo'],
                    5 => (object)['id' => 43, 'title' => 'boo'],
                ],
                // Key
                'id',
                // Expected
                [
                    41 => (object)['id' => 41, 'title' => 'boo'],
                    42 => [
                        (object)['id' => 42, 'title' => 'boo'],
                        (object)['id' => 42, 'title' => 'boo'],
                    ],
                    43 => (object)['id' => 43, 'title' => 'boo'],
                ],
                false,
            ],
        ];
    }

    /**
     * testUnique
     *
     * @return  void
     */
    public function testUnique()
    {
        $array = [
            [1, 2, 3, [4]],
            [2, 2, 3, [4]],
            [3, 2, 3, [4]],
            [2, 2, 3, [4]],
            [3, 2, 3, [4]],
        ];

        $expected = [
            [1, 2, 3, [4]],
            [2, 2, 3, [4]],
            [3, 2, 3, [4]],
        ];

        self::assertEquals($expected, Arr::unique($array));
    }

    /**
     * testMerge
     *
     * @return  void
     */
    public function testMergeRecursive()
    {
        $data1 = [
            'green' => 'Hulk',
            'red' => 'empty',
            'human' => [
                'dark' => 'empty',
                'black' => [
                    'male' => 'empty',
                    'female' => 'empty',
                    'no-gender' => 'empty',
                ],
            ],
        ];

        $data2 = [
            'ai' => 'Jarvis',
            'agent' => 'Phil Coulson',
            'red' => [
                'left' => 'Pepper',
                'right' => 'Iron Man',
            ],
            'human' => [
                'dark' => 'Nick Fury',
                'black' => [
                    'female' => 'Black Widow',
                    'male' => 'Loki',
                ],
            ],
        ];

        $data3 = [
            'ai' => 'Ultron',
        ];

        $expected = [
            'ai' => 'Jarvis',
            'agent' => 'Phil Coulson',
            'green' => 'Hulk',
            'red' => [
                'left' => 'Pepper',
                'right' => 'Iron Man',
            ],
            'human' => [
                'dark' => 'Nick Fury',
                'black' => [
                    'male' => 'Loki',
                    'female' => 'Black Widow',
                    'no-gender' => 'empty',
                ],
            ],
        ];

        $this->assertEquals($expected, Arr::mergeRecursive($data1, $data2));

        $expected['ai'] = 'Ultron';

        $this->assertEquals($expected, Arr::mergeRecursive($data1, $data2, $data3));

        $this->expectException(\InvalidArgumentException::class);

        Arr::mergeRecursive('', 123);
    }

    /**
     * testDump
     *
     * @return  void
     */
    public function testDump()
    {
        if (PhpHelper::isHHVM()) {
            static::markTestSkipped('Skip since HHVM has different behavior of ArrayObject, but still works.');
        }

        $data = [
            1,
            2,
            'foo' => 'bar',
            (object)['baz' => 'yoo'],
            new \ArrayObject(['flower' => 'sakura', 'fruit' => 'apple']),
            ['max' => ['level' => ['test' => ['no' => 'show']]]],
        ];

        $expected = <<<OUT
Array
(
    [0] => 1
    [1] => 2
    [foo] => bar
    [2] => stdClass Object
        (
            [baz] => yoo
        )

    [3] => ArrayObject Object
        (
            [flower] => sakura
            [fruit] => apple
        )

    [4] => Array
        (
            [max] => Array
                (
                    [level] => Array
                        *MAX LEVEL*

                )

        )

)
OUT;

        self::assertStringSafeEquals($expected, Arr::dump($data, 4));
    }

    /**
     * testShow
     *
     * @return  void
     */
    public function testShow()
    {
        $data = [
            'test',
            1,
            2,
            ['foo' => 'bar'],
            ['max' => ['level' => ['test' => ['no' => 'show']]]],
            4,
        ];

        $expected = <<<OUT
[Value 1]
test

[Value 2]
1

[Value 3]
2

[Value 4]
Array
(
    [foo] => bar
)


[Value 5]
Array
(
    [max] => Array
        (
            [level] => Array
                (
                    [test] => Array
                        *MAX LEVEL*

                )

        )

)
OUT;

        self::assertStringSafeEquals($expected, call_user_func_array([Arr::class, 'show'], $data));
        self::assertStringSafeEquals('string', Arr::show('string'));

        Arr::$sapi = 'web';

        self::assertStringSafeEquals('<pre>string</pre>', Arr::show('string'));

        Arr::$sapi = PHP_SAPI;
    }

    /**
     * testMap
     *
     * @return  void
     */
    public function testMap()
    {
        $data = [
            'green' => 'Hulk',
            'red' => [
                'left' => 'Pepper',
            ],
            'human' => [
                'dark' => 'Nick Fury',
                'black' => [
                    'male' => 'Loki',
                ],
            ],
        ];

        $expected = [
            'green' => 'Hulk #',
            'red' => [
                'left' => 'Pepper #',
            ],
            'human' => [
                'dark' => 'Nick Fury #',
                'black' => [
                    'male' => 'Loki #',
                ],
            ],
        ];

        $expected2 = [
            'green@' => 'Hulk #',
            'red' => [
                'left@' => 'Pepper #',
            ],
            'human' => [
                'dark@' => 'Nick Fury #',
                'black' => [
                    'male@' => 'Loki #',
                ],
            ],
        ];

        self::assertEquals(
            $expected, Arr::map(
            $data, function ($value, $key) {
            return $value . ' #';
        }, true
        )
        );

        self::assertEquals(
            $expected2, Arr::map(
            $data, function ($value, &$key) {
            $key .= '@';

            return $value . ' #';
        }, true
        )
        );
    }
}
