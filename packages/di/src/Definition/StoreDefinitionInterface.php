<?php

declare(strict_types=1);

namespace Windwalker\DI\Definition;

use Closure;
use Windwalker\DI\Container;

/**
 * Interface StoreDefinitionInterface
 */
interface StoreDefinitionInterface extends DefinitionInterface
{
    public function getId(): string;

    public function setId(string $id): static;

    public function isShared(): bool;

    public function isProtected(): bool;

    public function extend(Closure $closure): static;

    public function alias(string $alias, string $id): static;

    public function reset(): void;

    public function getOptions(): int;

    public function getCache(?string $tag = null): mixed;

    public function getTag(): ?string;

    public function providedIn(int|array|null|Closure $levels): static;

    public function resolve(?Container $container = null, array $args = [], ?string $tag = null): mixed;
}
