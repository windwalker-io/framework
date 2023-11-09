<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Memory;

use Swoole\Table;

readonly class SwooleTable implements MemoryTableInterface
{
    protected Table $table;

    public function __construct(int $size, float $conflictProportion = 0.2)
    {
        $this->table ??= new Table($size, $conflictProportion);
    }

    /**
     * @param int $type Must be one of the following constants: Table::TYPE_INT, Table::TYPE_FLOAT, or Table::TYPE_STRING.
     * @param int $size Length of the string. This parameter is ignored for other types.
     */
    public function column(string $name, int $type, int $size = 0): bool
    {
        return $this->table->column($name, $type, $size);
    }

    /**
     * Create the table.
     *
     * @return bool TRUE on success, FALSE on failure.
     */
    public function create(): bool
    {
        return $this->table->create();
    }

    /**
     * Destroy the table.
     *
     * It will free all memory allocated for the table, although the Table object itself still exists. After calling
     * this method, the Table object should not be used anymore.
     *
     * After the table is destroyed,
     *   - property $size and $memorySize still contain the same values of the table before it's destroyed.
     *   - method \Swoole\Table::getSize() and \Swoole\Table::getMemorySize() return 0.
     *
     * @return bool returns TRUE all the time
     */
    public function destroy(): bool
    {
        return $this->table->destroy();
    }

    public function set(string $key, array $value): bool
    {
        return $this->table->set($key, $value);
    }

    public function get(string $key, ?string $field = null): mixed
    {
        if ($field === null) {
            return $this->table->get($key);
        }

        return $this->table->get($key, $field);
    }

    /**
     * @see \Countable::count()
     * @see https://www.php.net/manual/en/countable.count.php
     * {@inheritDoc}
     */
    public function count(): int
    {
        return $this->table->count();
    }

    /**
     * @alias Alias of method \Swoole\Table::del().
     * @see \Swoole\Table::del()
     */
    public function delete(string $key): bool
    {
        return $this->table->delete($key);
    }

    public function exists(string $key): bool
    {
        return $this->table->exists($key);
    }

    public function getTable(): Table
    {
        return $this->table;
    }
}
