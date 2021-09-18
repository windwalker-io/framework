<?php

/**
 * Part of framework project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Scalars\Test\Concern;

use PHPUnit\Framework\TestCase;

use function Windwalker\str;

/**
 * The StringStructureTest class.
 */
class StringStructureTest extends TestCase
{
    public function testParseToCollection(): void
    {
        $str = str('{"foo": "bar"}');
        $json = $str->parseToCollection('json');

        self::assertEquals('bar', $json['foo']);
    }

    public function testJsonDecode(): void
    {
        $str = str('{"foo": "bar"}');
        $json = $str->jsonDecode();

        self::assertEquals('bar', $json['foo']);
    }

    public function testToDOMDocument(): void
    {
        $str = str('<root><foo bar="yoo" /></root>');
        $dom = $str->toDOMDocument();

        self::assertEquals('yoo', (string) $dom->documentElement->childNodes[0]->getAttribute('bar'));
    }

    public function testToHTMLDocument(): void
    {
        $str = str('<div><p bar="yoo" /></div>');
        $dom = $str->toHTMLDocument();

        $xpath = new \DOMXPath($dom);
        $elements = $xpath->query('/div/p');

        self::assertEquals('yoo', (string) $elements[0]->getAttribute('bar'));
    }

    public function testToSimpleXML(): void
    {
        $str = str('<div><p bar="yoo" /></div>');
        $xml = $str->toSimpleXML();
        $elements = $xml->xpath('/div/p');

        self::assertEquals('yoo', (string) $elements[0]['bar']);
    }

    protected function setUp(): void
    {
    }

    protected function tearDown(): void
    {
    }
}
