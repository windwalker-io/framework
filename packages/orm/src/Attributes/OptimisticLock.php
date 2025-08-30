<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Windwalker\Attributes\AttributeHandler;
use Windwalker\Attributes\AttributeInterface;
use Windwalker\ORM\Cast\CasterInfo;
use Windwalker\ORM\Metadata\EntityMember;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\ORM\ORM;

use function Windwalker\uid;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class OptimisticLock implements AttributeInterface, OptimisticLockInterface, CastForSaveInterface
{
    use ORMAttributeTrait;

    public EntityMember $member;

    /**
     * @var callable|null
     */
    protected mixed $nextHandler;

    public function __construct(?callable $nextHandler = null)
    {
        $this->nextHandler = $nextHandler;
    }

    protected function handle(EntityMetadata $metadata, AttributeHandler $handler): callable
    {
        /** @var \ReflectionProperty $prop */
        $prop = $handler->getReflector();

        $metadata->addAttributeMap($this, $prop);

        $this->member = $metadata->getPropertyMember($prop->getName());

        return $handler->get();
    }

    public function getCaster(): \Closure
    {
        return function (CasterInfo $info, ORM $orm) {
            $mapper = $orm->mapper($info->entity::class);

            if ($mapper->canCheckIsNew() && $mapper->isNew($info->entity)) {
                return $info->value;
            }

            return $this->pushValueToNextValue($info->property, $info->value);
        };
    }

    public function pushValueToNextValue(\ReflectionProperty $prop, mixed $value): mixed
    {
        $type = $prop->getType();

        if ($type instanceof \ReflectionUnionType) {
            $type = $type->getTypes()[0];
        }

        if ($type instanceof \ReflectionIntersectionType || $this->nextHandler) {
            return ($this->nextHandler)($value);
        }

        $typeName = $type?->getName();

        if (is_a($typeName, \DateTimeInterface::class, true)) {
            return new \DateTimeImmutable();
        }

        return match ($typeName) {
            'int' => ((int) $value) + 1,
            'float', 'double' => ((float) $value) + 1.0,
            'string' => uid(),
            default => throw new \RuntimeException('Unsupported optimistic lock type: ' . ($typeName ?? 'mixed')),
        };
    }
}
