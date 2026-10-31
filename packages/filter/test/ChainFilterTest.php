<?php

declare(strict_types=1);

namespace Windwalker\Filter\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Filter\CallbackFilter;
use Windwalker\Filter\ChainFilter;
use Windwalker\Filter\Exception\ValidateException;
use Windwalker\Filter\Rule\Alnum;
use Windwalker\Filter\Rule\Length;
use Windwalker\Utilities\Iterator\PriorityQueue;

/**
 * @see ChainFilter
 */
class ChainFilterTest extends TestCase
{
    #[Test]
    public function constructorWrapsCallablesIntoCallbackFilters(): void
    {
        $chain = new ChainFilter([fn($v) => $v . 'a']);

        $filters = iterator_to_array(clone $chain->getFilters(), false);

        self::assertCount(1, $filters);
        self::assertInstanceOf(CallbackFilter::class, $filters[0]);
    }

    #[Test]
    public function filterRunsFiltersInPriorityOrder(): void
    {
        $chain = new ChainFilter();
        $chain->addFilter(fn($v) => $v . 'low', PriorityQueue::LOW)
            ->addFilter(fn($v) => $v . 'high', PriorityQueue::HIGH)
            ->addFilter(fn($v) => $v . 'normal');

        self::assertSame('xhighnormallow', $chain->filter('x'));
    }

    #[Test]
    public function filterCanBeCalledRepeatedlyWithoutConsumingTheQueue(): void
    {
        $chain = new ChainFilter([new Alnum()]);

        self::assertSame('abc', $chain->filter('a-b-c'));
        self::assertSame('xyz', $chain->filter('x-y-z'));
    }

    #[Test]
    public function testReturnsTrueWhenAllFiltersPass(): void
    {
        $chain = new ChainFilter([new Alnum(), new Length(10)]);

        self::assertTrue($chain->test('abc123'));
    }

    #[Test]
    public function testThrowsValidateExceptionWithValueDescriptionWhenFilterFails(): void
    {
        $chain = new ChainFilter([new Alnum()]);

        try {
            $chain->test('a-b');
            self::fail('Expected ValidateException');
        } catch (ValidateException $e) {
            self::assertInstanceOf(Alnum::class, $e->getValidator());
            self::assertStringContainsString('test failure, value is: string(3) "a-b"', $e->getMessage());
        }
    }

    #[Test]
    public function resetFiltersRemovesAllFilters(): void
    {
        $chain = new ChainFilter([new Alnum()]);

        $chain->resetFilters();

        self::assertCount(0, $chain->getFilters());
        self::assertSame('a-b', $chain->filter('a-b'));
    }
}
