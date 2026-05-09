<?php

declare(strict_types=1);

namespace Windwalker\Cache\Storage;

use Windwalker\Database\DatabaseAdapter;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Database\Platform\AbstractPlatform;
use Windwalker\Database\Schema\Schema;
use Windwalker\ORM\ORM;
use Windwalker\Query\Query;
use Throwable;

use function Windwalker\raw;

class DatabaseStorage implements StorageInterface, PrunableStorageInterface, GroupedStorageInterface
{

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

    public string $keyField {
        get => $this->columns['key'];
    }

    public string $groupField {
        get => $this->columns['group'];
    }

    public string $payloadField {
        get => $this->columns['payload'];
    }

    public string $expiredAtField {
        get => $this->columns['expired_at'];
    }

    public function __construct(
        protected DatabaseAdapter $db,
        public protected(set) string $group = '',
        protected string $table = 'cache_items',
        array $columns = [],
        protected float $pruneProbability = 0.01,
        protected bool $autoCreateTable = true,
    ) {
        $this->columns = array_merge($this->columns, $columns);
    }

    public function save(string $key, mixed $value, int $expiration = 0): bool
    {
        try {
            return $this->saveInternal($key, $value, $expiration);
        } catch (Throwable $e) {
            // Lazy create table only when first save fails and table is missing.
            if (!$this->autoCreateTable || $this->tableExists()) {
                throw $e;
            }

            $this->ensureTableExists();

            return $this->saveInternal($key, $value, $expiration);
        }
    }

    public function get(string $key): mixed
    {
        $payload = $this->orm->select($this->payloadField)
            ->from($this->table)
            ->where($this->keyField, $key)
            ->where($this->groupField, $this->group)
            // AND (A OR B)
            ->orWhere(
                function (Query $query) {
                    $query->where($this->expiredAtField, null);
                    $query->where(
                        $this->expiredAtField,
                        '>',
                        raw($this->getCurrentTimestampStatement())
                    );
                }
            )
            ->result();

        return $payload;
    }

    public function has(string $key): bool
    {
        try {
            return $this->get($key) !== null;
        } catch (Throwable) {
            return false;
        }
    }

    public function clear(): bool
    {
        $this->orm->delete($this->table)
            ->where($this->groupField, $this->group)
            ->execute();

        return true;
    }

    public function remove(string $key): bool
    {
        try {
            $this->orm->delete($this->table)
                ->where($this->keyField, $key)
                ->where($this->groupField, $this->group)
                ->execute();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function saveInternal(string $key, mixed $value, int $expiration = 0): bool
    {
        $expiredAt = 'NULL';

        if ($expiration) {
            $expiredAt = (string) $expiration;
        }

        // Upsert
        $query = $this->upsertSql($key, $this->group, $value, $expiredAt);

        if ($query) {
            $query->execute();

            if ($this->shouldPrune()) {
                $this->prune();
            }

            return true;
        }

        // Fallback
        $this->orm->transaction(
            function () use ($expiredAt, $value, $key) {
                $item = $this->orm->from($this->table)
                    ->where($this->keyField, $key)
                    ->where($this->groupField, $this->group)
                    ->forUpdate()
                    ->get();

                $isNew = !$item;

                if ($isNew) {
                    $this->orm->insert($this->table)
                        ->columns(
                            $this->keyField,
                            $this->groupField,
                            $this->payloadField,
                            $this->expiredAtField,
                        )
                        ->values(
                            [
                                $key,
                                $this->group,
                                $value,
                                raw($expiredAt),
                            ]
                        )
                        ->execute();
                } else {
                    $this->orm->update($this->table)
                        ->where($this->keyField, $key)
                        ->where($this->groupField, $this->group)
                        ->set($this->payloadField, $value)
                        ->set($this->expiredAtField, raw($expiredAt))
                        ->execute();
                }
            }
        );

        if ($this->shouldPrune()) {
            $this->prune();
        }

        return true;
    }

    public function prune(): int
    {
        return $this->runPruneStatement($this->group)->countAffected();
    }

    public function pruneAll(): int
    {
        return $this->runPruneStatement()->countAffected();
    }

    public function shouldPrune(): bool
    {
        return random_int(0, 100_000) / 100_000 < $this->pruneProbability;
    }

    /**
     * Get the prune probability (0.0 to 1.0)
     */
    public function getPruneProbability(): float
    {
        return $this->pruneProbability;
    }

    /**
     * Set the prune probability (0.0 to 1.0)
     *
     * @param  float  $probability
     * @return  static  Return self to support chaining.
     */
    public function setPruneProbability(float $probability): static
    {
        $this->pruneProbability = max(0.0, min(1.0, $probability));

        return $this;
    }

    public function withGroup(string $group): static
    {
        $new = clone $this;
        $new->group = $group;

        return $new;
    }

    private function upsertSql(string $key, string $group, string $payload, string $expiredAt): ?Query
    {
        $platformName = $this->db->getPlatform()->getName();

        $query = $this->db->createQuery();

        $table = $query->quoteName($this->table);
        $keyField = $query->quoteName($this->keyField);
        $payloadField = $query->quoteName($this->payloadField);
        $groupField = $query->quoteName($this->groupField);
        $expiredAtField = $query->quoteName($this->expiredAtField);

        switch ($platformName) {
            case AbstractPlatform::MYSQL:
                $query->bind('key', $key);
                $query->bind('group', $group);
                $query->bind('payload', $payload);

                return $query->sql(
                    "INSERT INTO $table ($keyField, $groupField, $payloadField, $expiredAtField)
VALUES (:key, :group, :payload, $expiredAt)
ON DUPLICATE KEY UPDATE
    $payloadField = VALUES($payloadField),
    $expiredAtField = VALUES($expiredAtField)"
                );

            case AbstractPlatform::POSTGRESQL:
                $query->bind('key', $key);
                $query->bind('group', $group);
                $query->bind('payload', $payload);

                return $query->sql(
                    "INSERT INTO $table ($keyField, $groupField, $payloadField, $expiredAtField)
VALUES (:key, :group, :payload, $expiredAt) ON CONFLICT ($keyField, $groupField) DO UPDATE SET
$payloadField = EXCLUDED.$payloadField,
$expiredAtField = EXCLUDED.$expiredAtField
"
                );

            case AbstractPlatform::SQLSERVER:
                if (
                    version_compare(
                        $this->db->getDriver()->getVersion(),
                        '10',
                        '<'
                    )
                ) {
                    return null;
                }

                $query->bind('key1', $key);
                $query->bind('key2', $key);
                $query->bind('group1', $group);
                $query->bind('group2', $group);
                $query->bind('payload1', $payload);
                $query->bind('payload2', $payload);

                // phpcs:disable
                // MERGE is only available since SQL Server 2008 and must be terminated by semicolon
                // It also requires HOLDLOCK according to http://weblogs.sqlteam.com/dang/archive/2009/01/31/UPSERT-Race-Condition-With-MERGE.aspx
                return $query->sql(
                    "MERGE INTO $table WITH (HOLDLOCK) USING (SELECT 1 AS dummy) AS src ON ($keyField = :key1 AND $groupField = :group1)
                    WHEN NOT MATCHED THEN INSERT ($keyField, $groupField, $payloadField, $expiredAtField)
                    VALUES (:key2, :group1, :payload1, $expiredAt)
                    WHEN MATCHED THEN UPDATE SET $payloadField = :payload2, $expiredAtField = $expiredAt;"
                );

            case AbstractPlatform::SQLITE:
                $query->bind('key', $key);
                $query->bind('group', $group);
                $query->bind('payload', $payload);

                return $query->sql(
                    "INSERT OR REPLACE INTO $table ($keyField, $groupField, $payloadField, $expiredAtField)
                VALUES (:key, :group, :payload, $expiredAt)"
                );
        }

        return null;
    }

    private function runPruneStatement(?string $group = null): StatementInterface
    {
        $query = $this->orm->delete($this->table)
            ->where($this->expiredAtField, '<', raw($this->getCurrentTimestampStatement()));

        if ($group !== null) {
            $query->where($this->groupField, $group);
        }

        return $query->execute();
    }

    private function getCurrentTimestampStatement(): string
    {
        return match ($this->db->getPlatform()->getName()) {
            AbstractPlatform::MYSQL => 'UNIX_TIMESTAMP(NOW(6))',
            AbstractPlatform::SQLITE => "(julianday('now') - 2440587.5) * 86400.0",
            AbstractPlatform::POSTGRESQL => 'CAST(EXTRACT(epoch FROM NOW()) AS DOUBLE PRECISION)',
            // 'oci' => "(CAST(systimestamp AT TIME ZONE 'UTC' AS DATE) - DATE '1970-01-01') * 86400 + TO_NUMBER(TO_CHAR(systimestamp AT TIME ZONE 'UTC', 'SSSSS.FF'))",
            AbstractPlatform::SQLSERVER => "CAST(DATEDIFF_BIG(ms, '1970-01-01', SYSUTCDATETIME()) AS FLOAT) / 1000.0",
            default => (new \DateTimeImmutable())->format('U.u'),
        };
    }

    public static function createTable(DatabaseAdapter $db, string $table = 'cache_items'): void
    {
        $db->getTableManager($table)->create(
            function (Schema $schema) use ($db) {
                if ($db->getPlatform()->getName() === AbstractPlatform::SQLITE) {
                    $schema->primary('id');
                } else {
                    $schema->primaryBigint('id');
                }

                $schema->varchar('key');
                $schema->varchar('group');
                $schema->longtext('payload');
                $schema->integer('expired_at')->nullable(true);

                $schema->addUniqueKey(['key', 'group']);
                $schema->addIndex('expired_at');
            }
        );
    }

    protected function ensureTableExists(): void
    {
        static::createTable($this->db, $this->table);
    }

    protected function tableExists(): bool
    {
        return $this->db->getTableManager($this->table)->exists();
    }
}
