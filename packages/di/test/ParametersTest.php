<?php

declare(strict_types=1);

namespace Windwalker\DI\Test;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\DI\Container;
use Windwalker\DI\Parameters;

use function Windwalker\ref;

/**
 * The ParametersTest class.
 */
class ParametersTest extends TestCase
{
    protected Parameters $instance;

    /**
     * @see  Parameters::extract
     */
    #[Test]
    public function extractsReferencedValues(): void
    {
        $foo = $this->instance->extract('foo', true);

        $this->instance->setDeep('foo.goo', 'World');

        self::assertEquals(
            'Hello',
            $foo->getDeep('bar.yoo')
        );

        self::assertEquals(
            'Hello',
            $foo->getDeep('foo.bar.yoo')
        );

        self::assertEquals(
            'World',
            $foo->getDeep('goo')
        );
    }

    #[Test]
    public function resolvesReferencesFromParentParameters(): void
    {
        $foo = $this->instance->extract('foo', true);

        self::assertEquals(
            'Hello',
            $foo->getDeep('ref')
        );

        $foo->set('ref', ref('bar.yoo'));

        self::assertEquals(
            'Hello',
            $foo->getDeep('ref')
        );
    }

    /**
     * @see  Parameters::hasDeep
     */
    #[Test]
    public function hasDeepReportsWhetherPathsResolve(): void
    {
        $foo = $this->instance->extract('foo', true);

        self::assertTrue($foo->hasDeep('bar.yoo'));
        self::assertTrue($foo->hasDeep('foo.bar.yoo'));
        self::assertFalse($foo->hasDeep('foo.goo'));
    }

    /**
     * @see  Parameters::has
     */
    #[Test]
    public function hasReturnsWhetherAValueExists(): void
    {
        self::assertTrue($this->instance->has('foo'));
        self::assertFalse($this->instance->has('missing'));
    }

    #[Test]
    public function getResolvesReferencesAndReturnsNullForMissingValues(): void
    {
        self::assertSame('Hello', $this->instance->get('ref'));
        self::assertNull($this->instance->get('missing'));
    }

    /**
     * @see  Parameters::getParent
     */
    #[Test]
    public function returnsTheParentForReferencedParameters(): void
    {
        $foo = $this->instance->extract('foo', true);

        self::assertSame($this->instance, $foo->getParent());
    }

    #[Test]
    public function createsChildScopesWithParentFallbackAndLocalOverrides(): void
    {
        $child = $this->instance->createChild();

        self::assertSame('Hello', $child->getDeep('foo.bar.yoo'));

        $child->setDeep('foo.bar.yoo', 'World');

        self::assertSame('World', $child->getDeep('foo.bar.yoo'));
        self::assertSame('Hello', $this->instance->getDeep('foo.bar.yoo'));
    }

    #[Test]
    public function iteratesParentValuesOnlyWhenRequested(): void
    {
        $child = $this->instance->createChild();
        $child->set('local', 'child');
        $this->instance->set('added-later', 'parent');

        self::assertArrayNotHasKey('added-later', iterator_to_array($child->getIterator(false)));
        self::assertSame('parent', iterator_to_array($child->getIterator())['added-later']);
    }

    #[Test]
    public function dumpsLocalAndParentValuesAndResolvesReferences(): void
    {
        $child = $this->instance->createChild();
        $child->set('local', 'child');

        $dump = $child->dump();

        self::assertSame('Hello', $dump['ref']);
        self::assertSame('child', $dump['local']);
        self::assertArrayHasKey('foo', $dump);
    }

    #[Test]
    public function containerChildOverlaysBranchesWithoutChangingParent(): void
    {
        $container = new Container();
        $container->setParameters($this->instance);
        $child = $container->createChild()->getParameters();
        $child->setDeep('foo.bar.local', 'child');

        $expected = ['bar' => ['local' => 'child', 'yoo' => 'Hello']];

        self::assertEquals($expected, $child->get('foo'));
        self::assertEquals($expected, $child->getDeep('foo'));
        self::assertEquals($expected, $child->dump()['foo']);
        self::assertEquals($expected, iterator_to_array($child)['foo']);
        self::assertNull($this->instance->getDeep('foo.bar.local'));
    }

    #[Test]
    public function overlaysMultipleAncestorScopesAndObservesLaterParentUpdates(): void
    {
        $child = $this->instance->createChild()->reset();
        $child->setDeep('foo.bar.local', 'child');
        $grandchild = $child->createChild()->reset();
        $grandchild->setDeep('foo.bar.yoo', 'grandchild');
        $this->instance->setDeep('foo.bar.later', 'parent');

        $expected = ['bar' => ['yoo' => 'grandchild', 'local' => 'child', 'later' => 'parent']];

        self::assertEquals($expected, $grandchild->getDeep('foo'));
        self::assertEquals($expected, $grandchild->get('foo'));
        self::assertEquals($expected, $grandchild->dump()['foo']);
        self::assertSame('Hello', $this->instance->getDeep('foo.bar.yoo'));
    }

    #[Test]
    public function extractsMergedBranchesAsDetachedSnapshots(): void
    {
        $child = $this->instance->createChild()->reset();
        $child->setDeep('foo.bar.local', 'child');
        $snapshot = $child->extract('foo');
        $all = $child->extract();
        $this->instance->setDeep('foo.bar.yoo', 'changed');
        $snapshot->setDeep('bar.local', 'snapshot');

        self::assertSame('Hello', $snapshot->getDeep('bar.yoo'));
        self::assertSame('Hello', $all->getDeep('foo.bar.yoo'));
        self::assertSame('child', $child->getDeep('foo.bar.local'));
        self::assertNull($snapshot->getParent());
    }

    #[Test]
    public function proxiesInheritedBranchesWithLiveReadsAndChildOnlyWrites(): void
    {
        $this->instance->setDeep('foo.bar.list', ['parent']);
        $child = $this->instance->createChild()->reset();
        $foo = $child->proxy('foo');
        $bar = $foo->proxy('bar');
        $bar->set('local', 'child');
        $bar->set('list', ['child']);
        $this->instance->setDeep('foo.bar.later', 'parent');

        $expected = [
            'local' => 'child',
            'list' => ['parent', 'child'],
            'yoo' => 'Hello',
            'later' => 'parent',
        ];

        self::assertEquals($expected, $child->getDeep('foo.bar'));
        self::assertEquals($expected, $foo->get('bar'));
        self::assertEquals($expected, $foo->getDeep('bar'));
        self::assertEquals($expected, $bar->dump());
        self::assertEquals($expected, iterator_to_array($bar));
        self::assertSame(['parent', 'child'], $bar->get('list'));
        self::assertSame(['parent', 'child'], $bar->getDeep('list'));
        self::assertSame('Hello', $bar->get('yoo'));
        self::assertNull($this->instance->getDeep('foo.bar.local'));
        self::assertSame(['parent'], $this->instance->getDeep('foo.bar.list'));
    }

    #[Test]
    public function mergesListsAndEmptyArraysUsingDumpSemantics(): void
    {
        $this->instance->set('list', ['parent']);
        $child = $this->instance->createChild()->reset();
        $child->set('list', ['child']);
        $child->set('foo', []);

        self::assertSame(['parent', 'child'], $child->get('list'));
        self::assertSame(['parent', 'child'], $child->getDeep('list'));
        self::assertSame(['parent', 'child'], $child->dump()['list']);
        self::assertSame($this->instance->get('foo'), $child->get('foo'));
    }

    #[Test]
    public function preservesScalarOverridesAndNullLeafFallback(): void
    {
        $child = $this->instance->createChild()->reset();
        $child->setDeep('foo.bar.yoo', null);
        $child->set('disabled', false);
        $child->set('zero', 0);
        $child->set('empty', '');

        self::assertSame('Hello', $child->getDeep('foo.bar.yoo'));
        self::assertFalse($child->get('disabled'));
        self::assertSame(0, $child->getDeep('zero'));
        self::assertSame('', $child->get('empty'));

        $child->set('foo', 'replaced');
        self::assertSame('replaced', $child->get('foo'));
        self::assertSame('replaced', $child->getDeep('foo'));
    }

    #[Test]
    public function resolvesInheritedReferencesAgainstChildOverrides(): void
    {
        $child = $this->instance->createChild()->reset();
        $child->setDeep('foo.bar.yoo', 'child');
        $child->set('branch', ref('foo.bar'));

        self::assertSame('child', $child->getDeep('ref'));
        self::assertSame('child', $child->get('ref'));
        self::assertSame(['yoo' => 'child'], $child->getDeep('branch'));
        self::assertSame('child', $child->dump()['ref']);
        self::assertSame(['yoo' => 'child'], $child->dump()['branch']);
        self::assertSame('Hello', $this->instance->getDeep('ref'));
    }

    #[Test]
    public function supportsCustomDelimitersAndLiteralKeys(): void
    {
        $this->instance->set('literal.key', ['inherited' => true]);
        $child = $this->instance->createChild()->reset();
        $child->setDeep('foo/bar/local', 'child', '/');
        $child->set('literal.key', ['local' => true]);

        self::assertEquals(['local' => 'child', 'yoo' => 'Hello'], $child->getDeep('foo/bar', '/'));
        self::assertEquals(['local' => true, 'inherited' => true], $child->get('literal.key'));
        self::assertEquals(['local' => 'child', 'yoo' => 'Hello'], $child->proxy('foo')->getDeep('bar', '/'));
        self::assertSame('Hello', $child->proxy('foo.bar')->getDeep('yoo', '/'));
    }

    #[Test]
    public function rejectsExtractingAnInheritedScalar(): void
    {
        $this->instance->set('scalar', 'value');
        $child = $this->instance->createChild()->reset();

        $this->expectException(\TypeError::class);
        $child->extract('scalar');
    }

    #[Test]
    public function dumpsProxyReferencesUsingTheOwningScope(): void
    {
        $this->instance->setDeep('foo.bar.alias', ref('foo.bar.yoo'));
        $child = $this->instance->createChild()->reset();
        $child->setDeep('foo.bar.yoo', 'child');

        self::assertSame('child', $child->proxy('foo.bar')->dump()['alias']);
        self::assertSame('child', $child->dump()['foo']['bar']['alias']);
    }

    #[Test]
    public function iterationIncludesEachMergedKeyOnceAndCanExposeLocalStorage(): void
    {
        $child = $this->instance->createChild()->reset();
        $child->setDeep('foo.bar.local', 'child');
        $keys = [];

        foreach ($child as $key => $value) {
            $keys[] = $key;
        }

        self::assertCount(count(array_unique($keys)), $keys);
        self::assertSame(
            ['foo' => ['bar' => ['local' => 'child']]],
            iterator_to_array($child->getIterator(false)),
        );
    }

    #[Before]
    public function initializeParameters(): void
    {
        $this->instance = new Parameters(
            [
                'foo' => [
                    'bar' => [
                        'yoo' => 'Hello',
                    ],
                ],
                'ref' => ref('foo.bar.yoo'),
            ]
        );
    }
}
