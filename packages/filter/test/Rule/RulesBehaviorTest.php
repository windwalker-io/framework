<?php

declare(strict_types=1);

namespace Windwalker\Filter\Test\Rule;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Filter\AbstractRegexFilter;
use Windwalker\Filter\CallbackFilter;
use Windwalker\Filter\Rule\CastTo;
use Windwalker\Filter\Rule\Clamp;
use Windwalker\Filter\Rule\CompareWith;
use Windwalker\Filter\Rule\IPAddress;
use Windwalker\Filter\Rule\IPV6;
use Windwalker\Filter\Rule\Length;
use Windwalker\Filter\Rule\RawValue;
use Windwalker\Filter\Rule\Regex;

/**
 * Behavior of filter rules not covered by FilterRulesTest.
 */
class RulesBehaviorTest extends TestCase
{
    #[Test]
    #[DataProvider('clampProvider')]
    public function clampKeepsNumericValueWithinBounds(mixed $value, int|float|null $min, int|float|null $max, mixed $expected): void
    {
        self::assertSame($expected, new Clamp($min, $max)->filter($value));
    }

    public static function clampProvider(): array
    {
        return [
            'below min' => [1, 5, 10, 5],
            'above max' => [20, 5, 10, 10],
            'inside' => [7, 5, 10, 7],
            'numeric string' => ['20', 5, 10, 10],
            'no max' => [100, 5, null, 100],
        ];
    }

    #[Test]
    public function lengthTruncatesByBytesWhenUtf8IsDisabled(): void
    {
        self::assertSame(3, strlen(new Length(3, false)->filter('中文')));
        self::assertSame('中文', new Length(2)->filter('中文字'));
    }

    #[Test]
    public function lengthReturnsStringUntouchedWhenShortEnough(): void
    {
        self::assertSame('123', new Length(5)->filter(123));
    }

    #[Test]
    public function castToSupportsStrictModeAndSetters(): void
    {
        $filter = new CastTo('int', true);

        self::assertTrue($filter->isStrict());
        self::assertSame('int', $filter->getType());
        self::assertFalse($filter->test('abc'));
        self::assertTrue($filter->test(123));
    }

    #[Test]
    public function compareWithUsesConfiguredOperatorAndStrictness(): void
    {
        $filter = new CompareWith(5, '=', true);

        self::assertTrue($filter->filter(5));
        self::assertFalse($filter->filter('5'));

        $filter->setStrict(false)->setCompare(6)->setOperator('>');

        self::assertSame('>', $filter->getOperator());
        self::assertSame(6, $filter->getCompare());
        self::assertFalse($filter->filter(7));
        self::assertTrue($filter->filter(5));
    }

    #[Test]
    public function regexMatchKeepsOnlyMatchesByDefault(): void
    {
        $regex = new Regex('/\d+/');

        self::assertSame('12345', $regex->filter('a12b34c5'));
        self::assertSame('/\d+/', $regex->getRegex());
    }

    #[Test]
    public function regexReplaceTypeRemovesMatches(): void
    {
        $regex = new Regex('/\d+/', AbstractRegexFilter::TYPE_REPLACE);

        self::assertSame('abc', $regex->filter('a12b34c5'));
        self::assertSame('x', $regex->setRegex('/y/')->filter('xyy'));
    }

    #[Test]
    public function ipAddressAcceptsIpv4AndIpv6ButRejectsGarbage(): void
    {
        $rule = new IPAddress();

        self::assertTrue($rule->test('127.0.0.1'));
        self::assertTrue($rule->test('::1'));
        self::assertFalse($rule->test('not-an-ip'));
        self::assertSame('127.0.0.1', $rule->filter('127.0.0.1 xyz'));
    }

    #[Test]
    public function ipv6ValidatesOnlyIpv6Addresses(): void
    {
        $rule = new IPV6();

        self::assertTrue($rule->test('2001:db8::1'));
        self::assertFalse($rule->test('127.0.0.1'));
    }

    #[Test]
    public function rawValueReturnsValueAndAlwaysValidates(): void
    {
        $rule = new RawValue();

        self::assertSame('<b>', $rule->filter('<b>'));
        self::assertTrue($rule->test(['anything']));
    }

    #[Test]
    public function callbackFilterInvokesHandlerAndAllowsReplacingIt(): void
    {
        $filter = new CallbackFilter('strtoupper');

        self::assertSame('ABC', $filter->filter('abc'));

        $filter->setHandler('strrev');

        self::assertSame('cba', $filter->filter('abc'));
        self::assertSame('strrev', $filter->getHandler());
    }

    #[Test]
    public function toStringDescribesClassAndProperties(): void
    {
        self::assertSame(
            'Windwalker\Filter\Rule\Length(max: 5, utf8: true)',
            (string) new Length(5)
        );
    }
}
