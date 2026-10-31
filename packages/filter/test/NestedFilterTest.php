<?php

declare(strict_types=1);

namespace Windwalker\Filter\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Filter\Exception\ValidateException;
use Windwalker\Filter\NestedFilter;
use Windwalker\Filter\Rule\Alnum;
use Windwalker\Filter\Rule\Absolute;

/**
 * @see NestedFilter
 */
class NestedFilterTest extends TestCase
{
    protected function createFilter(): NestedFilter
    {
        return new NestedFilter(
            [
                'name' => new Alnum(),
                'meta' => ['count' => new Absolute()],
            ]
        );
    }

    #[Test]
    public function filterAppliesFiltersOnlyToMappedPaths(): void
    {
        $result = $this->createFilter()->filter(
            [
                'name' => 'a-b c',
                'other' => 'x-y',
                'meta' => ['count' => -5],
            ]
        );

        self::assertSame(
            ['name' => 'abc', 'other' => 'x-y', 'meta' => ['count' => 5]],
            $result
        );
    }

    #[Test]
    public function testReturnsTrueWhenMappedValuesAreValid(): void
    {
        self::assertTrue($this->createFilter()->test(['name' => 'abc', 'meta' => ['count' => 5]]));
    }

    #[Test]
    public function testThrowsExceptionNamingTheFailedField(): void
    {
        try {
            $this->createFilter()->test(['name' => 'abc', 'meta' => ['count' => -5]]);
            self::fail('Expected ValidateException');
        } catch (ValidateException $e) {
            self::assertStringStartsWith('Field "meta.count" not match - ', $e->getMessage());
            self::assertInstanceOf(Absolute::class, $e->getValidator());
        }
    }

    #[Test]
    public function getReturnsFilterByNameOrNull(): void
    {
        $filter = $this->createFilter();

        self::assertInstanceOf(Alnum::class, $filter->get('name'));
        self::assertNull($filter->get('missing'));
    }
}
