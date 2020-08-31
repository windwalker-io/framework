<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Manager;

use Windwalker\Utilities\Cache\InstanceCacheTrait;

/**
 * The DatabaseManager class.
 */
class DatabaseManager extends AbstractMetaManager
{
    use InstanceCacheTrait;

    public function create(array $options = []): static
    {
        if (!$this->exists()) {
            $this->getPlatform()->createDatabase($this->getName(), $options);
        }

        return $this;
    }

    public function drop(array $options = []): static
    {
        if ($this->exists()) {
            $this->getPlatform()->dropDatabase($this->getName(), $options);
        }

        return $this;
    }

    public function exists(): bool
    {
        return isset($this->getPlatform()->listDatabases()[$this->getName()]);
    }

    public function reset(): static
    {
        $this->cacheReset();

        return $this;
    }
}
