<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Attributes;

use ReflectionAttribute;
use ReflectionClass;
use Windwalker\Filesystem\Filesystem;
use Windwalker\Utilities\Reflection\ReflectAccessor;
use Windwalker\Utilities\StrNormalize;

class AttributesAccessor
{
    public static function runAttributeIfExists(
        mixed $valueOrAttrs,
        string $attributeClass,
        callable $handler,
        int $options = 0
    ): int {
        $count = 0;
        $source = $valueOrAttrs;

        if (!is_array($valueOrAttrs)) {
            $valueOrAttrs = (array) static::getAttributesFromAny(
                $valueOrAttrs,
                $attributeClass,
                $options & ReflectionAttribute::IS_INSTANCEOF
            );
        }

        /** @var ReflectionAttribute $attribute */
        foreach ($valueOrAttrs as $attribute) {
            $match = strtolower($attribute->getName()) === strtolower($attributeClass);

            if (!$match && ($options & ReflectionAttribute::IS_INSTANCEOF)) {
                $match = is_subclass_of($attribute->getName(), $attributeClass);
            }

            if ($match) {
                $handler($attribute->newInstance(), $source);
                $count++;
            }
        }

        return $count;
    }

    /**
     * scanDirAndRunAttributes
     *
     * @param  string    $attributeClass
     * @param  string    $dir
     * @param  string    $namespace
     * @param  callable  $handler
     * @param  int       $options
     *
     * @return  array<string, int>
     */
    public static function scanDirAndRunAttributes(
        string $attributeClass,
        string $dir,
        string $namespace,
        callable $handler,
        int $options = 0
    ): array {
        if (!class_exists(Filesystem::class)) {
            throw new \DomainException(
                'Please install windwalker/filesystem package to use scanDirAndRunAttributes() method.'
            );
        }

        $files = Filesystem::files($dir, true);
        $results = [];

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $class = $namespace . '\\' . $file->getRelativePath() . '\\' . $file->getBasename('.php');
            $class = StrNormalize::toClassNamespace($class);

            if (class_exists($class)) {
                $results[$class] = static::runAttributeIfExists(
                    $class,
                    $attributeClass,
                    function ($attrInstance, $source) use ($file, $class, $handler) {
                        return $handler($attrInstance, $class, $file);
                    },
                    $options
                );
            }
        }

        return $results;
    }

    /**
     * @template T
     *
     * @param  mixed            $value
     * @param  class-string<T>  $attributeClass
     * @param  int              $flags
     *
     * @return  ReflectionAttribute<T>|null
     */
    public static function getFirstAttribute(
        mixed $value,
        string $attributeClass,
        int $flags = 0
    ): ?ReflectionAttribute {
        $attrs = static::getAttributesFromAny($value, $attributeClass, $flags);

        return $attrs ? $attrs[0] : null;
    }

    /**
     * @template T
     *
     * @param  mixed            $value
     * @param  class-string<T>  $attributeClass
     * @param  int              $flags
     *
     * @return  T|null
     */
    public static function getFirstAttributeInstance(
        mixed $value,
        string $attributeClass,
        int $flags = 0
    ): ?object {
        $attr = static::getFirstAttribute($value, $attributeClass, $flags);

        return $attr?->newInstance();
    }

    /**
     * Get Attributes from any supported object or class names.
     *
     * @param  mixed        $value
     * @param  string|null  $name
     * @param  int          $flags
     *
     * @return  ?array<int, ReflectionAttribute>
     */
    public static function getAttributesFromAny(
        mixed $value,
        string|null $name = null,
        int $flags = 0
    ): ?array {
        if (!$value instanceof \Reflector) {
            $ref = ReflectAccessor::reflect($value);

            if (!$ref) {
                return null;
            }
        } else {
            $ref = $value;
        }

        $attrs[] = $ref->getAttributes($name, $flags);

        if ($ref instanceof ReflectionClass) {
            while ($ref = $ref->getParentClass()) {
                $attrs[] = $ref->getAttributes($name, $flags);
            }
        }

        return array_merge(...$attrs);
    }

    /**
     * @template Member of \Reflector
     * @template Attr of object
     *
     * @param  string|object                       $ref
     * @param  class-string<Attr>                  $name
     * @param  int                                 $flags
     * @param  class-string<Member>|\Closure|null  $filter
     *
     * @return  \Generator<string, array{ Member, ReflectionAttribute<Attr> }>
     */
    public static function getMembersWithAttribute(
        string|object $ref,
        string $name,
        int $flags = 0,
        \Closure|string|null $filter = null
    ): \Generator {
        $ref = ReflectAccessor::reflect($ref);

        foreach (static::findAttributeOfMembers($ref, $name, $flags) as $refName => [$memberRef, $attr]) {
            if (is_string($filter) && !is_a($memberRef, $filter, true)) {
                continue;
            }

            if ($filter instanceof \Closure && !$filter($memberRef, $attr)) {
                continue;
            }

            yield $refName => [$memberRef, $attr];
        }
    }

    /**
     * @template Member of \Reflector
     * @template Attr of object
     *
     * @param  string|object                       $ref
     * @param  class-string<Attr>                  $name
     * @param  int                                 $flags
     * @param  class-string<Member>|\Closure|null  $filter
     *
     * @return  array{ Member, ReflectionAttribute<Attr> }|null
     */
    public static function getFirstMemberWithAttribute(
        string|object $ref,
        string $name,
        int $flags = 0,
        \Closure|string|null $filter = null
    ): ?array {
        return static::getMembersWithAttribute($ref, $name, $flags, $filter)->current();
    }

    /**
     * @param  ReflectionClass  $ref
     * @param  string           $name
     * @param  int              $flags
     *
     * @return  \Generator<string, array{ \Reflector, ReflectionAttribute }>
     */
    private static function findAttributeOfMembers(
        \ReflectionClass $ref,
        string $name,
        int $flags = 0
    ): \Generator {
        /** @var \ReflectionClassConstant $constant */
        foreach ($ref->getConstants() as $constant) {
            if ($attr = static::getFirstAttribute($constant, $name, $flags)) {
                yield $constant->getName() => [$constant, $attr];
            }
        }

        foreach ($ref->getProperties() as $property) {
            if ($attr = static::getFirstAttribute($property, $name, $flags)) {
                yield $property->getName() => [$property, $attr];
            }
        }

        foreach ($ref->getMethods() as $method) {
            if ($attr = static::getFirstAttribute($method, $name, $flags)) {
                yield $method->getName() => [$method, $attr];
            }
        }

        if ($ref instanceof \ReflectionEnum) {
            foreach ($ref->getCases() as $case) {
                if ($attr = static::getFirstAttribute($case, $name, $flags)) {
                    yield $case->getName() => [$case, $attr];
                }
            }
        }
    }
}
