<?php

declare(strict_types=1);

namespace Windwalker\ORM\Event;

use Asika\ObjectMetadata\ObjectMetadata;
use Windwalker\ORM\Attributes\ORMAttributeTrait;
use Windwalker\ORM\EntityMapper;

#[\Attribute(\Attribute::TARGET_METHOD)]
class EnergizeEvent extends AbstractEntityEvent
{
    use ORMAttributeTrait;

    protected object $entity;

    public function getEntity(): object
    {
        return $this->entity;
    }

    public function setEntity(object $entity): static
    {
        $this->entity = $entity;

        return $this;
    }

    public function retrieve(string $key): mixed
    {
        return $this->getObjectMetadata()->get($this->getEntity(), $key);
    }

    public function store(string $key, mixed $value): static
    {
        $this->getObjectMetadata()->set($this->getEntity(), $key, $value);

        return $this;
    }

    public function storeCallback(string $key, callable $callback): static
    {
        $callback = fn() => $this->getORM()->getAttributesResolver()->call($callback);

        return $this->store($key, $callback);
    }

    public function forget(string $key): static
    {
        $this->getObjectMetadata()->remove($this->getEntity(), $key);

        return $this;
    }

    public function all(): array
    {
        return $this->getObjectMetadata()->getMetadata($this->getEntity());
    }

    public function getObjectMetadata(): ObjectMetadata
    {
        return EntityMapper::getObjectMetadata();
    }
}
