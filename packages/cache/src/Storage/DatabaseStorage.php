<?php

declare(strict_types=1);

namespace Windwalker\Cache\Storage;

use Windwalker\Core\DateTime\Chronos;
use Windwalker\Database\DatabaseAdapter;
use Windwalker\ORM\ORM;

use function Windwalker\collect;
use function Windwalker\try_chronos;

class DatabaseStorage implements StorageInterface, LockableStorageInterface
{
    use LockableStorageTrait;

    protected ORM $orm {
        get => $this->db->orm();
    }

    protected array $locked = [];

    protected array $columns = [
        'id' => 'id',
        'key' => 'key',
        'group' => 'group',
        'payload' => 'payload',
        'expired_at' => 'expired_at',
    ];

    public function __construct(
        protected DatabaseAdapter $db,
        protected string $group = '',
        protected string $table = 'cache_items',
        array $columns = []
    ) {
        $this->columns = array_merge($this->columns, $columns);
    }

    public function get(string $key): mixed
    {
        $item = $this->orm->from($this->table)
            ->where($this->columns['key'], $key)
            ->where($this->columns['group'], $this->group)
            ->get();

        if (!$item) {
            return null;
        }

        $expiredAt = try_chronos($item->expired_at);

        if ($expiredAt && $expiredAt->isPast()) {
            return null;
        }

        return $item->payload;
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function clear(): bool
    {
        $this->orm->delete($this->table)
            ->execute();

        return true;
    }

    public function remove(string $key): bool
    {
        $this->orm->delete($this->table)
            ->where($this->columns['key'], $key)
            ->where($this->columns['group'], $this->group)
            ->execute();

        return true;
    }

    public function save(string $key, mixed $value, int $expiration = 0): bool
    {
        $this->clearExpired();

        return $this->orm->transaction(
            function () use ($expiration, $value, $key) {
                $item = $this->orm->from($this->table)
                    ->where($this->columns['key'], $key)
                    ->where($this->columns['group'], $this->group)
                    ->forUpdate()
                    ->get();

                $isNew = !$item;

                if ($isNew) {
                    $item = collect();
                    $item->{$this->columns['key']} = $key;
                    $item->{$this->columns['group']} = $this->group;
                }

                $item->payload = $value;

                if ($expiration) {
                    $item->expired_at = Chronos::createFromFormat('U', (string) $expiration);
                }

                $writer = $this->db->getWriter();

                if ($isNew) {
                    $writer->insertOne($this->table, $item);
                } else {
                    $writer->updateOne($this->table, $item, 'id');
                }

                return true;
            }
        );
    }

    public function lock(string $key, ?bool &$isNew = null): bool
    {
        $this->db->getPlatform()->transactionStart();

        $item = $this->orm->from($this->table)
            ->where($this->columns['key'], $key)
            ->where($this->columns['group'], $this->group)
            ->forUpdate()
            ->get();

        $isNew = !$item;

        $this->locked[$key] = $isNew;

        return true;
    }

    public function release(string $key): bool
    {
        $this->db->getPlatform()->transactionCommit();

        unset($this->locked[$key]);

        return true;
    }

    public function isLocked(string $key): bool
    {
        return array_key_exists($key, $this->locked);
    }

    protected function clearExpired(): void
    {
        $this->orm->delete($this->table)
            ->where($this->columns['group'], $this->group)
            ->where($this->columns['expired_at'], '<', new \DateTime('now'))
            ->execute();
    }
}
