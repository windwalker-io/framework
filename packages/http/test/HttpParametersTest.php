<?php

declare(strict_types=1);

namespace Windwalker\Http\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;
use Windwalker\Http\HttpParameters;

class HttpParametersTest extends TestCase
{
    #[Test]
    public function constructorLowercasesKeys(): void
    {
        $params = new HttpParameters(['Content-Type' => 'text/html', 'X-FOO' => 'bar']);

        self::assertSame(['content-type' => 'text/html', 'x-foo' => 'bar'], $params->dump());
    }

    #[Test]
    public function wrapReturnsSameInstanceOrCreatesNewFromArray(): void
    {
        $params = new HttpParameters(['a' => 1]);

        self::assertSame($params, HttpParameters::wrap($params));
        self::assertSame(['a' => 1], HttpParameters::wrap(['A' => 1])->dump());
    }

    #[Test]
    public function getAndSetAreCaseInsensitive(): void
    {
        $params = new HttpParameters();

        self::assertSame($params, $params->set('Accept-Language', 'en'));
        self::assertSame('en', $params->get('ACCEPT-language'));
        self::assertNull($params->get('missing'));
    }

    #[Test]
    public function getReturnsReferenceToStoredValue(): void
    {
        $params = new HttpParameters(['list' => [1]]);

        $list = &$params->get('LIST');
        $list[] = 2;

        self::assertSame([1, 2], $params->get('list'));
    }

    #[Test]
    public function defSetsDefaultOnlyWhenValueIsMissing(): void
    {
        $params = new HttpParameters(['Existing' => 'keep']);

        self::assertSame('keep', $params->def('EXISTING', 'other'));
        self::assertSame('new', $params->def('Fresh', 'new'));
        self::assertSame('new', $params->get('fresh'));
    }

    #[Test]
    public function hasTreatsNullValuesAsMissing(): void
    {
        $params = new HttpParameters(['a' => 0, 'n' => null]);

        self::assertTrue($params->has('A'));
        self::assertFalse($params->has('N'));
        self::assertFalse($params->has('missing'));
    }

    #[Test]
    public function removeDeletesKeyCaseInsensitivelyAndIgnoresMissing(): void
    {
        $params = new HttpParameters(['a' => 1, 'b' => 2]);

        self::assertSame($params, $params->remove('A')->remove('nope'));
        self::assertSame(['b' => 2], $params->dump());
    }

    #[Test]
    public function dumpRecursivelyConvertsNestedObjects(): void
    {
        $params = new HttpParameters(['obj' => (object) ['x' => (object) ['y' => 1]]]);

        self::assertSame(['obj' => ['x' => ['y' => 1]]], $params->dump(true));
        self::assertInstanceOf(stdClass::class, $params->dump()['obj']);
    }

    #[Test]
    public function supportsArrayAccessAndIteration(): void
    {
        $params = new HttpParameters();

        $params['Foo'] = 'bar';

        self::assertTrue(isset($params['FOO']));
        self::assertSame('bar', $params['foo']);

        $params['a'] = 1;
        unset($params['FOO']);

        self::assertFalse(isset($params['foo']));
        self::assertSame(['a' => 1], iterator_to_array($params));
    }
}
