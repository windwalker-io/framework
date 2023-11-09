<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Memory;

class ArrayTable implements MemoryTableInterface
{
    protected array $columns = [];

    protected array $data = [];

    public function column(string $name, int $type, int $size = 0): bool
    {
        $this->columns[$name] = compact('name', 'type', 'size');

        return true;
    }

    public function create(): bool
    {
        return true;
    }

    public function destroy(): bool
    {
        $this->data = [];

        return true;
    }

    public function set(string $key, array $value): bool
    {
        $columns = array_keys($this->columns);

        $item = [];

        foreach ($columns as $column) {
            $item[$column] = $value[$column] ?? null;
        }

        $this->data[$key] = $item;

        return true;
    }

    public function get(string $key, ?string $field = null): mixed
    {
        $value = $this->data[$key] ?? null;

        if (!$value) {
            return false;
        }

        return $value[$field] ?? null;
    }

    public function delete(string $key): bool
    {
        unset($this->data[$key]);

        return true;
    }

    public function exists(string $key): bool
    {
        return isset($this->data[$key]);
    }

    public function count(): int
    {
        return count($this->data);
    }
}
