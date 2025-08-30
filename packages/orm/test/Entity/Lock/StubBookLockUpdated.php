<?php

declare(strict_types=1);

namespace Windwalker\ORM\Test\Entity\Lock;

use Windwalker\ORM\Attributes\AutoIncrement;
use Windwalker\ORM\Attributes\Cast;
use Windwalker\ORM\Attributes\CastNullable;
use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Attributes\OptimisticLock;
use Windwalker\ORM\Attributes\PK;
use Windwalker\ORM\Attributes\Table;

#[Table('books')]
#[\AllowDynamicProperties]
class StubBookLockUpdated
{
    #[Column('id'), PK, AutoIncrement]
    public ?int $id = null;

    #[Column('title')]
    public string $title = '';

    #[Column('created')]
    #[CastNullable(\DateTimeImmutable::class)]
    public ?\DateTimeImmutable $created = null;

    #[Column('updated')]
    #[CastNullable(\DateTimeImmutable::class)]
    #[OptimisticLock]
    public ?\DateTimeImmutable $updated = null;

    #[Column('version')]
    public int $version = 0;

    #[Column('hash')]
    public string $hash = '';
}
