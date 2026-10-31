<?php

declare(strict_types=1);

namespace Windwalker\ORM\Test;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\ORM\Exception\NestedHandleException;
use Windwalker\ORM\Nested\NestedEntityInterface;
use Windwalker\ORM\Nested\Position;
use Windwalker\ORM\NestedSetMapper;
use Windwalker\ORM\Test\Entity\StubNestedSet;

/**
 * The NestedSetMapperTest class.
 */
class NestedSetMapperTest extends AbstractORMTestCase
{
    protected ?NestedSetMapper $instance;

    #[Test]
    public function createRootCreatesRootNode()
    {
        $items = iterator_to_array($this->instance->findList([]));

        self::assertEquals(
            'root',
            $items[0]->getTitle()
        );
    }

    #[Test]
    public function checkRejectsInvalidParentId()
    {
        $this->expectException(NestedHandleException::class);
        $this->expectExceptionMessage('ReferenceId is negative.');

        $child = new StubNestedSet();
        $child->setLft(2);

        $this->instance->setPosition($child, -1, Position::LAST_CHILD);

        $this->instance->saveOne($child);
    }

    #[Test]
    public function checkRejectsMissingParent()
    {
        $this->expectExceptionMessage('Reference ID 999 not found.');

        $child = new StubNestedSet();

        $this->instance->setPosition($child, 999, Position::FIRST_CHILD);

        $this->instance->createOne($child);
    }

    #[Test]
    public function saveComputesPositionsAndNestedSetValues()
    {
        $child = new StubNestedSet();
        $child->setTitle('Flower');
        $child->setAlias('flower');

        $this->instance->setPosition($child, 1, Position::FIRST_CHILD);
        $this->instance->createOne($child);

        $child = new StubNestedSet();
        $child->setTitle('Sakura');
        $child->setAlias('sakura');

        $this->instance->setPosition($child, 2, Position::FIRST_CHILD);
        $this->instance->saveOne($child);

        // First child
        $child = new StubNestedSet();
        $child->setTitle('Olive');
        $child->setAlias('olive');

        $this->instance->setPosition($child, 2, Position::FIRST_CHILD);
        $ent = $this->instance->saveOne($child);
        $this->instance->rebuildPath($child);

        self::assertEquals(
            [2, 3],
            [
                $ent->getLft(),
                $ent->getRgt(),
            ]
        );

        // Last child
        $child = new StubNestedSet();
        $child->setTitle('Sunflower');
        $child->setAlias('sunflower');

        $this->instance->setPosition($child, 2, Position::LAST_CHILD);
        /** @var NestedEntityInterface $ent */
        $ent = $this->instance->saveOne($child);
        $this->instance->rebuildPath($child);

        self::assertEquals(
            [6, 7],
            [
                $ent->getLft(),
                $ent->getRgt(),
            ]
        );

        // Before
        $child = new StubNestedSet();
        $child->setTitle('Rose');
        $child->setAlias('rose');

        $ent = $this->instance->putBefore($child, 2);

        self::assertEquals(
            [1, 2],
            [
                $ent->getLft(),
                $ent->getRgt(),
            ]
        );

        // After
        $child = new StubNestedSet();
        $child->setTitle('Orchid');
        $child->setAlias('orchid');

        $ent = $this->instance->putAfter($child, 2);

        self::assertEquals(
            [11, 12],
            [
                $ent->getLft(),
                $ent->getRgt(),
            ]
        );
    }

    #[Test]
    public function getPathReturnsAncestorPath()
    {
        $path = $this->instance->getPath(5);

        $ids = $path->column('id', null, true)->dump();
        $paths = $path->column('path', null, true)->dump();

        self::assertEquals([1, 2, 5], $ids);
        self::assertEquals(['', 'flower', 'flower/sunflower'], $paths);
    }

    #[Test]
    public function getAncestorsReturnsParentChain()
    {
        $path = $this->instance->getAncestors(5);

        $ids = $path->column('id', null, true)->dump();
        $paths = $path->column('path', null, true)->dump();

        self::assertEquals([1, 2], $ids);
        self::assertEquals(['', 'flower'], $paths);
    }

    /**
     * @see  NestedSetMapper::getTree
     */
    #[Test]
    public function getTreeReturnsDescendants(): void
    {
        $tree = $this->instance->getTree(1);
        $ids = $tree->column('id', null, true)->dump();
        $paths = $tree->column('path', null, true)->dump();

        self::assertEquals(
            [1, 6, 2, 4, 3, 5, 7],
            $ids,
        );
        self::assertEquals(
            [
                '',
                'rose',
                'flower',
                'flower/olive',
                'flower/sakura',
                'flower/sunflower',
                'orchid',
            ],
            $paths
        );
    }

    /**
     * @see  NestedSetMapper::isLeaf
     */
    #[Test]
    public function isLeafDetectsNodesWithoutChildren(): void
    {
        self::assertTrue($this->instance->isLeaf(5));
        self::assertFalse($this->instance->isLeaf(2));
    }

    /**
     * @see  NestedSetMapper::move
     */
    #[Test]
    public function moveRepositionsNodeAmongSiblings(): void
    {
        /** @var NestedEntityInterface $item */
        $item = $this->instance->findOne(5);

        $this->instance->move($item, Position::MOVE_UP);

        self::assertEquals([6, 7], [$item->getLft(), $item->getRgt()]);
    }

    /**
     * @see  NestedSetMapper::moveByReference
     */
    #[Test]
    public function moveByReferenceRepositionsNodeRelativeToTarget(): void
    {
        /** @var NestedEntityInterface $item */
        $item = $this->instance->findOne(5);

        $this->instance->moveByReference($item, 1, Position::LAST_CHILD);

        self::assertEquals([11, 12], [$item->getLft(), $item->getRgt()]);
    }

    #[Test]
    public function entityReturnsChildrenAndAncestors(): void
    {
        /** @var NestedEntityInterface $item */
        $item = $this->instance->findOne(2);

        self::assertEquals(
            [4, 3],
            $item->getChildren()->all()->column('id', null, true)->dump()
        );

        /** @var NestedEntityInterface $item */
        $item = $this->instance->findOne(3);

        self::assertEquals(
            [1, 2],
            $item->getAncestors()->all()->column('id', null, true)->dump()
        );

        /** @var NestedEntityInterface $item */
        $item = $this->instance->getRoot();

        self::assertEquals(
            [1, 6, 2, 4, 3, 7, 5],
            $item->getTree()->all()->column('id', null, true)->dump()
        );
    }

    /**
     * @see  NestedSetMapper::rebuild
     */
    #[Test]
    public function rebuildRestoresNestedSetValues(): void
    {
        $this->instance->update()
            ->set('lft', 0)
            ->set('rgt', 0)
            ->execute();

        $this->instance->rebuild(1);

        $lfts = $this->instance->select('lft')
            ->loadColumn()
            ->dump();

        self::assertEquals(
            [0, 1, 2, 4, 7, 9, 11],
            $lfts
        );
    }

    /**
     * @see  NestedSetMapper::rebuildPath
     */
    #[Test]
    public function rebuildPathRestoresPaths(): void
    {
        $this->instance->update()
            ->set('path', '')
            ->execute();

        $this->instance->rebuild(1);

        $paths = $this->instance->select('path')
            ->loadColumn()
            ->dump();

        self::assertEquals(
            [
                '',
                'flower',
                'flower/sakura',
                'flower/olive',
                'sunflower',
                'rose',
                'orchid',
            ],
            $paths
        );
    }

    #[Test]
    public function deleteRemovesNodeAndDescendants(): void
    {
        $this->rollbackAfter(function () {
            $newChild = new StubNestedSet();
            $newChild->setTitle('Kapok');
            $newChild->setAlias('kapok');

            $this->instance->putBefore($newChild, 2);

            $this->instance->deleteWhere(8);

            self::assertNull(
                $this->instance->findOne(8)
            );

            // Delete children
            $this->instance->deleteWhere(2);

            self::assertNull($this->instance->findOne(3));
            self::assertNull($this->instance->findOne(4));
        });
    }

    /**
     * @see  NestedSetMapper::getRoot
     */
    #[Test]
    public function getRootReturnsRootNode(): void
    {
        /** @var StubNestedSet $root */
        $root = $this->instance->getRoot();

        self::assertEquals(
            1,
            $root->getId()
        );

        self::assertEquals(
            'root',
            $root->getAlias()
        );
    }

    #[Test]
    public function isLeafAcceptsEntitiesAndReturnsFalseForMissingNodes(): void
    {
        self::assertTrue($this->instance->isLeaf($this->instance->findOne(5)));
        self::assertFalse($this->instance->isLeaf($this->instance->findOne(2)));
        self::assertFalse($this->instance->isLeaf(99999));
    }

    #[Test]
    public function setPositionAcceptsReferenceEntitiesAndShortcuts(): void
    {
        $reference = $this->instance->findOne(2);
        $entity = new StubNestedSet();

        $this->instance->setPositionAppendTo($entity, $reference);

        self::assertEquals(2, $entity->getPosition()->getReferenceId());
        self::assertSame(Position::LAST_CHILD, $entity->getPosition()->getPosition());

        $this->instance->setPositionPrependTo($entity, 3);

        self::assertEquals(3, $entity->getPosition()->getReferenceId());
        self::assertSame(Position::FIRST_CHILD, $entity->getPosition()->getPosition());
    }

    #[Test]
    public function createRootRejectsWhenRootAlreadyExists(): void
    {
        $this->expectException(NestedHandleException::class);
        $this->expectExceptionMessage('Root has already exists.');

        $this->instance->createRoot(['title' => 'root2']);
    }

    #[Test]
    public function createRootIfNotExistReturnsExistingRoot(): void
    {
        $root = $this->instance->createRootIfNotExist(['title' => 'root']);

        self::assertEquals(1, $root->getId());
    }

    #[Test]
    public function appendToAndPrependToSaveNodeUnderReference(): void
    {
        $this->rollbackAfter(function () {
            $appended = new StubNestedSet();
            $appended->setTitle('Appended');
            $appended->setAlias('appended');

            $appended = $this->instance->appendTo($appended, 1);

            self::assertEquals(1, $appended->getParentId());

            $prepended = new StubNestedSet();
            $prepended->setTitle('Prepended');
            $prepended->setAlias('prepended');

            $prepended = $this->instance->prependTo($prepended, 1);

            self::assertEquals(1, $prepended->getParentId());
            self::assertLessThan($appended->getLft(), $this->instance->findOne($prepended->getId())->getLft());
        });
    }

    #[Test]
    public function saveRejectsParentThatIsSelfOrChild(): void
    {
        $this->rollbackAfter(function () {
            $node = $this->instance->findOne(2);

            $this->instance->setPositionAppendTo($node, 2);

            try {
                $this->instance->saveOne($node);
                self::fail('Expected NestedHandleException for self parent.');
            } catch (NestedHandleException) {
                self::assertTrue(true);
            }

            $node = $this->instance->findOne(2);
            $this->instance->setPositionAppendTo($node, 3);

            $this->expectException(NestedHandleException::class);

            $this->instance->saveOne($node);
        });
    }

    private function rollbackAfter(callable $callback): void
    {
        $platform = self::$db->getPlatform();
        $platform->transactionStart();

        try {
            $callback();
        } finally {
            $platform->transactionRollback();
        }
    }

    #[Before]
    protected function createMapperInstance(): void
    {
        /** @var NestedSetMapper $mapper */
        $mapper = self::$orm->mapper(StubNestedSet::class);

        $this->instance = $mapper;
    }

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        /** @var NestedSetMapper $mapper */
        $mapper = self::$orm->mapper(StubNestedSet::class);
        $mapper->createRoot(
            [
                'title' => 'root',
                'access' => 1,
            ]
        );
    }

    /**
     * @inheritDoc
     */
    protected static function setupDatabase(): void
    {
        self::importFromFile(__DIR__ . '/Stub/nested.sql');
    }
}
