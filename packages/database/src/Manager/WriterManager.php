<?php

declare(strict_types=1);

namespace Windwalker\Database\Manager;

use InvalidArgumentException;
use JetBrains\PhpStorm\ArrayShape;
use JsonException;
use RuntimeException;
use Windwalker\Database\DatabaseAdapter;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Database\Platform\AbstractPlatform;
use Windwalker\Query\Query;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\TypeCast;

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
     * @param  string        $table  The name of the database table to insert into.
     * @param  array|object  &$data  A reference to an object whose public properties match the table fields.
     * @param  string|null   $key    The name of the primary key. If provided the object property is updated.
     * @param  array{
     *     incrementField?: bool|string,
     *     filterFields?: bool,
     * }  $options  Options.
     *
     * @return  array|object
     *
     * @throws  JsonException
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
     * @param  string        $table  The name of the database table to update.
     * @param  array|object  $data   A reference to an object whose public properties match the table fields.
     * @param  array|string  $key    The name of the primary key.
     * @param  array{
     *     updateNulls?: bool,
     *     filterFields?: bool|array,
     *     toJson?: bool,
     *     lockCallback?: (callable(Query): void)|null,
     * }  $options  Options.
     *
     * @return  StatementInterface
     *
     * @throws  JsonException
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

        $item = $origin = TypeCast::toArray($data);

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
            $query->where($k, $origin[$k] ?? null);
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
     * @param  array         $options  Same as insertOne() and updateOne() options.
     *
     * @return  mixed
     * @throws  JsonException
     */
    public function saveOne(string $table, array|object $data, ?string $key, array $options = []): mixed
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

    /**
     * @param  string        $table
     * @param  array|object  $data
     * @param  array|string  $keys
     * @param  array|null    $updateFields
     * @param  array{
     *     updateNulls?: bool,
     *     returningIdField?: string|null,
     * } $options
     *
     * @return  array|object  The inserted or updated data with returning ID.
     */
    public function upsert(
        string $table,
        array|object $data,
        array|string $keys,
        ?array $updateFields = null,
        #[ArrayShape([
            'updateNulls' => 'bool',
            'returningIdField' => 'string|null',
        ])]
        array $options = [],
    ): array|object {
        $keys = (array) $keys;

        $platformName = $this->db->getPlatform()->getName();
        $updateNulls = $options['updateNulls'] ?? true;
        $returningIdField = $options['returningIdField'] ?? null;

        if (!$updateNulls) {
            $data = array_filter(TypeCast::toArray($data), fn($v) => $v !== null);
        }

        $query = $this->db->createQuery();

        $originData = $data;
        $data = TypeCast::toArray($originData);
        $allFields = array_keys($data);

        $updateFields ??= array_filter($allFields, fn($field) => !in_array($field, $keys, true));

        // Nothing to update (e.g. all non-key values are null): re-assign keys so the statement stays valid
        // and still yields the row ID.
        if ($updateFields === []) {
            $updateFields = $keys;
        }

        if (
            $platformName === AbstractPlatform::SQLSERVER
            && version_compare($this->db->getDriver()->getVersion(), '10', '<')
        ) {
            throw new RuntimeException('Upsert is only supported in SQL Server 2008 (10) and later versions.');
        }

        $query->upsert($table, $returningIdField !== null && in_array($returningIdField, $keys, true))
            ->set($data)
            ->onConflict($keys)
            ->onConflictUpdateColumns(...array_values($updateFields))
            ->returning($returningIdField);

        $statement = $query->execute();

        if ($returningIdField) {
            $id = match ($platformName) {
                AbstractPlatform::MYSQL => $statement->lastInsertId(),
                // Oracle MERGE has no RETURNING clause, so look the row up by its keys.
                AbstractPlatform::ORACLE => $this->db->createQuery()
                    ->select($returningIdField)
                    ->from($table)
                    ->tap(
                        static function ($q) use ($keys, $data) {
                            foreach ($keys as $key) {
                                $q->where($key, $data[$key]);
                            }
                        }
                    )
                    ->result(),
                default => $statement->result(),
            };

            if (is_array($originData)) {
                $originData[$returningIdField] = $id;
            } else {
                $originData->$returningIdField = $id;
            }
        }

        return $originData;
    }

    /**
     * insertMultiple
     *
     * @param  string       $table    The name of the database table to update.
     * @param  array        $items    A reference to an object whose public properties match the table fields.
     * @param  string|null  $key      The name of the primary key.
     * @param  array        $options  See insertOne().
     *
     * @return  array[]|object[]
     * @throws  JsonException
     */
    public function insertMultiple(string $table, iterable $items, ?string $key = null, array $options = []): array
    {
        $result = [];

        foreach ($items as $k => $item) {
            $result[$k] = $this->insertOne($table, $item, $key, $options);
        }

        return $result;
    }

    /**
     * @param  array{
     *     incrementField?: bool|string,
     *     filterFields?: bool,
     * }  $options
     */
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
     * @param  string        $table    The name of the database table to update.
     * @param  array         $items    A reference to an object whose public properties match the table fields.
     * @param  array|string  $key      The name of the primary key.
     * @param  array         $options  See updateOne().
     *
     * @return  StatementInterface[]
     * @throws  JsonException
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
     * @param  string             $table    The name of the database table to update.
     * @param  array              $items    A reference to an object whose public properties match the table fields.
     * @param  array|string|null  $key      The name of the primary key.
     * @param  array              $options  See insertOne() and updateOne().
     *
     * @return  array
     * @throws  JsonException
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
     * @param  array{filterFields?: bool}  $options
     *
     * @return  StatementInterface|null
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
     * @return  string|null  The value of the auto-increment field from the last inserted row.
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
     * @param  string  $table
     * @param  array   $item
     *
     * @return  array
     */
    public function filterFields(string $table, array $item): array
    {
        $schema = $this->db->getTableManager($table)->getSchema();
        $tableManager = $schema->getTable($table);

        return Arr::only($item, $tableManager->getColumnNames());
    }
}
