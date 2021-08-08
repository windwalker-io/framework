<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

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
class CurrentTime extends CastForSave
{
    protected string $time = 'now';

    /**
     * CurrentTime constructor.
     *
     * @param  string  $time
     */
    public function __construct(string $time = 'now', public bool $onlyUpdate = true)
    {
        $this->time = $time;

        parent::__construct(null);
    }

    public function getCurrent(): DateTimeImmutable
    {
        return new DateTimeImmutable($this->time);
    }

    public function filter(mixed $value): DateTimeImmutable
    {
        return $this->getCurrent();
    }

    protected function getDefaultCaster(): callable
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
