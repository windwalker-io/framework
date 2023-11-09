<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Memory;

interface MemoryTableInterface extends \Countable
{
    public const TYPE_INT = 1;

    public const TYPE_FLOAT = 2;

    public const TYPE_STRING = 3;

    /**
     * @param int $type Must be one of the following constants: Table::TYPE_INT, Table::TYPE_FLOAT, or Table::TYPE_STRING.
     * @param int $size Length of the string. This parameter is ignored for other types.
     */
    public function column(string $name, int $type, int $size = 0): bool;

    /**
     * Create the table.
     *
     * @return bool TRUE on success, FALSE on failure.
     */
    public function create(): bool;

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
    public function destroy(): bool;

    public function set(string $key, array $value): bool;

    public function get(string $key, ?string $field = null): mixed;

    /**
     * @alias Alias of method \Swoole\Table::del().
     * @see \Swoole\Table::del()
     */
    public function delete(string $key): bool;

    public function exists(string $key): bool;
}
