<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Attribute;
use DateTimeImmutable;
use Windwalker\Filter\FilterInterface;
use Windwalker\ORM\ORM;

/**
 * The CurrentTime class.
 */
#[Attribute]
class CreatedTime implements CastForSaveInterface
{
    /**
     * CurrentTime constructor.
     *
     * @param  string  $time
     * @param  bool    $onlyUpdate
     */
    public function __construct(protected string $time = 'now', public bool $onlyUpdate = true)
    {
        //
    }

    public function getCurrent(): DateTimeImmutable
    {
        return new DateTimeImmutable($this->time);
    }

    public function filter(mixed $value): DateTimeImmutable
    {
        return $this->getCurrent();
    }

    public function getCaster(): \Closure
    {
        return function (mixed $value, ORM $orm, object $entity, bool $isNew = false) {
            $isNull = $value === null || $orm->getDb()->isNullDate($value);

            $mapper = $orm->mapper($entity::class);

            if ($isNull) {
                if ($mapper->getMainKey()) {
                    if ($isNew) {
                        $value = $this->getCurrent();
                    }
                } else {
                    $value = $this->getCurrent();
                }
            }

            return $value;
        };
    }
}
