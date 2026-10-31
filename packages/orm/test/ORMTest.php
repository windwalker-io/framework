<?php

declare(strict_types=1);

namespace Windwalker\ORM\Test;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Test\AbstractDatabaseTestCase;
use Windwalker\ORM\EntityMapper;
use Windwalker\ORM\ORM;
use Windwalker\ORM\SelectorQuery;
use Windwalker\ORM\Test\Entity\StubArticle;
use Windwalker\Data\Collection;
use Windwalker\Query\Query;

/**
 * The ORMTest class.
 */
class ORMTest extends AbstractDatabaseTestCase
{
    protected ?ORM $instance;

    /**
     * @see  ORM::findOne
     */
    #[Test]
    public function findOneReturnsHydratedEntity(): void
    {
        $article = $this->instance->findOne(StubArticle::class, 1);

        self::assertInstanceOf(StubArticle::class, $article);
        self::assertEquals(1, $article->getId());
        self::assertEquals('Corrupti illum.', $article->getTitle());
        self::assertEquals('2009-05-14 17:45:24', $article->getCreated()->format(self::$db->getDateFormat()));
        self::assertTrue($article->getParams()['show_title']);
    }

    public function chainReturn()
    {
        /** @var EntityMapper<StubArticle> $mapper */
        $mapper = $this->instance->mapper(StubArticle::class);

        $item = $mapper->tryEntity();
    }

    /**
     * @see  ORM::__construct
     */
    #[Test]
    public function constructInitializesOrm(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  ORM::getDb
     */
    #[Test]
    public function getDbReturnsAdapter(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  ORM::from
     */
    #[Test]
    public function fromCreatesSelectorQuery(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  ORM::setDb
     */
    #[Test]
    public function setDbReplacesAdapter(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  ORM::conditionsToWheres
     */
    #[Test]
    public function conditionsToWheresBuildsWhereClauses(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  ORM::select
     */
    #[Test]
    public function selectCreatesSelectorQuery(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    #[Test]
    public function queryFactoriesCreateQueriesForTablesOrEntities(): void
    {
        self::assertInstanceOf(SelectorQuery::class, $this->instance->selectRaw('1'));
        self::assertInstanceOf(Query::class, $this->instance->createBaseQuery());
        self::assertInstanceOf(Query::class, $this->instance->insert('articles'));
        self::assertInstanceOf(Query::class, $this->instance->update('articles', 'a'));
        self::assertInstanceOf(Query::class, $this->instance->delete('articles', 'a'));
        self::assertInstanceOf(Query::class, $this->instance->insert(StubArticle::class));
        self::assertInstanceOf(Query::class, $this->instance->update(StubArticle::class));
        self::assertInstanceOf(Query::class, $this->instance->delete(StubArticle::class));
        self::assertInstanceOf(SelectorQuery::class, $this->instance->from(StubArticle::class, 'a'));
    }

    #[Test]
    public function extractEntityAndFieldHandleArraysCollectionsAndObjects(): void
    {
        self::assertSame([], $this->instance->extractEntity(null));
        self::assertSame(['a' => 1], $this->instance->extractEntity(['a' => 1]));
        self::assertSame(['a' => 1], $this->instance->extractEntity(new Collection(['a' => 1])));
        self::assertSame(2, $this->instance->extractField(['b' => 2], 'b'));
        self::assertSame(2, $this->instance->extractField((object) ['b' => 2], 'b'));
        self::assertSame(['a' => 1], $this->instance->extractEntity((object) ['a' => 1]));
    }

    #[Test]
    public function toCollectionWrapsPlainDataAndEntities(): void
    {
        $collection = new Collection(['a' => 1]);

        self::assertSame($collection, $this->instance->toCollection($collection));
        self::assertSame(['a' => 1], $this->instance->toCollection(['a' => 1])->dump());
        self::assertSame(['a' => 1], $this->instance->toCollection((object) ['a' => 1])->dump());

        $article = $this->instance->findOne(StubArticle::class, 1);

        self::assertEquals(1, $this->instance->toCollection($article)['id']);
    }

    #[Test]
    public function tryEntityAndToEntityOrNullReturnNullForNullData(): void
    {
        self::assertNull($this->instance->tryEntity(StubArticle::class, null));
        self::assertInstanceOf(StubArticle::class, $this->instance->tryEntity(StubArticle::class, ['id' => 1]));
        self::assertNull(@$this->instance->toEntityOrNull(StubArticle::class, null));
    }

    #[Test]
    public function entityClassAndMetadataResolveFromClassOrObject(): void
    {
        $article = new StubArticle();

        self::assertSame(StubArticle::class, $this->instance->getEntityClass($article));
        self::assertSame(StubArticle::class, $this->instance->getEntityClass(StubArticle::class));
        self::assertSame(
            $this->instance->getEntityMetadata(StubArticle::class),
            $this->instance->getEntityMetadata($article)
        );
    }

    #[Test]
    public function energizeTogglesEntityState(): void
    {
        $article = new StubArticle();

        self::assertFalse($this->instance->isEnergized($article));

        $this->instance->energize($article);

        self::assertTrue($this->instance->isEnergized($article));

        $this->instance->unenergize($article);

        self::assertFalse($this->instance->isEnergized($article));
    }

    #[Test]
    public function callForwardsToEntityMapperAndRejectsUnknownMethods(): void
    {
        self::assertSame(1, $this->instance->countColumn(StubArticle::class, 'id', ['id' => 1]));
        self::assertTrue($this->instance->isInTransaction() === false);

        $this->expectException(\BadMethodCallException::class);

        $this->instance->noSuchMethod(StubArticle::class);
    }

    #[Test]
    public function writeMethodsAcceptEntityOrClassWithData(): void
    {
        $this->rollbackAfter(function () {
            $article = $this->instance->findOne(StubArticle::class, 1);
            $article->setId(null);
            $article->setTitle('Created by entity');

            $created = $this->instance->createOne($article);

            self::assertNotNull($created->getId());
            self::assertEquals('Created by entity', $created->getTitle());

            $created->setTitle('Updated by entity');
            $this->instance->updateOne($created);

            self::assertEquals(
                'Updated by entity',
                $this->instance->findOne(StubArticle::class, $created->getId())->getTitle()
            );

            $created->setTitle('Saved by entity');
            $this->instance->saveOne($created);

            self::assertEquals(
                'Saved by entity',
                $this->instance->findOne(StubArticle::class, $created->getId())->getTitle()
            );

            $this->instance->updateOne(StubArticle::class, ['id' => $created->getId(), 'title' => 'By class']);

            self::assertEquals(
                'By class',
                $this->instance->findOne(StubArticle::class, $created->getId())->getTitle()
            );

            $this->instance->saveOne(StubArticle::class, ['id' => $created->getId(), 'title' => 'Saved by class']);

            self::assertEquals(
                'Saved by class',
                $this->instance->findOne(StubArticle::class, $created->getId())->getTitle()
            );
        });
    }

    #[Test]
    public function createOneAcceptsClassNameWithData(): void
    {
        $this->rollbackAfter(function () {
            $created = $this->instance->createOne(StubArticle::class, ['category_id' => 1, 'title' => 'By class']);

            self::assertEquals('By class', $created->getTitle());
        });
    }

    #[Test]
    public function copyAndCopyOneAcceptEntityOrConditions(): void
    {
        $this->rollbackAfter(function () {
            $article = $this->instance->findOne(StubArticle::class, 1);

            $copies = $this->instance->copy($article, null, ['title' => 'Copied']);

            self::assertCount(1, $copies);
            self::assertEquals('Copied', $copies[0]->getTitle());

            $copy = $this->instance->copyOne($article, null, ['title' => 'Copied One']);

            self::assertEquals('Copied One', $copy->getTitle());

            $copies = $this->instance->copy(StubArticle::class, ['id' => 1], ['title' => 'By conditions']);

            self::assertEquals('By conditions', $copies[0]->getTitle());

            $copy = $this->instance->copyOne(StubArticle::class, ['id' => 1], ['title' => 'One by conditions']);

            self::assertEquals('One by conditions', $copy->getTitle());
        });
    }

    #[Test]
    public function copyRequiresConditionsWhenGivenClassName(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->instance->copy(StubArticle::class);
    }

    #[Test]
    public function copyOneRequiresConditionsWhenGivenClassName(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->instance->copyOne(StubArticle::class);
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
    protected function createOrmInstance(): void
    {
        $this->instance = new ORM(self::$db);
    }

    /**
     * @inheritDoc
     */
    protected static function setupDatabase(): void
    {
        self::importFromFile(__DIR__ . '/Stub/data.sql');
    }
}
