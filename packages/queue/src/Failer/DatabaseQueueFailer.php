<?php

declare(strict_types=1);

namespace Windwalker\Queue\Failer;

use DateTime;
use InvalidArgumentException;
use JsonException;
use RuntimeException;
use Windwalker\Data\Collection;
use Windwalker\Database\DatabaseAdapter;
use Windwalker\Queue\Driver\UuidDriverTrait;
use Windwalker\Queue\Enum\DatabaseIdType;

/**
 * The DatabaseQueueFailer class.
 *
 * @since  3.2
 */
class DatabaseQueueFailer implements QueueFailerInterface
{
    use UuidDriverTrait;

    /**
     * DatabaseQueueFailer constructor.
     *
     * @param  DatabaseAdapter  $db
     * @param  string           $table
     */
    public function __construct(
        protected DatabaseAdapter $db,
        protected string $table = 'queue_failed_jobs',
        DatabaseIdType $idType = DatabaseIdType::INT
    ) {
        $this->idType = $idType;
    }

    /**
     * isSupported
     *
     * @return  bool
     */
    public function isSupported(): bool
    {
        return $this->db->getTableManager($this->table)->exists();
    }

    /**
     * add
     *
     * @param  string  $connection
     * @param  string  $channel
     * @param  string  $body
     * @param  string  $exception
     *
     * @return  int|string
     * @throws JsonException
     */
    public function add(string $connection, string $channel, string $body, string $exception): int|string
    {
        $data = compact(
            'connection',
            'channel',
            'body',
            'exception'
        );

        if ($this->idType->isUuid()) {
            $data['id'] = $this->generateUuidString();
        }

        $data['created'] = new DateTime('now');

        $data = $this->db->getWriter()->insertOne($this->table, $data, 'id');

        return $data['id'];
    }

    /**
     * all
     *
     * @return  iterable
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function all(): iterable
    {
        $query = $this->db->select('*')
            ->from($this->table);

        /** @var Collection $item */
        foreach ($query as $item) {
            $item->id = $this->idType->toReadable($item->id);

            yield $item;
        }
    }

    /**
     * get
     *
     * @param  mixed  $conditions
     *
     * @return array|null
     */
    public function get(mixed $conditions): ?array
    {
        $conditions = $this->makeConditionsWritable($conditions);

        $item = $this->db->select('*')
            ->from($this->table)
            ->where('id', $conditions)
            ->get();

        if (!$item) {
            return null;
        }

        $item->id = $this->idType->toReadable($item->id);

        return $item->dump();
    }

    /**
     * remove
     *
     * @param  mixed  $conditions
     *
     * @return  bool
     */
    public function remove(mixed $conditions): bool
    {
        $conditions = $this->makeConditionsWritable($conditions);

        $this->db->delete($this->table)
            ->where('id', $conditions)
            ->execute()
            ->countAffected();

        return true;
    }

    /**
     * clear
     *
     * @return  bool
     */
    public function clear(): bool
    {
        $this->db->getTableManager($this->table)->truncate();

        return true;
    }

    /**
     * Method to get property Table
     *
     * @return  string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Method to set property table
     *
     * @param  string  $table
     *
     * @return  static  Return self to support chaining.
     */
    public function setTable(string $table): static
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @param  mixed  $conditions
     *
     * @return  int|mixed
     */
    public function makeConditionsWritable(mixed $conditions): mixed
    {
        if (!is_array($conditions)) {
            $conditions = $this->idType->toWritable($conditions);
        } elseif (isset($conditions['id'])) {
            $conditions['id'] = $this->idType->toWritable($conditions['id']);
        }

        return $conditions;
    }
}
