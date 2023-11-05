<?php

declare(strict_types=1);

namespace Windwalker\ORM;

use JsonSerializable;
use Windwalker\Data\Collection;
use Windwalker\Utilities\Contract\DumpableInterface;

/**
 * Interface EntityInterface
 */
interface EntityInterface extends JsonSerializable, DumpableInterface
{
    public static function table(): ?string;

    public static function newInstance(array $data = []): static;

    public function loadAllRelations(): void;

    public function clearRelations(): void;

    public function toCollection(): Collection;
}
