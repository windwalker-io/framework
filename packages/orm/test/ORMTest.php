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
