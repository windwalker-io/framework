<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI\Definition;

use Closure;

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

    public function reset(): void;

    public function getOptions(): int;

    public function getCache(): mixed;
}
