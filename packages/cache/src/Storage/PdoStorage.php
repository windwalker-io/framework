<?php

declare(strict_types=1);

namespace Windwalker\Cache\Storage;

use PDO;
use PDOException;
use Throwable;

class PdoStorage implements StorageInterface, PrunableStorageInterface, GroupedStorageInterface
{
    public function __construct(
        protected PDO $pdo,
        public protected(set) string $group = '',
        protected string $table = 'cache_items',
        protected array $columns = [
            'id' => 'id',
            'key' => 'key',
            'group' => 'group',
            'payload' => 'payload',
            'expired_at' => 'expired_at',
        ],
        protected float $pruneProbability = 0.01,
        protected bool $autoCreateTable = true,
    ) {
    }

    public function save(string $key, mixed $value, int $expiration = 0): bool
    {
        try {
            return $this->saveInternal($key, $value, $expiration);
        } catch (Throwable $e) {
            if (!$this->autoCreateTable || $this->tableExists()) {
                throw $e;
            }

            $this->ensureTableExists();

            return $this->saveInternal($key, $value, $expiration);
        }
    }

    public function get(string $key): mixed
    {
        $sql = sprintf(
            'SELECT %s FROM %s WHERE %s = :key AND %s = :grp AND (%s IS NULL OR %s > :now) LIMIT 1',
            $this->qn($this->columns['payload']),
            $this->qn($this->table),
            $this->qn($this->columns['key']),
            $this->qn($this->columns['group']),
            $this->qn($this->columns['expired_at']),
            $this->qn($this->columns['expired_at'])
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(
            [
                ':key' => $key,
                ':grp' => $this->group,
                ':now' => time(),
            ]
        );

        $value = $stmt->fetchColumn();

        return $value === false ? null : $value;
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
        $sql = sprintf(
            'DELETE FROM %s WHERE %s = :grp',
            $this->qn($this->table),
            $this->qn($this->columns['group'])
        );

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([':grp' => $this->group]);
    }

    public function remove(string $key): bool
    {
        try {
            $sql = sprintf(
                'DELETE FROM %s WHERE %s = :key AND %s = :grp',
                $this->qn($this->table),
                $this->qn($this->columns['key']),
                $this->qn($this->columns['group'])
            );

            $stmt = $this->pdo->prepare($sql);

            return $stmt->execute(
                [
                    ':key' => $key,
                    ':grp' => $this->group,
                ]
            );
        } catch (Throwable) {
            return false;
        }
    }

    public function prune(): int
    {
        $sql = sprintf(
            'DELETE FROM %s WHERE %s IS NOT NULL AND %s <= :now AND %s = :grp',
            $this->qn($this->table),
            $this->qn($this->columns['expired_at']),
            $this->qn($this->columns['expired_at']),
            $this->qn($this->columns['group'])
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(
            [
                ':now' => time(),
                ':grp' => $this->group,
            ]
        );

        return $stmt->rowCount();
    }

    public function pruneAll(): int
    {
        $sql = sprintf(
            'DELETE FROM %s WHERE %s IS NOT NULL AND %s <= :now',
            $this->qn($this->table),
            $this->qn($this->columns['expired_at']),
            $this->qn($this->columns['expired_at'])
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':now' => time()]);

        return $stmt->rowCount();
    }

    public function shouldPrune(): bool
    {
        return random_int(0, 100_000) / 100_000 < $this->pruneProbability;
    }

    public function getPruneProbability(): float
    {
        return $this->pruneProbability;
    }

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

    public static function createTable(PDO $pdo, string $table = 'cache_items'): void
    {
        $driver = (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $qTable = self::quoteNameByDriver($driver, $table);
        $qKey = self::quoteNameByDriver($driver, 'key');
        $qGroup = self::quoteNameByDriver($driver, 'group');
        $qPayload = self::quoteNameByDriver($driver, 'payload');
        $qExpiredAt = self::quoteNameByDriver($driver, 'expired_at');

        $pdo->exec(
            match ($driver) {
                'sqlite' => <<<SQL
                    CREATE TABLE IF NOT EXISTS $qTable (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        $qKey VARCHAR(255) NOT NULL,
                        $qGroup VARCHAR(255) NOT NULL DEFAULT '',
                        $qPayload TEXT,
                        $qExpiredAt DOUBLE NULL
                    )
                    SQL,
                'mysql' => <<<SQL
                    CREATE TABLE IF NOT EXISTS $qTable (
                        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                        $qKey VARCHAR(255) NOT NULL,
                        $qGroup VARCHAR(255) NOT NULL DEFAULT '',
                        $qPayload LONGTEXT,
                        $qExpiredAt DOUBLE NULL
                    )
                    SQL,
                'pgsql' => <<<SQL
                    CREATE TABLE IF NOT EXISTS $qTable (
                        id BIGSERIAL PRIMARY KEY,
                        $qKey VARCHAR(255) NOT NULL,
                        $qGroup VARCHAR(255) NOT NULL DEFAULT '',
                        $qPayload TEXT,
                        $qExpiredAt DOUBLE PRECISION NULL
                    )
                    SQL,
                'sqlsrv' => <<<SQL
                    IF OBJECT_ID(N'$table', N'U') IS NULL
                    CREATE TABLE $qTable (
                        id BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
                        $qKey NVARCHAR(255) NOT NULL,
                        $qGroup NVARCHAR(255) NOT NULL DEFAULT '',
                        $qPayload NVARCHAR(MAX) NULL,
                        $qExpiredAt FLOAT NULL
                    )
                    SQL,
                default => <<<SQL
                    CREATE TABLE IF NOT EXISTS $qTable (
                        id BIGINT PRIMARY KEY,
                        $qKey VARCHAR(255) NOT NULL,
                        $qGroup VARCHAR(255) NOT NULL DEFAULT '',
                        $qPayload TEXT,
                        $qExpiredAt DOUBLE NULL
                    )
                    SQL,
            }
        );

        try {
            $pdo->exec("CREATE UNIQUE INDEX {$table}_key_group_unique ON $qTable ($qKey, $qGroup)");
        } catch (Throwable) {
        }

        try {
            $pdo->exec("CREATE INDEX {$table}_expired_at_index ON $qTable ($qExpiredAt)");
        } catch (Throwable) {
        }
    }

    protected function saveInternal(string $key, mixed $value, int $expiration = 0): bool
    {
        $expiredAt = $expiration ?: null;

        $upsertSql = $this->upsertSql();

        if ($upsertSql !== null) {
            $upsert = $this->pdo->prepare($upsertSql);
            $upsert->execute($this->upsertBindings($key, $value, $expiredAt));

            if ($this->shouldPrune()) {
                $this->prune();
            }

            return true;
        }

        $sql = sprintf(
            'UPDATE %s SET %s = :payload, %s = :expiredAt WHERE %s = :key AND %s = :grp',
            $this->qn($this->table),
            $this->qn($this->columns['payload']),
            $this->qn($this->columns['expired_at']),
            $this->qn($this->columns['key']),
            $this->qn($this->columns['group'])
        );

        $update = $this->pdo->prepare($sql);
        $update->execute(
            [
                ':payload' => $value,
                ':expiredAt' => $expiredAt,
                ':key' => $key,
                ':grp' => $this->group,
            ]
        );

        if ($update->rowCount() === 0) {
            try {
                $insertSql = sprintf(
                    'INSERT INTO %s (%s, %s, %s, %s) VALUES (:key, :grp, :payload, :expiredAt)',
                    $this->qn($this->table),
                    $this->qn($this->columns['key']),
                    $this->qn($this->columns['group']),
                    $this->qn($this->columns['payload']),
                    $this->qn($this->columns['expired_at'])
                );

                $insert = $this->pdo->prepare($insertSql);
                $insert->execute(
                    [
                        ':key' => $key,
                        ':grp' => $this->group,
                        ':payload' => $value,
                        ':expiredAt' => $expiredAt,
                    ]
                );
            } catch (PDOException $e) {
                // Race condition: if another writer inserts first, run update once more.
                $update->execute(
                    [
                        ':payload' => $value,
                        ':expiredAt' => $expiredAt,
                        ':key' => $key,
                        ':grp' => $this->group,
                    ]
                );
            }
        }

        if ($this->shouldPrune()) {
            $this->prune();
        }

        return true;
    }

    protected function ensureTableExists(): void
    {
        static::createTable($this->pdo, $this->table);
    }

    protected function tableExists(): bool
    {
        try {
            $this->pdo->query('SELECT 1 FROM ' . $this->qn($this->table) . ' WHERE 1 = 0');

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function qn(string $identifier): string
    {
        return self::quoteNameByDriver((string) $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME), $identifier);
    }

    private static function quoteNameByDriver(string $driver, string $identifier): string
    {
        $parts = explode('.', $identifier);

        return implode(
            '.',
            array_map(
                static fn(string $part): string => match ($driver) {
                    'mysql' => '`' . str_replace('`', '``', $part) . '`',
                    'sqlsrv' => '[' . str_replace(']', ']]', $part) . ']',
                    default => '"' . str_replace('"', '""', $part) . '"',
                },
                $parts
            )
        );
    }

    private function upsertSql(): ?string
    {
        $driver = (string) $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $table = $this->qn($this->table);
        $keyField = $this->qn($this->columns['key']);
        $groupField = $this->qn($this->columns['group']);
        $payloadField = $this->qn($this->columns['payload']);
        $expiredAtField = $this->qn($this->columns['expired_at']);

        return match ($driver) {
            'mysql' => <<<SQL
                INSERT INTO $table ($keyField, $groupField, $payloadField, $expiredAtField)
                VALUES (:key, :grp, :payload, :expiredAt)
                ON DUPLICATE KEY UPDATE
                    $payloadField = VALUES($payloadField),
                    $expiredAtField = VALUES($expiredAtField)
                SQL,
            'pgsql' => <<<SQL
                INSERT INTO $table ($keyField, $groupField, $payloadField, $expiredAtField)
                VALUES (:key, :grp, :payload, :expiredAt)
                ON CONFLICT ($keyField, $groupField) DO UPDATE SET
                    $payloadField = EXCLUDED.$payloadField,
                    $expiredAtField = EXCLUDED.$expiredAtField
                SQL,
            'sqlite' => <<<SQL
                INSERT INTO $table ($keyField, $groupField, $payloadField, $expiredAtField)
                VALUES (:key, :grp, :payload, :expiredAt)
                ON CONFLICT ($keyField, $groupField) DO UPDATE SET
                    $payloadField = excluded.$payloadField,
                    $expiredAtField = excluded.$expiredAtField
                SQL,
            'sqlsrv' => <<<SQL
                MERGE INTO $table WITH (HOLDLOCK) USING (SELECT 1 AS dummy) AS src
                ON ($keyField = :key1 AND $groupField = :grp1)
                WHEN NOT MATCHED THEN
                    INSERT ($keyField, $groupField, $payloadField, $expiredAtField)
                    VALUES (:key2, :grp2, :payload1, :expiredAt1)
                WHEN MATCHED THEN
                    UPDATE SET $payloadField = :payload2, $expiredAtField = :expiredAt2;
                SQL,
            default => null,
        };
    }

    private function upsertBindings(string $key, mixed $value, mixed $expiredAt): array
    {
        $driver = (string) $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlsrv') {
            return [
                ':key1' => $key,
                ':key2' => $key,
                ':grp1' => $this->group,
                ':grp2' => $this->group,
                ':payload1' => $value,
                ':payload2' => $value,
                ':expiredAt1' => $expiredAt,
                ':expiredAt2' => $expiredAt,
            ];
        }

        return [
            ':key' => $key,
            ':grp' => $this->group,
            ':payload' => $value,
            ':expiredAt' => $expiredAt,
        ];
    }
}

