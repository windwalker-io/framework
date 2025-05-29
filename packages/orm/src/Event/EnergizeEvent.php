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

    public function __construct(
        public object $entity = new \stdClass(),
        array $data = [],
    ) {
        parent::__construct(data: $data);
    }

    public function retrieve(string $key): mixed
    {
        return $this->getObjectMetadata()->get($this->entity, $key);
    }

    public function store(string $key, mixed $value): static
    {
        $this->getObjectMetadata()->set($this->entity, $key, $value);

        return $this;
    }

    public function storeCallback(string $key, callable $callback): static
    {
        $callback = fn() => $this->orm->getAttributesResolver()->call($callback);

        return $this->store($key, $callback);
    }

    public function forget(string $key): static
    {
        $this->getObjectMetadata()->remove($this->entity, $key);

        return $this;
    }

    public function all(): array
    {
        return $this->getObjectMetadata()->getMetadata($this->entity);
    }

    public function getObjectMetadata(): ObjectMetadata
    {
        return EntityMapper::getObjectMetadata();
    }
}
