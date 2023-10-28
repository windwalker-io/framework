<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

use Windwalker\Database\DatabaseFactory;
use Windwalker\ORM\ORM;
use Windwalker\ORM\Test\Entity\StubArticle;

include __DIR__ . '/../../../vendor/autoload.php';

$orm = new ORM(
    (new DatabaseFactory())->create('pdo_mysql', [])
);

$meta = $orm->getEntityMetadata(StubArticle::class);

const NUM_TESTS = 100000;

$start = microtime(true);

for ($i = 0; $i < NUM_TESTS; $i++) {
    // \Windwalker\Utilities\Reflection\ReflectAccessor::reflect(Article::class);
    // \Windwalker\ORM\Metadata\EntityMetadata::isEntity(Article::class);
    $meta->getProperties();
    // iterator_to_array($meta->getMethodsOfAttribute(AfterSaveEvent::class));
    // $meta->getPropertiesOfAttribute(Column::class);
}

$end = microtime(true);

echo "Time: " . number_format($end - $start, 5);
