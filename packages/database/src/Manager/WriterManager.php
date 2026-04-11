<?php

declare(strict_types=1);

namespace Windwalker\Database\Manager;

use InvalidArgumentException;
use JsonException;
use RuntimeException;
use Windwalker\Database\DatabaseAdapter;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Database\Platform\AbstractPlatform;
use Windwalker\Query\Query;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\TypeCast;
use Windwalker\Utilities\Wrapper\RawWrapper;

/**
 * The WriterManager class.
 */
class WriterManager
{
    protected DatabaseAdapter $db;

    /**
     * Property cursor.
     *
     * @var  StatementInterface|null
     */
    protected ?StatementInterface $statement = null;

    /**
     * Constructor.
     *
     * @param  DatabaseAdapter  $db
     */
    public function __construct(DatabaseAdapter $db)
    {
        $this->db = $db;
    }

    /**
     * Inserts a row into a table based on an object's properties.
     *
     * @param  string        $table    The name of the database table to insert into.
     * @param  array|object &$data     A reference to an object whose public properties match the table fields.
     * @param  string|null   $key      The name of the primary key. If provided the object property is updated.
     * @param  array         $options  Options.
     *
     * @return array|object
     *
     * @throws JsonException
     * @since   2.0
     */
    public function insertOne(string $table, array|object $data, ?string $key = null, array $options = []): array|object
    {
        $options = array_merge(
            [
                'incrementField' => false,
                'filterFields' => false,
            ],
            $options
        );

        $fields = [];
        $values = [];

        $item = TypeCast::toArray($data);

        if ($options['filterFields']) {
            $item = $this->filterFields($table, $item);
        }

        $query = $this->db->createQuery();

        // Iterate over the object variables to build the query fields and values.
        foreach ($item as $k => $v) {
            // Prepare and sanitize the fields and values for the database query.
            $fields[] = $k;
            $values[] = $v;
        }

        // Create the base insert statement.
        $query->insert($table, $options['incrementField'])
            ->columns($fields)
            ->values($values);

        // Set the query and execute the insert.
        $this->execute($query);

        // Update the primary key if it exists.
        if (!$options['incrementField'] && $key && $id = $this->lastInsertId()) {
            if (is_array($data)) {
                $data[$key] = $id;
            } else {
                $data->$key = $id;
            }
        }

        return $data;
    }

    /**
     * Updates a row in a table based on an object's properties.
     *
     * @param  string        $table    The name of the database table to update.
     * @param  array|object  $data     A reference to an object whose public properties match the table fields.
     * @param  array|string  $key      The name of the primary key.
     * @param  array         $options  Options.
     *
     * @return StatementInterface
     *
     * @throws JsonException
     * @since   2.0
     */
    public function updateOne(
        string $table,
        array|object $data,
        array|string $key,
        array $options = []
    ): StatementInterface {
        $options = array_merge(
            [
                'updateNulls' => true,
                'filterFields' => [],
                'lockCallback' => null,
            ],
            $options
        );

        $item = TypeCast::toArray($data);

        $key = (array) $key;

        if ($key === []) {
            throw new InvalidArgumentException(
                'Condition fields cannot be empty array when updating data.'
            );
        }

        // Create the base update statement.
        $query = $this->db->update($table);

        if ($options['filterFields']) {
            $item = $this->filterFields($table, $item);
        }

        // Iterate over the object variables to build the query fields/value pairs.
        foreach ($item as $k => $v) {
            if (is_array($v) || is_object($v)) {
                if ($options['toJson']) {
                    // To JSON
                    $v = json_encode($v, JSON_THROW_ON_ERROR);
                } else {
                    // Only process non-null scalars.
                    continue;
                }
            }

            if ($v !== null) {
                $v = TypeCast::toString($v);
            }

            if (in_array($k, $key, true)) {
                // Set wheres later
                continue;
            }

            // If the value is null and we want to update nulls then set it.
            if ($v === null && !$options['updateNulls']) {
                continue;
            }

            // Add the field to be updated.
            $query->set($k, $v);
        }

        foreach ($key as $k) {
            // Set the primary key to the WHERE clause instead of a field to update.
            $query->where($k, $data[$k] ?? null);
        }

        if ($options['lockCallback'] ?? null) {
            $options['lockCallback']($query);
        }

        // Set the query and execute the update.
        return $this->execute($query);
    }

    /**
     * save
     *
     * @param  string        $table    The name of the database table to update.
     * @param  array|object  $data     A reference to an object whose public properties match the table fields.
     * @param  string|null   $key      The name of the primary key.
     * @param  array         $options  Options.
     *
     * @return  mixed
     * @throws JsonException
     */
    public function saveOne(string $table, array|object $data, string|null $key, array $options = []): mixed
    {
        if ($key !== null) {
            if (is_array($data)) {
                $id = $data[$key] ?? null;
            } else {
                $id = $data->$key ?? null;
            }
        }

        if ($id) {
            return $this->updateOne($table, $data, $key, $options);
        }

        return $this->insertOne($table, $data, $key, $options);
    }

    public function upsert(
        string $table,
        array|object $data,
        array|string $keys,
        array|null $updateFields = null,
        array $options = [],
    ): StatementInterface {
        $keys = (array) $keys;

        $platformName = $this->db->getPlatform()->getName();
        $updateNulls = $options['updateNulls'] ?? true;

        if (!$updateNulls) {
            $data = array_filter($data, fn($v) => $v !== null);
        }

        $query = $this->db->createQuery();

        $table = (string) $query->quoteName($table);
        $data = TypeCast::toArray($data);
        $allFields = array_keys($data);
        // $valueFields = array_filter($allFields, fn($field) => !in_array($field, $keys, true));

        $updateFields ??= array_filter($allFields, fn($field) => !in_array($field, $keys, true));

        $quotedUpdateFields = $query->qnMultiple($updateFields);
        $quotedFields = $query->qnMultiple($allFields);
        // $quotedValueFields = $query->qnMultiple($valueFields);
        $quotedKeys = $query->qnMultiple($keys);

        $columns = $query->clause('()', $quotedFields, ',');
        $values = $query->clause('()', [], ',');

        foreach ($data as $k => $v) {
            if ($v instanceof RawWrapper) {
                $values->append($v());
            } else {
                $query->bind($k, $v);
                $values->append(":$k");
            }
        }

        switch ($platformName) {
            case AbstractPlatform::MYSQL:
                $updateActions = $query->clause('', [], ',');

                foreach ($quotedUpdateFields as $key) {
                    $updateActions->append("$key = VALUES($key)");
                }

                $query->sql(
                    <<<SQL
                    INSERT INTO $table $columns
                    VALUES $values
                    ON DUPLICATE KEY UPDATE
                        $updateActions
                    SQL
                );
                break;

            case AbstractPlatform::POSTGRESQL:
                $updateActions = $query->clause('()', [], ',');

                foreach ($quotedKeys as $key) {
                    $updateActions->append($key);
                }

                $excludeKeys = $query->clause('', [], ',');

                foreach ($quotedUpdateFields as $key) {
                    $excludeKeys->append("$key = EXCLUDED.$key");
                }

                $query->sql(
                    <<<SQL
                    INSERT INTO $table $columns
                    VALUES $values ON CONFLICT $updateActions DO UPDATE SET
                        $excludeKeys
                    SQL
                );
                break;

            case AbstractPlatform::SQLSERVER:
                if (
                    version_compare(
                        $this->db->getDriver()->getVersion(),
                        '10',
                        '<'
                    )
                ) {
                    throw new RuntimeException('Upsert is only supported in SQL Server 2008 (10) and later versions.');
                }

                $srcOn = $query->clause('()', [], ' AND ');

                foreach ($keys as $key) {
                    $srcOn->append($query->quoteName($key) . " = :key_$key");
                    $query->bind(
                        "key_$key",
                        $data[$key]
                        ?? throw new InvalidArgumentException("Key field: $key not exists in data.")
                    );
                }

                $updateSet = $query->clause('', [], ',');

                foreach ($updateFields as $k) {
                    $v = $data[$k];

                    if ($v instanceof RawWrapper) {
                        $updateSet->append($query->quoteName($k) . '=' . $v());
                    } else {
                        $query->bind('update_set_' . $k, $v);
                        $updateSet->append($query->quoteName($k) . " = :update_set_$k");
                    }
                }

                // phpcs:disable
                // MERGE is only available since SQL Server 2008 and must be terminated by semicolon
                // It also requires HOLDLOCK according to http://weblogs.sqlteam.com/dang/archive/2009/01/31/UPSERT-Race-Condition-With-MERGE.aspx
                $query->sql(
                    "MERGE INTO $table WITH (HOLDLOCK) USING (SELECT 1 AS dummy) AS src ON $srcOn
                    WHEN NOT MATCHED THEN INSERT $columns
                    VALUES $values
                    WHEN MATCHED THEN UPDATE SET $updateSet;"
                );
                break;

            case AbstractPlatform::SQLITE:
                $query->sql(
                    "INSERT OR REPLACE INTO $table $columns
                VALUES $values"
                );
                break;
        }

        return $query->execute();
    }

    /**
     * insertMultiple
     *
     * @param  string       $table  The name of the database table to update.
     * @param  array        $items  A reference to an object whose public properties match the table fields.
     * @param  string|null  $key    The name of the primary key.
     * @param  array        $options
     *
     * @return array[]|object[]
     * @throws JsonException
     */
    public function insertMultiple(string $table, iterable $items, ?string $key = null, array $options = []): array
    {
        $result = [];

        foreach ($items as $k => $item) {
            $result[$k] = $this->insertOne($table, $item, $key, $options);
        }

        return $result;
    }

    public function insertBulk(
        string $table,
        iterable $items,
        array $options = []
    ): StatementInterface {
        $options = array_merge(
            [
                'incrementField' => false,
                'filterFields' => false,
            ],
            $options
        );

        $columnRegistered = false;
        $query = $this->db->createQuery()
            ->insert($table, $options['incrementField']);

        foreach ($items as $i => $data) {
            $fields = [];
            $values = [];

            $item = TypeCast::toArray($data);

            if ($options['filterFields']) {
                $item = $this->filterFields($table, $item);
            }

            // Iterate over the object variables to build the query fields and values.
            foreach ($item as $k => $v) {
                // Prepare and sanitize the fields and values for the database query.
                $fields[] = $k;
                $values[] = $v;
            }

            // Create the base insert statement.
            if (!$columnRegistered) {
                $query->columns(...$fields);
                $columnRegistered = true;
            }

            $query->values($values);
        }

        // Set the query and execute the insert.
        return $this->execute($query);
    }

    /**
     * updateMultiple
     *
     * @param  string        $table  The name of the database table to update.
     * @param  array         $items  A reference to an object whose public properties match the table fields.
     * @param  array|string  $key    The name of the primary key.
     * @param  array         $options
     *
     * @return StatementInterface[]
     * @throws JsonException
     */
    public function updateMultiple(string $table, iterable $items, array|string $key, array $options = []): array
    {
        $result = [];

        foreach ($items as $k => $item) {
            $result[$k] = $this->updateOne($table, $item, $key, $options);
        }

        return $result;
    }

    /**
     * saveMultiple
     *
     * @param  string             $table  The name of the database table to update.
     * @param  array              $items  A reference to an object whose public properties match the table fields.
     * @param  array|string|null  $key    The name of the primary key.
     * @param  array              $options
     *
     * @return array
     * @throws JsonException
     */
    public function saveMultiple(
        string $table,
        iterable $items,
        array|string|null $key,
        array $options = []
    ): array {
        $result = [];

        foreach ($items as $k => $item) {
            $result[$k] = $this->saveOne($table, $item, $key, $options);
        }

        return $result;
    }

    /**
     * Batch update some data.
     *
     * @param  string  $table       Table name.
     * @param  array   $data        Data you want to update.
     * @param  mixed   $conditions  Where conditions, you can use array or Compare object.
     *                              Example:
     *                              - `['id' => 5]` => id = 5
     *
     * @param  array   $options
     *
     * @return StatementInterface|null
     */
    public function updateWhere(string $table, array $data, $conditions = [], array $options = []): ?StatementInterface
    {
        $options = array_merge(
            [
                'filterFields' => false,
            ],
            $options
        );

        $query = $this->db->getQuery(true);

        // Build conditions
        $query = Query::convertAllToWheres($query, $conditions);

        // Build update values.
        if ($options['filterFields']) {
            $data = $this->filterFields($table, $data);
        }

        foreach ($data as $field => $value) {
            $query->set($field, $value);
        }

        if (!$query->getSet()) {
            return null;
        }

        $query->update($table);

        return $this->execute($query);
    }

    /**
     * delete
     *
     * @param  string  $table
     * @param  array   $conditions
     *
     * @return  StatementInterface
     */
    public function delete(string $table, array $conditions = []): StatementInterface
    {
        $query = $this->db->getQuery(true);

        // Conditions.
        $query = Query::convertAllToWheres($query, $conditions);

        $query->delete($table);

        return $this->execute($query);
    }

    /**
     * Get the number of affected rows for the previous executed SQL statement.
     * Only applicable for DELETE, INSERT, or UPDATE statements.
     *
     * @return  integer  The number of affected rows.
     *
     * @since   2.0
     */
    public function countAffected(): int
    {
        return $this->getStatement()?->countAffected() ?? 0;
    }

    /**
     * Method to get the auto-incremented value from the last INSERT statement.
     *
     * @param  string|null  $sequence
     *
     * @return string|null The value of the auto-increment field from the last inserted row.
     *
     * @since   2.0
     */
    public function lastInsertId(?string $sequence = null): ?string
    {
        return $this->getStatement()?->lastInsertId($sequence);
    }

    /**
     * execute
     *
     * @param  string|Query  $query
     *
     * @return  StatementInterface
     */
    public function execute(Query|string $query): StatementInterface
    {
        return $this->statement = $this->db->execute($query);
    }

    /**
     * Method to get property Db
     *
     * @return  DatabaseAdapter
     */
    public function getDb(): DatabaseAdapter
    {
        return $this->db;
    }

    /**
     * Method to get property Cursor
     *
     * @return  ?StatementInterface
     */
    public function getStatement(): ?StatementInterface
    {
        return $this->statement;
    }

    /**
     * Method to set property cursor
     *
     * @param  ?StatementInterface  $statement
     *
     * @return  static  Return self to support chaining.
     */
    public function setStatement(?StatementInterface $statement): static
    {
        $this->statement = $statement;

        return $this;
    }

    /**
     * filterfields
     *
     * @param  string  $table
     * @param  array   $item
     *
     * @return  array
     */
    public function filterFields(string $table, array $item): array
    {
        $schema = $this->db->getTableManager($table)->getSchema();
        $tableManager = $schema->getTable($table);

        if (!$tableManager) {
            throw new RuntimeException(
                sprintf(
                    'Table: %s not exists in Schema: %s',
                    $table,
                    $schema->getName()
                )
            );
        }

        return Arr::only($item, $tableManager->getColumnNames());
    }
}
