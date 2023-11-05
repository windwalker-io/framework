<?php

declare(strict_types=1);

namespace Windwalker\ORM\Test\Cast;

use Windwalker\ORM\Cast\JsonCast;
use Windwalker\ORM\Test\AbstractORMTestCase;
use Windwalker\ORM\Test\Entity\StubArticle;
use Windwalker\ORM\Test\Entity\StubArticleParamsArray;
use Windwalker\ORM\Test\Entity\StubArticleParamsObject;

class JsonCastTest extends AbstractORMTestCase
{
    /**
     * @see  JsonCast::hydrate
     */
    public function testHydrate(): void
    {
        $orm = self::$orm;
        /** @var StubArticleParamsArray $article */
        $article = $orm->toEntity(
            StubArticleParamsArray::class,
            [
                'title' => 'Hello',
                'params' => [
                    'foo1' => 'bar2',
                    'foo2' => 'bar2',
                    'foo3' => 'bar3',
                ]
            ]
        );

        self::assertTrue(array_is_list($article->getParams()));
    }

    /**
     * @see  JsonCast::extract
     */
    public function testExtract(): void
    {
        $article = new StubArticle();
        $article->setTitle('Hello');
        $article->setParams([]);

        $data = self::$orm->extractEntity($article);

        self::assertEquals('{}', $data['params']);
    }

    public function testExtractUescape(): void
    {
        $orm = self::$orm;
        /** @var StubArticleParamsArray $article */
        $article = $orm->toEntity(
            StubArticleParamsArray::class,
            [
                'title' => 'Hello',
                'params' => [
                    'foo1' => 'bar2',
                    'foo2' => 'bar2',
                    'foo3' => '中文測試',
                ]
            ]
        );

        $data = $orm->extractEntity($article);

        self::assertEquals(
            '["bar2","bar2","中文測試"]',
            $data['params'],
            'UTF-8 Should not escaped'
        );
    }

    protected static function setupDatabase(): void
    {
        self::importFromFile(__DIR__ . '/../Stub/data.sql');
    }
}
