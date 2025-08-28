<?php

declare(strict_types=1);

use Windwalker\Data\Collection;
use Windwalker\ORM\DataMapper;
use Windwalker\ORM\ORM;
use Windwalker\ORM\Test\Entity\StubArticle;
use Windwalker\ORM\Test\Entity\StubUser;

$orm = new ORM($db);

/** @var \Windwalker\ORM\EntityMapper<StubUser> $em */
$em = $orm->mapper(StubUser::class);
$user = $em->createEntity();

/** @psalm-var  DataMapper<StubUser> $dm */
$dm = (new DataMapper(Collection::class));
$u = $dm->se
$u = $dm->create(StubUser::class);

/** @var \Windwalker\ORM\EntityMapper<\Windwalker\ORM\Test\Entity\StubSakura> $mapper */
$mapper = $orm->mapper(
    \Windwalker\ORM\Test\Entity\StubSakura::class
);

$mapper->mustFindOne([])->mapAs();
