<?php

declare(strict_types=1);

namespace Windwalker\ORM\Test;

use Windwalker\ORM\Exception\NestedHandleException;
use Windwalker\ORM\Nested\MultiTreeNestedEntityInterface;
use Windwalker\ORM\Nested\NestedEntityInterface;
use Windwalker\ORM\Nested\Position;
use Windwalker\ORM\NestedSetMapper;
use Windwalker\ORM\Test\Entity\StubMTNestedSet;
use Windwalker\ORM\Test\Entity\StubNestedSet;

use function Windwalker\uid;

/**
 * The NestedSetMapperTest class.
 */
class MultiTreeNestedSetMapperTest extends AbstractORMTestCase
{
    protected ?NestedSetMapper $instance;

    public function testRoot()
    {
        $items = iterator_to_array($this->instance->findList([]));

        self::assertEquals(
            'root:1',
            $items[0]->getTitle()
        );

        self::assertEquals(
            'root:2',
            $items[1]->getTitle()
        );

        self::assertEquals(
            'root:3',
            $items[2]->getTitle()
        );
    }

    public function testCheckInvalidParentId()
    {
        $this->expectException(NestedHandleException::class);
        $this->expectExceptionMessage('ReferenceId is negative.');

        $child = new StubNestedSet();
        $child->setLft(2);

        $this->instance->setPosition($child, -1, Position::LAST_CHILD);

        $this->instance->saveOne($child);
    }

    public function testCheckParentIdNotExists()
    {
        $this->expectExceptionMessage('Reference ID 999 not found.');

        $child = new StubNestedSet();

        $this->instance->setPosition($child, 999, Position::FIRST_CHILD);

        $this->instance->createOne($child);
    }

    public function testPostionAndSave()
    {
        $child = new StubNestedSet();
        $child->setTitle('Flower');
        $child->setAlias('flower');

        $this->instance->setPosition($child, 1, Position::FIRST_CHILD);
        $flower = $this->instance->createOne($child);

        self::assertEquals(
            [1, 2],
            [
                $flower->getLft(),
                $flower->getRgt(),
            ],
            'Flower position wrong.'
        );

        $child = new StubNestedSet();
        $child->setTitle('Sakura');
        $child->setAlias('sakura');

        $this->instance->setPosition($child, 2, Position::FIRST_CHILD);
        $sakura = $this->instance->saveOne($child);

        self::assertEquals(
            [1, 2],
            [
                $flower->getLft(),
                $flower->getRgt(),
            ],
            'Sakura position wrong.'
        );

        // First child
        $child = new StubNestedSet();
        $child->setTitle('Olive');
        $child->setAlias('olive');

        $this->instance->setPosition($child, $sakura->getId(), Position::FIRST_CHILD);
        $ent = $this->instance->saveOne($child);
        $this->instance->rebuildPath($child);

        self::assertEquals(
            [2, 3],
            [
                $ent->getLft(),
                $ent->getRgt(),
            ],
            'Olive position wrong'
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
            [5, 6],
            [
                $ent->getLft(),
                $ent->getRgt(),
            ],
            'Sunflower position wrong.'
        );

        // Before
        $child = new StubNestedSet();
        $child->setTitle('Rose');
        $child->setAlias('rose');

        $ent = $this->instance->putBefore($child, $ent->getId());

        self::assertEquals(
            [5, 6],
            [
                $ent->getLft(),
                $ent->getRgt(),
            ]
        );

        // After
        $child = new StubNestedSet();
        $child->setTitle('Orchid');
        $child->setAlias('orchid');

        $ent = $this->instance->putAfter($child, $ent->getId());

        self::assertEquals(
            [7, 8],
            [
                $ent->getLft(),
                $ent->getRgt(),
            ]
        );
    }

    public function testGetPath()
    {
        $path = $this->instance->getPath(6);

        $ids = $path->column('id', null, true)->dump();
        $paths = $path->column('path', null, true)->dump();

        self::assertEquals([2, 5, 6], $ids);
        self::assertEquals(['', 'sakura', 'sakura/olive'], $paths);
    }

    public function testGetAncestors()
    {
        $path = $this->instance->getAncestors(6);

        $ids = $path->column('id', null, true)->dump();
        $paths = $path->column('path', null, true)->dump();

        self::assertEquals([2, 5], $ids);
        self::assertEquals(['', 'sakura'], $paths);
    }

    /**
     * @see  NestedSetMapper::getTree
     */
    public function testGetTree(): void
    {
        $tree = $this->instance->getTree(2);
        $ids = $tree->column('id', null, true)->dump();
        $paths = $tree->column('path', null, true)->dump();

        self::assertEquals(
            [2, 5, 6, 8, 9, 7],
            $ids,
        );
        self::assertEquals(
            [
                '',
                'sakura',
                'sakura/olive',
                'rose',
                'orchid',
                'sunflower',
            ],
            $paths
        );
    }

    /**
     * @see  NestedSetMapper::isLeaf
     */
    public function testIsLeaf(): void
    {
        self::assertTrue($this->instance->isLeaf(6));
        self::assertFalse($this->instance->isLeaf(5));
    }

    /**
     * @see  NestedSetMapper::move
     */
    public function testMove(): void
    {
        /** @var NestedEntityInterface $item */
        $item = $this->instance->findOne(9);

        $this->instance->move($item, Position::MOVE_UP);

        self::assertEquals([5, 6], [$item->getLft(), $item->getRgt()]);
    }

    /**
     * @see  NestedSetMapper::moveByReference
     */
    public function testMoveByReference(): void
    {
        /** @var NestedEntityInterface $item */
        $item = $this->instance->findOne(9);

        $this->instance->moveByReference($item, 7, Position::LAST_CHILD);

        self::assertEquals([8, 9], [$item->getLft(), $item->getRgt()]);
    }

    /**
     * @see  NestedSetMapper::moveByReference
     */
    public function testMoveCrossTree(): void
    {
        /** @var NestedEntityInterface|MultiTreeNestedEntityInterface $item */
        $item = $this->instance->findOne(5);

        $this->instance->moveByReference($item, 1, Position::LAST_CHILD);

        self::assertEquals([3, 6], [$item->getLft(), $item->getRgt()]);
        self::assertEquals(1, $item->getRootId());

        /** @var MultiTreeNestedEntityInterface $child */
        $child = $this->instance->findOne(['parent_id' => 5]);

        self::assertEquals(1, $child->getRootId());
    }

    public function testEntityGetChildrenAndAncestors(): void
    {
        /** @var NestedEntityInterface $item */
        $item = $this->instance->findOne(2);

        self::assertEquals(
            [8, 7],
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
            [1, 4, 5, 6],
            $item->getTree()->all()->column('id', null, true)->dump()
        );
    }

    /**
     * @see  NestedSetMapper::rebuild
     */
    public function testRebuild(): void
    {
        $this->instance->update()
            ->set('lft', 0)
            ->set('rgt', 0)
            ->execute();

        $this->instance->rebuild(1);
        $this->instance->rebuild(2);
        $this->instance->rebuild(3);

        $lfts = $this->instance->select('lft')
            ->loadColumn()
            ->dump();

        self::assertEquals(
            [0, 0 ,0 , 1, 1, 2, 3, 4, 5],
            $lfts
        );
    }

    /**
     * @see  NestedSetMapper::rebuildPath
     */
    public function testRebuildPath(): void
    {
        $this->instance->update()
            ->set('path', '')
            ->execute();

        $this->instance->rebuildPath(1);
        $this->instance->rebuildPath(2);
        $this->instance->rebuildPath(3);

        $paths = $this->instance->select('path')
            ->loadColumn()
            ->dump();

        self::assertEquals(
            [
                '',
                '',
                '',
                'flower',
                'sakura',
                'sakura/olive',
                'sunflower',
                'rose',
                'sunflower/orchid',
            ],
            $paths
        );
    }

    public function testDelete(): void
    {
        $newChild = new StubNestedSet();
        $newChild->setTitle('Kapok');
        $newChild->setAlias('kapok');

        $this->instance->putBefore($newChild, 4);

        $this->instance->deleteWhere(8);

        self::assertNull(
            $this->instance->findOne(8)
        );

        // Delete children
        $this->instance->deleteWhere(5);

        self::assertNull($this->instance->findOne(5));
        self::assertNull($this->instance->findOne(6));
    }

    /**
     * @see  NestedSetMapper::getRoot
     */
    public function testGetRoot(): void
    {
        /** @var StubNestedSet $root */
        $root = $this->instance->getRoot();

        self::assertEquals(
            1,
            $root->getId()
        );

        self::assertEquals(
            'root:1',
            $root->getAlias()
        );
    }

    protected function setUp(): void
    {
        /** @var NestedSetMapper $mapper */
        $mapper = self::$orm->mapper(StubMTNestedSet::class);

        $this->instance = $mapper;
    }

    protected function tearDown(): void
    {
    }

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        /** @var NestedSetMapper $mapper */
        $mapper = self::$orm->mapper(StubMTNestedSet::class);

        foreach (range(1, 3) as $i) {
            $mapper->createRoot(
                [
                    'title' => 'root:' . $i,
                    'access' => 1,
                    'alias' => 'root:' . $i
                ]
            );
        }
    }

    /**
     * @inheritDoc
     */
    protected static function setupDatabase(): void
    {
        self::importFromFile(__DIR__ . '/Stub/nested.sql');
    }
}
