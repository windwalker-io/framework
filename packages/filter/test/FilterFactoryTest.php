<?php

declare(strict_types=1);

namespace Windwalker\Filter\Test;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Filter\FilterFactory;
use Windwalker\Test\Traits\BaseAssertionTrait;

/**
 * The FilterFactoryTest class.
 */
class FilterFactoryTest extends TestCase
{
    use BaseAssertionTrait;

    protected ?FilterFactory $instance;

    /**
     * @see  FilterFactory::createFromSyntax
     */
    #[Test]
    public function createFromSyntaxBuildsRangeFilterWithNamedArguments(): void
    {
        $filter = $this->instance->createFromSyntax('range(min : 5, max:10)');

        self::assertEquals(
            10,
            $filter->filter(20)
        );
    }

    #[Test]
    public function createFromSyntaxBuildsBoolFilter(): void
    {
        $filter = $this->instance->createFromSyntax('bool');

        self::assertEquals(
            true,
            $filter->filter('Hello')
        );
    }

    #[Test]
    public function createFromSyntaxBuildsDefaultFilterReturningFallbackForNull(): void
    {
        $filter = $this->instance->createFromSyntax('default("foo")');

        self::assertEquals(
            'foo',
            $filter->filter(null)
        );
    }

    #[Test]
    public function createNestedFiltersNestedValuesByRules(): void
    {
        $map = $this->instance->createNested(
            [
                'id' => 'required|int|range(min:1,max:100)',
                'alias' => 'alnum|length(max:10)|default(hello)',
                'item' => [
                    'content' => 'func(strtoupper)',
                    'params' => fn($v) => json_decode($v, true, 512, JSON_THROW_ON_ERROR),
                ],
            ]
        );

        $result = $map->filter(
            [
                'id' => '600',
                'alias' => 'title is here 123 Hello World',
                'item' => [
                    'intro' => 'Intro',
                    'content' => 'Flower',
                    'params' => '{"foo": "bar"}',
                ],
            ]
        );

        self::assertEquals(
            [
                'id' => 100,
                'alias' => 'titleisher',
                'item' => [
                    'intro' => 'Intro',
                    'content' => 'FLOWER',
                    'params' => [
                        'foo' => 'bar',
                    ],
                ],
            ],
            $result
        );
    }

    #[Test]
    public function createNestedTestThrowsWhenRuleFails(): void
    {
        $this->expectExceptionMessageIsOrContains(
            'Field "id" not match - Validator: Windwalker\Filter\Rule\Range(min: 1, max: 100)'
            . ' test failure, value is: string(3) "600"'
        );

        $map = $this->instance->createNested(
            [
                'id' => 'required|int|range(min=1,max=100)',
                'alias' => 'alnum|length(max=10)|default(hello)',
                'item' => [
                    'content' => 'func(strtoupper)',
                    'params' => fn($v) => json_decode($v, true, 512, JSON_THROW_ON_ERROR),
                ],
            ]
        );

        $map->test(
            [
                'id' => '600',
                'alias' => 'title is here 123 Hello World',
                'item' => [
                    'intro' => 'Intro',
                    'content' => 'Flower',
                    'params' => '{"foo": "bar"}',
                ],
            ]
        );
    }

    #[Before]
    protected function createFilterFactory(): void
    {
        $this->instance = new FilterFactory();
    }
}
