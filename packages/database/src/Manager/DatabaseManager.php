<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Manager;

/**
 * The DatabaseManager class.
 */
class DatabaseManager extends AbstractMetaManager
{
    /**
     * createDatabase
     *
     * @param  bool   $ifNotExists
     * @param  array  $options
     *
     * @return static
     */
    public function create(bool $ifNotExists = false, array $options = []): static
    {
        if ($ifNotExists && $this->exists()) {
            return $this;
        }

        $this->db->getPlatform()->createDatabase($this->getName(), $options);

        return $this;
    }

    /**
     * dropDatabase
     *
     * @param  bool  $ifExists
     *
     * @return  static
     */
    public function drop(bool $ifExists = false): static
    {
        $name = $this->getName();

        if ($ifExists && $this->exists()) {
            return $this;
        }

        if ($name === $this->db->getPlatform()->getCurrentDatabase()) {
            $this->db->disconnect();

            $this->db->getDriver()->setOption('dbname', null);
        }

        $this->db->getPlatform()->dropDatabase($name);

        return $this;
    }

    /**
     * exists
     *
     * @return  bool
     */
    public function exists(): bool
    {
        return in_array(
            $this->getName(),
            $this->db->listDatabases(),
            true
        );
    }

    /**
     * resetCache
     *
     * @return  static
     */
    public function reset(): static
    {
        return $this;
    }
}
