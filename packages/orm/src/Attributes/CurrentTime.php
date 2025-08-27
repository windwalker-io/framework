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
class CurrentTime implements CastForSaveInterface
{
    /**
     * CurrentTime constructor.
     *
     * @param  string  $time
     * @param  bool    $onlyUpdate
     */
    public function __construct(protected string $time = 'now', public bool $onlyUpdate = true)
    {
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
        return function (mixed $value, ORM $orm, object $entity) {
            $mapper = $orm->mapper($entity::class);

            if ($this->onlyUpdate && $mapper->canCheckIsNew() && $mapper->isNew($entity)) {
                return $value;
            }

            return $this->filter($value);
        };
    }
}
