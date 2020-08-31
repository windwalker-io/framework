<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Manager;

use Windwalker\Database\Schema\Ddl\Table;
use Windwalker\Utilities\Cache\InstanceCacheTrait;

/**
 * The SchemaManager class.
 */
class SchemaManager extends AbstractMetaManager
{
    use InstanceCacheTrait;

    public function create(array $options = []): static
    {
        if (!$this->exists()) {
            $this->getPlatform()->createSchema($this->getName(), $options);
        }

        return $this;
    }

    public function drop(array $options = []): static
    {
        if ($this->exists()) {
            $this->getPlatform()->dropSchema($this->getName(), $options);
        }

        return $this;
    }

    public function exists(): bool
    {
        return isset($this->getPlatform()->listDatabases()[$this->getName()]);
    }

    public function getTables(bool $refresh = false): array
    {
        return $this->once(
            'tables',
            fn() => Table::wrapList(
                $this->getPlatform()
                    ->listTables($this->getName())
            ),
            $refresh
        );
    }

    public function hasTable(string $table): bool
    {
        return isset($this->getTables()[$table]);
    }

    public function getTable(string $table): ?Table
    {
        return $this->getTables()[$table] ?? null;
    }

    public function dropTable(string $table): static
    {
        $this->db->getTable($table)->drop();

        return $this;
    }

    public function reset(): static
    {
        $this->cacheReset();

        return $this;
    }
}
