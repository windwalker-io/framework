<?php

declare(strict_types=1);

namespace Windwalker\ORM\Test;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\Data\Collection;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\ORM\Attributes\Watch;
use Windwalker\ORM\EntityMapper;
use Windwalker\ORM\Event\WatchEvent;
use Windwalker\ORM\Exception\OptimisticLockException;
use Windwalker\ORM\ORMOptions;
use Windwalker\ORM\Test\Entity\Lock\StubBookLockHash;
use Windwalker\ORM\Test\Entity\Lock\StubBookLockUpdated;
use Windwalker\ORM\Test\Entity\Lock\StubBookLockVersion;
use Windwalker\ORM\Test\Entity\StubArticle;
use Windwalker\ORM\Test\Entity\StubComment;
use Windwalker\ORM\Test\Entity\StubFlower;
use Windwalker\Utilities\Arr;

use function PHPUnit\Framework\assertEquals;
use function Windwalker\raw;

/**
 * Test class of DataMapper
 *
 * @since 2.0
 */
class EntityMapperTest extends AbstractORMTestCase
{
    /**
     * Test instance.
     *
     * @var  EntityMapper
     */
    protected EntityMapper $instance;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return  void
     * @throws  Exception
     */
    #[Before]
    protected function createMapperInstance(): void
    {
        $this->instance = self::$orm->mapper(StubFlower::class);
    }

    /**
     * Method to test find().
     *
     * @return  void
     *
     * @throws  Exception
     */
    #[Test]
    public function findReturnsMatchingEntities()
    {
        $dataset = $this->instance->select()
            ->limit(3)
            ->offset(0)
            ->all();

        self::assertEquals([1, 2, 3], $dataset->column('id')->dump());
        self::assertEquals(['Alstroemeria', 'Amaryllis', 'Anemone'], $dataset->column('title')->dump());

        $dataset = $this->instance->select()
            ->where('id', null)
            ->limit(3)
            ->all();

        self::assertEquals([], $dataset->column('id')->dump());

        $dataset = $this->instance->select()
            ->where('id', 0)
            ->limit(3)
            ->all();

        self::assertEquals([], $dataset->column('id')->dump());

        $dataset = $this->instance->select()
            ->where(['state' => 1])
            ->order('ordering', 'DESC')
            ->limit(3)
            ->offset(2)
            ->all();

        self::assertEquals([1, 1, 1], $dataset->column('state')->dump());
        self::assertEquals([82, 79, 77], $dataset->column('ordering')->dump());
        self::assertEquals(['Violet', 'red', 'pink'], $dataset->column('title')->dump());

        $dataset = $this->instance
            ->select('id', 'state')
            ->where(['state' => 1])
            ->order('ordering', 'DESC')
            ->offset(2)
            ->limit(3)
            ->all();

        self::assertEquals([], $dataset->column('catid')->dump());

        // Test find with no conditions
        $dataset = $this->instance->select()->all();

        self::assertFalse($dataset->isNull());

        $dataset = $this->instance->findOne(null);

        self::assertNull($dataset);
    }

    /**
     * Method to test findOne().
     *
     * @return  void
     */
    #[Test]
    public function findOneReturnsSingleEntity()
    {
        // Find by primary key
        /** @var StubFlower $data */
        $data = $this->instance->findOne(7);

        self::assertInstanceOf(
            StubFlower::class,
            $data
        );
        self::assertEquals('Baby\'s Breath', $data->getTitle());

        // Find by conditions
        $data = $this->instance->findOne(['title' => 'Cosmos']);

        self::assertEquals('peaceful', $data->getMeaning());

        $data = $this->instance->findOne(['title' => 'Freesia', 'state' => 1]);

        self::assertNull($data);

        // Test camel case conversion
        $categoryId = 2;
        $item = self::$orm->mustFindOne(StubArticle::class, compact('categoryId'));

        assertEquals(1, $item->getId());
        assertEquals(2, $item->getCategoryId());
    }

    /**
     * testFindColumn
     *
     * @return  void
     */
    #[Test]
    public function findColumnReturnsColumnValues()
    {
        $columns = $this->instance->select('id')
            ->order(['catid', 'ordering'])
            ->limit(3)
            ->loadColumn();

        self::assertEquals([3, 4, 7], $columns->dump());
    }

    /**
     * Method to test create().
     *
     * @return  void
     */
    #[Test]
    public function createMultipleStoresAllEntities()
    {
        // Create from array
        $items = [
            ['title' => 'Sakura', 'meaning' => '', 'params' => ''],
            ['title' => 'Peony', 'meaning' => '', 'params' => ''],

            // DataMapper should remove non-necessary field
            ['title' => 'Sunflower', 'anim' => 'bird', 'meaning' => '', 'params' => ''],
        ];

        /** @var StubFlower[] $returns */
        $returns = $this->instance->createMultiple($items);

        $newItems = self::$db->prepare(
            'SELECT * FROM ww_flower ORDER BY id DESC LIMIT 3'
        )
            ->all();

        self::assertEquals(['Sunflower', 'Peony', 'Sakura'], $newItems->column('title')->dump());

        self::assertEquals(86, $returns[0]->id, 'Inserted id not matched.');

        self::assertInstanceOf(StubFlower::class, $returns[0]);

        // Create from Collection
        $items = new Collection(
            [
                new Collection(['title' => 'Sakura2', 'meaning' => '', 'params' => '']),
                new Collection(['title' => 'Peony2', 'meaning' => '', 'params' => '']),
                new Collection(['title' => 'Sunflower2', 'meaning' => '', 'params' => '']),
            ]
        );

        $returns = $this->instance->createMultiple($items);

        $newItems = static::$db->prepare('SELECT * FROM ww_flower ORDER BY id DESC LIMIT 3')->all();

        self::assertEquals(['Sunflower2', 'Peony2', 'Sakura2'], $newItems->column('title')->dump());

        self::assertEquals(89, $returns[0]->id, 'Inserted id not matched.');

        self::assertInstanceOf(StubFlower::class, $returns[0]);
    }

    /**
     * Method to test createOne().
     *
     * @return  void
     */
    #[Test]
    public function createOneStoresEntityAndReturnsIt()
    {
        // Create from array
        $data = [
            'title' => 'Foo flower',
            'state' => 1,
            'meaning' => '',
            'params' => '',
        ];

        $newData = $this->instance->createOne($data);

        self::assertEquals(92, $newData->id);
        self::assertEquals(
            92,
            static::$db->prepare('SELECT * FROM ww_flower ORDER BY id DESC LIMIT 1')->get()->id
        );

        self::assertInstanceOf(StubFlower::class, $newData);

        // Create from Collection
        $data = new Collection(
            [
                'title' => 'Foo flower',
                'state' => 1,
                'meaning' => '',
                'params' => '',
            ]
        );

        $newData = $this->instance->createOne($data);

        self::assertEquals(93, $newData->id);
        self::assertEquals(93, static::$db->prepare('SELECT * FROM ww_flower ORDER BY id DESC LIMIT 1')->get()->id);

        self::assertInstanceOf(StubFlower::class, $newData);
    }

    /**
     * Method to test update().
     *
     * @return  void
     */
    #[Test]
    public function updateAppliesDataToAllMatchedEntities()
    {
        // Update from array
        $items = [
            ['id' => 1, 'state' => 1],
            ['id' => 2, 'state' => 1],
            ['id' => 3, 'state' => 1],
        ];

        $returns = $this->instance->updateMultiple($items, 'id');

        $updated = static::$db->prepare('SELECT * FROM ww_flower LIMIT 3')->all();

        self::assertEquals([1, 1, 1], $updated->column('state')->dump());

        self::assertInstanceOf(StatementInterface::class, $returns[0]);

        // Use from DataSet
        $items = new Collection(
            [
                new Collection(['id' => 1, 'state' => 0]),
                new Collection(['id' => 2, 'state' => 0]),
                new Collection(['id' => 3, 'state' => 0]),
            ]
        );

        $returns = $this->instance->updateMultiple($items, 'id');

        $updated = static::$db->prepare('SELECT * FROM ww_flower LIMIT 3')->all();

        self::assertEquals([0, 0, 0], $updated->column('state')->dump());

        self::assertInstanceOf(StatementInterface::class, $returns[0]);
        // TODO: Test Update Nulls
    }

    /**
     * Method to test updateOne().
     *
     * @return  void
     */
    #[Test]
    public function updateOneModifiesSingleEntity()
    {
        // Update from array
        $data = ['id' => 10, 'params' => '{}'];

        $this->instance->updateOne($data);

        self::assertEquals(
            '{}',
            static::$db->prepare('SELECT * FROM ww_flower WHERE id = 10 LIMIT 1')
                ->get()
                ->params
        );

        // Update from Data
        $data = new Collection(['id' => 11, 'params' => '{}']);

        $updateData = $this->instance->updateOne($data);

        self::assertEquals(
            '{}',
            static::$db->prepare('SELECT * FROM ww_flower WHERE id = 11 LIMIT 1')
                ->get()
                ->params
        );
        // TODO: Test Update Nulls
    }

    #[Test]
    public function updateRefreshesCurrentTimeColumns()
    {
        $mapper = static::$orm->mapper(StubComment::class);

        /** @var StubComment $comment */
        /** @var StubComment $comment2 */
        $comment = $mapper->findOne(10);
        $expected = clone $comment;

        $mapper->updateOne($comment);

        $comment2 = $mapper->findOne(10);

        self::assertNotEquals(
            $expected->getCreated()?->getTimestamp(),
            $comment2->getCreated()?->getTimestamp()
        );

        self::assertNotEquals(
            self::$db->getNullDate(),
            $comment2->getCreated()->format(self::$db->getDateFormat())
        );
    }

    #[Test]
    public function updateOptimisticLockRejectsStaleVersion(): void
    {
        $mapper = static::$orm->mapper(StubBookLockUpdated::class);

        $item = $mapper->mustFindOne(1);

        $item->title = 'Book 1 updated';

        $mapper->updateOne($item);

        self::assertNotEquals(
            '2024-01-01 00:00:00',
            $mapper->select('updated')->where('id', 1),
        );

        self::assertNotEquals(
            '2024-01-01T00:00:00+00:00',
            $item->updated->format(\DateTime::ATOM),
        );

        // Test version
        $mapper = static::$orm->mapper(StubBookLockVersion::class);

        $item = $mapper->mustFindOne(2);

        $item->title = 'Book 2 updated';

        $mapper->updateOne($item);

        self::assertEquals(
            2,
            $item->version,
        );

        self::assertEquals(
            (int) $mapper->select('version')->where('id', 2)->result(),
            $item->version,
        );

        // Test hash
        $mapper = static::$orm->mapper(StubBookLockHash::class);

        $item = $mapper->mustFindOne(3);

        $item->title = 'Book 3 updated';

        $mapper->updateOne($item);

        self::assertNotEquals(
            'hash3',
            $item->hash,
        );

        self::assertEquals(
            $mapper->select('hash')->where('id', 3)->result(),
            $item->hash,
        );

        // Should error
        $this->expectException(OptimisticLockException::class);

        $mapper = static::$orm->mapper(StubBookLockUpdated::class);

        $item = $mapper->mustFindOne(4);

        $item->title = 'Book 4 updated';

        $mapper->update(StubBookLockUpdated::class)
            ->set('updated', raw('CURRENT_TIMESTAMP()'))
            ->where('id', 4)
            ->execute();

        $mapper->updateOne($item);

        self::assertNotEquals(
            '2024-01-04T00:00:00+00:00',
            $item->updated->format(\DateTime::ATOM),
        );
    }

    #[Test]
    public function pushNextVersionIncrementsVersionColumn()
    {
        $mapper = static::$orm->mapper(StubBookLockUpdated::class);

        $item = $mapper->mustFindOne(5);

        $item = $mapper->pushNextVersion($item);

        self::assertNotEquals(
            '2024-01-05T00:00:00+00:00',
            $item->updated->format(\DateTime::ATOM),
        );

        $mapper = static::$orm->mapper(StubBookLockHash::class);

        $item = $mapper->mustFindOne(5);

        $item = $mapper->pushNextVersion($item);

        self::assertNotEquals(
            'hash5',
            $item->hash,
        );

        $mapper = static::$orm->mapper(StubBookLockVersion::class);

        $item = $mapper->mustFindOne(6);

        $item = $mapper->pushNextVersion($item);

        self::assertEquals(
            2,
            $item->version
        );
    }

    /**
     * Method to test updateAll().
     *
     * @return  void
     */
    #[Test]
    public function updateWhereUpdatesOnlyMatchingRows()
    {
        $data = ['state' => 0];

        $this->instance->updateWhere($data, ['id' => [4, 5, 6]]);

        $dataset = static::$db->prepare('SELECT * FROM ww_flower WHERE id IN(4, 5, 6)')->all();

        self::assertEquals([0, 0, 0], $dataset->column('state')->dump());
    }

    #[Test]
    public function updateBatchUpdatesAllEntities()
    {
        $data = ['state' => 2];

        $this->instance->updateWhere($data, ['id' => [4, 5, 6]]);

        $dataset = static::$db->prepare('SELECT * FROM ww_flower WHERE id IN(4, 5, 6)')->all();

        self::assertEquals([2, 2, 2], $dataset->column('state')->dump());
    }

    /**
     * Method to test flush().
     *
     * @return  void
     */
    #[Test]
    public function flushReplacesTableContents()
    {
        // Prepare test data
        static::$db->execute('UPDATE ww_flower SET catid = 3 WHERE id IN (6, 7, 8)');

        $dataset = [
            ['title' => 'Baby\'s Breath2', 'catid' => 3, 'meaning' => '', 'params' => ''],
            ['title' => 'Bachelor Button2', 'catid' => 3, 'meaning' => '', 'params' => ''],
            ['title' => 'Begonia2', 'catid' => 3, 'meaning' => '', 'params' => ''],
        ];

        // Delete all catid = 3 and re insert them.
        $returns = $this->instance->flush($dataset, ['catid' => 3]);

        $newDataset = static::$db->prepare('SELECT * FROM ww_flower WHERE catid = 3')->all();

        self::assertEquals(['Baby\'s Breath2', 'Bachelor Button2', 'Begonia2'], $newDataset->column('title')->dump());
        self::assertEquals([94, 95, 96], $newDataset->column('id')->dump());
    }

    /**
     * Method to test save().
     *
     * @return  void
     */
    #[Test]
    public function saveMultipleCreatesOrUpdatesEntities()
    {
        $items = [
            ['title' => 'Sunflower', 'catid' => 5, 'meaning' => '', 'params' => ''],
            ['id' => 15, 'title' => 'striped2', 'catid' => 5, 'meaning' => '', 'params' => ''],
        ];

        $returns = $this->instance->saveMultiple($items, 'id');

        $returns = new Collection($returns);

        $newDataset = static::$db->prepare('SELECT * FROM ww_flower WHERE catid = 5')->all();

        self::assertEquals(97, $returns[0]->id, 'Should return insert ID');
        self::assertEquals([97, 15], $returns->column('id')->dump(), 'Inserted ID not matched');
        self::assertEquals([5, 5], $newDataset->column('catid')->dump(), 'New catid should be 5');
    }

    /**
     * Method to test saveOne().
     *
     * @return  void
     */
    #[Test]
    public function saveOneCreatesOrUpdatesEntity()
    {
        $data = ['title' => 'Sakura', 'catid' => 6, 'meaning' => '', 'params' => ''];

        $return = $this->instance->saveOne($data, 'id');

        self::assertEquals('Sakura', static::$db->prepare('SELECT title FROM ww_flower WHERE catid = 6')->result());
        self::assertEquals(98, $return->id);

        $data = ['id' => 15, 'title' => 'striped3', 'catid' => 6, 'meaning' => '', 'params' => ''];

        $return = $this->instance->saveOne($data, 'id');

        self::assertEquals('striped3', static::$db->prepare('SELECT title FROM ww_flower WHERE id = 15')->result());
        self::assertEquals(15, $return->id);
    }

    /**
     * Method to test upsert().
     *
     * @return  void
     */
    #[Test]
    public function upsertInsertsOrUpdatesOnDuplicateKey(): void
    {
        // Update case - matched by existing PK, row should be updated in place.
        /** @var StubFlower $flower */
        $flower = $this->instance->upsert(
            [
                'id' => 20,
                'catid' => 1,
                'title' => 'White Renewed',
                'meaning' => 'renewed',
                'ordering' => 20,
                'state' => 1,
                'params' => '',
            ]
        );

        self::assertInstanceOf(StubFlower::class, $flower);
        self::assertEquals(20, $flower->id);
        self::assertEquals('White Renewed', $flower->getTitle());

        $row = static::$db->prepare('SELECT * FROM ww_flower WHERE id = 20')->get();

        self::assertEquals('White Renewed', $row->title);
        self::assertEquals('renewed', $row->meaning);

        // Insert case - no matching PK, a new row should be created and
        // the auto-increment ID should be returned back into the entity.
        /** @var StubFlower $newFlower */
        $newFlower = $this->instance->upsert(
            [
                'catid' => 2,
                'title' => 'Upsert Flower',
                'meaning' => 'brand new',
                'ordering' => 999,
                'state' => 1,
                'params' => '',
            ]
        );

        self::assertInstanceOf(StubFlower::class, $newFlower);
        self::assertNotEmpty($newFlower->id);

        $row = static::$db->prepare('SELECT * FROM ww_flower WHERE title = \'Upsert Flower\'')->get();

        self::assertEquals($newFlower->id, $row->id);
        self::assertEquals('brand new', $row->meaning);
    }

    /**
     * Method to test delete().
     *
     * @return  void
     */
    #[Test]
    public function deleteRemovesMatchingRows()
    {
        $this->instance->deleteWhere(['id' => 16]);

        self::assertNull(static::$db->prepare('SELECT * FROM ww_flower WHERE id = 16')->get());

        $flower = new StubFlower();
        $flower->id = 17;
        $this->instance->deleteWhere($flower);

        self::assertNull(static::$db->prepare('SELECT * FROM ww_flower WHERE id = 17')->get());
    }

    #[Test]
    public function syncMakesTableMatchGivenDataset()
    {
        $comments = static::$orm->from(StubComment::class)
            ->where('type', 'article')
            ->where('user_id', 1)
            ->where('target_id', 1)
            ->all();

        unset($comments[1]);

        $comments[0]->user_id = 10;

        $comment = new StubComment();
        $comment->setType('article');
        $comment->setUserId(1);
        $comment->setTargetId(1);
        $comment->setContent('A');

        $comments[] = $comment;

        $comment = new StubComment();
        $comment->setType('article');
        $comment->setUserId(1);
        $comment->setTargetId(1);
        $comment->setContent('B');

        $comments[] = $comment;

        $comments = $comments->values();

        $syncResult = self::$orm->mapper(StubComment::class)
            ->sync(
                $comments,
                [
                    'type' => 'article',
                    'user_id' => 1,
                    'target_id' => 1,
                ],
                ['id']
            );

        $syncResult = Arr::collapse($syncResult);

        self::assertContainsOnlyInstancesOf(
            StubComment::class,
            $syncResult
        );

        $newComments = static::$orm->from(StubComment::class)
            ->where('type', 'article')
            ->where('user_id', 1)
            ->where('target_id', 1)
            ->all();

        self::assertEquals(
            [1, 3, 46, 47],
            $newComments->column('id')->dump()
        );
    }

    #[Test]
    public function findOneOrCreateCreatesWhenMissing()
    {
        /** @var StubFlower $flower */
        $flower = $this->instance->findOneOrCreate(
            ['title' => 'Fire Flower'],
            [
                'catid' => 1,
                'meaning' => 'Hot',
                'ordering' => 123,
                'state' => 1,
            ]
        );

        self::assertInstanceOf(StubFlower::class, $flower);
        self::assertEquals(100, $flower->id);
        self::assertEquals('Fire Flower', $flower->getTitle());
        self::assertEquals('Hot', $flower->getMeaning());

        /** @var StubFlower $flower */
        $flower = $this->instance->findOneOrCreate(
            ['title' => 'Ice Flower'],
            fn(array $item) => $item += [
                'catid' => 1,
                'meaning' => 'Cold',
                'ordering' => 124,
                'state' => 1,
            ]
        );

        self::assertInstanceOf(StubFlower::class, $flower);
        self::assertEquals(101, $flower->id);
        self::assertEquals('Ice Flower', $flower->getTitle());
        self::assertEquals('Cold', $flower->getMeaning());
    }

    #[Test]
    public function updateOneOrCreateCreatesWhenMissing()
    {
        /** @var StubFlower $flower */
        $flower = $this->instance->updateOneOrCreate(
            ['title' => 'Stone Flower', 'meaning' => 'Strong'],
            [
                'catid' => 1,
                'ordering' => 125,
                'state' => 1,
            ],
            ['meaning'],
        );

        self::assertInstanceOf(StubFlower::class, $flower);
        self::assertEquals(102, $flower->id);
        self::assertEquals('Stone Flower', $flower->getTitle());
        self::assertEquals('Strong', $flower->getMeaning());

        /** @var StubFlower $flower */
        $flower = $this->instance->updateOneOrCreate(
            ['title' => 'Lisianthus', 'meaning' => 'calming2'],
            [
                'catid' => 1,
                'ordering' => 2,
                'state' => 1,
            ],
            ['title'],
        );

        self::assertInstanceOf(StubFlower::class, $flower);
        self::assertEquals(50, $flower->id);
        self::assertEquals('Lisianthus', $flower->getTitle());
        self::assertEquals('calming2', $flower->getMeaning());

        $flower = $this->instance->select()->where('id', 50)
            ->get(StubFlower::class);

        self::assertEquals(50, $flower->id);
        self::assertEquals('Lisianthus', $flower->getTitle());
        self::assertEquals('calming2', $flower->getMeaning());
    }

    #[Test]
    public function incrementAddsToColumnValue(): void
    {
        $mapper = static::$orm->mapper(StubArticle::class);

        $mapper->increment('hits', 1);

        $hits = $mapper->findOne(1)->getHits();

        self::assertEquals(2, $hits);

        $mapper->increment('hits', 2, 3, new ORMOptions(ignoreEvents: true, transaction: true));

        $hits = $mapper->findOne(2)->getHits();

        self::assertEquals(4, $hits);
    }

    #[Test]
    public function incrementOrCreateCreatesWhenMissing(): void
    {
        $mapper = static::$orm->mapper(StubArticle::class);

        $exists = $mapper->findOne(['content' => 'YOO']);

        self::assertNull($exists);

        $mapper->incrementOrCreate('hits', ['content' => 'YOO'], initData: ['hits' => 10]);

        $hits = $mapper->mustFindOne(['content' => 'YOO'])->getHits();

        self::assertEquals(11, $hits);
    }

    #[Test]
    public function decrementSubtractsFromColumnValue(): void
    {
        $mapper = static::$orm->mapper(StubArticle::class);

        $mapper->decrement('hits', 1);

        $hits = $mapper->findOne(1)->getHits();

        self::assertEquals(1, $hits);

        $mapper->decrement('hits', 2, 3, new ORMOptions(ignoreEvents: true, transaction: true));

        $hits = $mapper->findOne(2)->getHits();

        self::assertEquals(1, $hits);
    }

    #[Test]
    public function decrementOrCreateCreatesWhenMissing(): void
    {
        $mapper = static::$orm->mapper(StubArticle::class);

        $exists = $mapper->findOne(['content' => 'GOO']);

        self::assertNull($exists);

        $mapper->decrementOrCreate('hits', ['content' => 'GOO'], initData: ['hits' => 10]);

        $hits = $mapper->mustFindOne(['content' => 'GOO'])->getHits();

        self::assertEquals(9, $hits);
    }

    /**
     * Don't change ordering of this method to keep the inserted IDs correct.
     *
     * @return  void
     *
     * @throws  \ReflectionException
     */
    #[Test]
    public function createBulkInsertsAllEntitiesInOneQuery(): void
    {
        // Create from array
        $items = [
            ['title' => 'Daisy', 'meaning' => '', 'params' => ''],
            ['title' => 'Tulip', 'meaning' => '', 'params' => ''],
            // Mapper should remove non-necessary field
            ['title' => 'Orchid', 'anim' => 'bird', 'meaning' => '', 'params' => ''],
        ];

        /** @var StubFlower[] $returns */
        $returns = $this->instance->createBulk($items);

        $sql = $this->instance->getDb()->getLastQuery()->render(true);

        self::assertStringContainsString('Daisy', $sql);
        self::assertStringContainsString('Tulip', $sql);
        self::assertStringContainsString('Orchid', $sql);

        $newItems = self::$db->prepare(
            'SELECT * FROM ww_flower ORDER BY id DESC LIMIT 3'
        )
            ->all();

        self::assertEquals(['Orchid', 'Tulip', 'Daisy'], $newItems->column('title')->dump());

        self::assertInstanceOf(StubFlower::class, $returns[0]);
    }

    /**
     * Method to test getPrimaryKey().
     *
     * @return  void
     *
     * @throws  Exception
     */
    #[Test]
    public function getMainKeyReturnsPrimaryKeyName()
    {
        self::assertEquals('id', $this->instance->getMainKey());
    }

    /**
     * Method to test getTable().
     *
     * @return  void
     */
    #[Test]
    public function getTableNameReturnsEntityTable()
    {
        self::assertEquals('ww_flower', $this->instance->getTableName());
    }

    #[Test]
    public function findResultReturnsSingleValue(): void
    {
        self::assertEquals('Alstroemeria', $this->instance->findResult('title', ['id' => 1]));
    }

    #[Test]
    public function findResultAppliesLockOptions(): void
    {
        $result = $this->instance->findResult('title', ['id' => 1], new ORMOptions(forUpdate: true));

        self::assertEquals('Alstroemeria', $result);
    }

    #[Test]
    public function findColumnAppliesLockOptions(): void
    {
        $result = $this->instance->findColumn('id', ['id' => 1], new ORMOptions(forShare: true));

        self::assertEquals([1], $result->dump());
    }

    #[Test]
    public function countColumnCountsMatchingRows(): void
    {
        self::assertSame(3, $this->instance->countColumn('id', ['id' => [1, 2, 3]]));
    }

    #[Test]
    public function countColumnSupportsGroups(): void
    {
        self::assertGreaterThan(0, $this->instance->countColumn('id', [], 'catid'));
    }

    #[Test]
    public function sumColumnSumsMatchingRows(): void
    {
        self::assertSame(6.0, $this->instance->sumColumn('id', ['id' => [1, 2, 3]]));
    }

    #[Test]
    public function sumColumnSupportsGroups(): void
    {
        self::assertGreaterThan(0.0, $this->instance->sumColumn('id', [], 'catid'));
    }

    #[Test]
    public function copyDuplicatesRowsWithNewValues(): void
    {
        $this->rollbackAfter(function () {
            $copies = $this->instance->copy(['id' => [1, 2]], ['title' => 'Copied']);

            self::assertCount(2, $copies);
            self::assertEquals('Copied', $copies[0]->getTitle());
            self::assertNotEquals(1, $copies[0]->getId());
            self::assertNotEquals($copies[0]->getId(), $copies[1]->getId());
            self::assertEquals('aspiring', $copies[0]->getMeaning());
            self::assertEquals('dramatic', $copies[1]->getMeaning());
        });
    }

    #[Test]
    public function copyAcceptsCallableToModifyData(): void
    {
        $this->rollbackAfter(function () {
            $copies = $this->instance->copy(
                ['id' => 1],
                function (array $data, array $oldData) {
                    self::assertEquals(1, $oldData['id']);
                    self::assertArrayNotHasKey('id', $data);

                    $data['title'] = 'Callable';

                    return $data;
                }
            );

            self::assertEquals('Callable', $copies[0]->getTitle());
        });
    }

    #[Test]
    public function copyOneAcceptsEntityAndReturnsNullWhenNothingMatches(): void
    {
        $this->rollbackAfter(function () {
            $entity = $this->instance->findOne(1);
            $copy = $this->instance->copyOne($entity, ['title' => 'One']);

            self::assertEquals('One', $copy->getTitle());
            self::assertNotEquals(1, $copy->getId());

            self::assertNull($this->instance->copyOne(['id' => 99999]));
        });
    }

    #[Test]
    public function deleteBulkAcceptsEntityAsConditions(): void
    {
        $this->rollbackAfter(function () {
            $entity = $this->instance->findOne(1);

            $this->instance->deleteBulk($entity);

            self::assertNull($this->instance->findOne(1));
            self::assertNotNull($this->instance->findOne(2));
        });
    }

    #[Test]
    public function deleteOneRejectsEntityWithEmptyKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('keys value contains NULL');

        $this->instance->deleteOne(new StubFlower());
    }

    #[Test]
    public function watchBeforeRunsHandlerOnlyWhenColumnChanges(): void
    {
        $this->rollbackAfter(function () {
            $seen = [];
            $unwatch = $this->instance->getMetadata()->watchBefore(
                'title',
                function (WatchEvent $event) use (&$seen) {
                    $seen[] = [$event->value, $event->oldValue];
                }
            );

            try {
                $this->instance->updateOne(['id' => 1, 'title' => 'Alstroemeria'], null, new ORMOptions());
                self::assertSame([], $seen);

                $this->instance->updateOne(['id' => 1, 'title' => 'Changed']);
                self::assertSame([['Changed', 'Alstroemeria']], $seen);
            } finally {
                $unwatch();
            }

            $this->instance->updateOne(['id' => 1, 'title' => 'After unwatch']);
            self::assertCount(1, $seen);
        });
    }

    #[Test]
    public function watchAfterAndUpdateWhereHandlersAreInvoked(): void
    {
        $this->rollbackAfter(function () {
            $afterSave = [];
            $afterWhere = [];
            $metadata = $this->instance->getMetadata();

            $unwatchSave = $metadata->watchAfter(
                'title',
                function (WatchEvent $event) use (&$afterSave) {
                    $afterSave[] = $event->value;
                }
            );
            $unwatchWhere = $metadata->watchAfter(
                'title',
                function (WatchEvent $event) use (&$afterWhere) {
                    $afterWhere[] = $event->value;
                },
                Watch::INCLUDE_UPDATE_WHERE
            );
            $unwatchBeforeWhere = $metadata->watchBefore(
                'title',
                function (WatchEvent $event) use (&$afterWhere) {
                    $afterWhere[] = 'before:' . $event->value;
                },
                Watch::INCLUDE_UPDATE_WHERE
            );

            try {
                $this->instance->updateOne(['id' => 2, 'title' => 'Saved']);
                $this->instance->updateBulk(['title' => 'Bulk'], ['id' => 3]);
            } finally {
                $unwatchSave();
                $unwatchWhere();
                $unwatchBeforeWhere();
            }

            self::assertContains('Saved', $afterSave);
            self::assertContains('Bulk', $afterWhere);
            self::assertContains('before:Bulk', $afterWhere);
        });
    }

    #[Test]
    public function tryEntityReturnsNullForNullAndEntityForArray(): void
    {
        self::assertNull($this->instance->tryEntity(null));
        self::assertNull($this->instance->toEntityOrNull(null));

        $entity = $this->instance->tryEntity(['id' => 5, 'title' => 'Foo']);

        self::assertInstanceOf(StubFlower::class, $entity);
        self::assertEquals('Foo', $entity->getTitle());
    }

    #[Test]
    public function toCollectionHandlesArraysEntitiesAndPlainObjects(): void
    {
        self::assertEquals(['a' => 1], $this->instance->toCollection(['a' => 1])->dump());
        self::assertEquals(['a' => 1], $this->instance->toCollection((object) ['a' => 1])->dump());

        $entity = $this->instance->toEntity(['id' => 5, 'title' => 'Foo']);
        $collection = $this->instance->toCollection($entity);

        self::assertEquals(5, $collection['id']);
        self::assertEquals('Foo', $collection['title']);
    }

    #[Test]
    public function findOneAcceptsScalarPrimaryKeyCondition(): void
    {
        $entity = $this->instance->findOne(1);

        self::assertEquals('Alstroemeria', $entity->getTitle());
    }

    #[Test]
    public function emitEventSkipsListenersWhenIgnoreEventsIsSet(): void
    {
        $called = false;
        $this->instance->on('foo', function () use (&$called) {
            $called = true;
        });

        $event = $this->instance->emitEvent('foo', ['options' => new ORMOptions(ignoreEvents: true)]);

        self::assertFalse($called);
        self::assertEquals('foo', $event->getName());
    }

    #[Test]
    public function queryFactoriesBuildStatementsForEntityTable(): void
    {
        self::assertStringContainsString('SELECT', (string) $this->instance->select());
        self::assertStringContainsString('INSERT', (string) $this->instance->insert());
        self::assertStringContainsString('UPDATE', (string) $this->instance->update());
        self::assertStringContainsString('DELETE', (string) $this->instance->delete());
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

    /**
     * @inheritDoc
     */
    protected static function setupDatabase(): void
    {
        self::importFromFile(__DIR__ . '/Stub/data.sql');
        self::importFromFile(__DIR__ . '/Stub/locks.sql');
    }
}
