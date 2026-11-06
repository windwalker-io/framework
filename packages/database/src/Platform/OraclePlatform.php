<?php

declare(strict_types=1);

namespace Windwalker\Database\Platform;

use Windwalker\Database\Driver\ConnectionInterface;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Database\Schema\Ddl\Column;
use Windwalker\Database\Schema\Ddl\Constraint;
use Windwalker\Database\Schema\Schema;
use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Query;
use Windwalker\Utilities\Wrapper\RawWrapper;

use function Windwalker\raw;

/**
 * The OraclePlatform class.
 *
 * Oracle folds unquoted identifiers to upper case and the Oracle grammar quotes names in upper case,
 * so all names are handled case-insensitively: queries use upper case, results are returned in lower case.
 * An Oracle "database" is a schema (user) inside the connected service, so schema and database are the same thing.
 */
class OraclePlatform extends AbstractPlatform
{
    protected string $name = self::ORACLE;

    protected static ?string $defaultSchema = null;

    protected const string CURRENT_SCHEMA = 'SYS_CONTEXT(\'USERENV\', \'CURRENT_SCHEMA\')';

    public function listDatabasesQuery(): Query
    {
        return $this->createQuery()
            ->select('USERNAME')
            ->from('ALL_USERS')
            ->where('ORACLE_MAINTAINED', 'N')
            ->order('USERNAME');
    }

    public function listSchemaQuery(): Query
    {
        return $this->listDatabasesQuery();
    }

    public function listDatabases(): array
    {
        return array_map('strtolower', parent::listDatabases());
    }

    public function listSchemas(): array
    {
        return array_map('strtolower', parent::listSchemas());
    }

    protected function whereOwner(Query $query, string $column, ?string $schema): void
    {
        if ($schema !== null) {
            $query->where($column, strtoupper($schema));
        } else {
            $query->where($column, raw(static::CURRENT_SCHEMA));
        }
    }

    protected function normalizeTableName(string $table): string
    {
        return strtoupper($this->db->replacePrefix($table));
    }

    public function listTablesQuery(?string $schema): Query
    {
        return $this->createQuery()
            ->select(
                [
                    'TABLE_NAME',
                    'OWNER AS TABLE_SCHEMA',
                    raw('\'BASE TABLE\' AS TABLE_TYPE'),
                    raw('NULL AS VIEW_DEFINITION'),
                    raw('NULL AS CHECK_OPTION'),
                    raw('NULL AS IS_UPDATABLE'),
                ]
            )
            ->from('ALL_TABLES')
            ->where('NESTED', 'NO')
            ->whereRaw(
                'NOT EXISTS (SELECT 1 FROM ALL_OBJECTS O WHERE O.OWNER = ALL_TABLES.OWNER'
                . " AND O.OBJECT_NAME = ALL_TABLES.TABLE_NAME AND O.OBJECT_TYPE = 'TABLE'"
                . " AND O.ORACLE_MAINTAINED = 'Y')"
            )
            ->tap(fn(Query $query) => $this->whereOwner($query, 'OWNER', $schema))
            ->order('TABLE_NAME');
    }

    public function listViewsQuery(?string $schema): Query
    {
        return $this->createQuery()
            ->select(
                [
                    'VIEW_NAME AS TABLE_NAME',
                    'OWNER AS TABLE_SCHEMA',
                    raw('\'VIEW\' AS TABLE_TYPE'),
                    raw('NULL AS VIEW_DEFINITION'),
                    raw('NULL AS CHECK_OPTION'),
                    raw('NULL AS IS_UPDATABLE'),
                ]
            )
            ->from('ALL_VIEWS')
            ->tap(fn(Query $query) => $this->whereOwner($query, 'OWNER', $schema))
            ->order('VIEW_NAME');
    }

    public function listTables(?string $schema = null, bool $includeViews = false): array
    {
        $tables = $this->normalizeTableRows($this->listTablesQuery($schema)->all()->dump());

        if ($includeViews) {
            $tables = array_merge($tables, $this->listViews($schema));
        }

        return $tables;
    }

    public function listViews(?string $schema = null): array
    {
        return $this->normalizeTableRows($this->listViewsQuery($schema)->all()->dump());
    }

    /**
     * @param  array<array>  $rows
     *
     * @return  array<string, array>
     */
    protected function normalizeTableRows(array $rows): array
    {
        $tables = [];

        foreach ($rows as $row) {
            $name = strtolower($row['table_name']);

            $tables[$name] = [
                'TABLE_NAME' => $name,
                'TABLE_SCHEMA' => strtolower($row['table_schema']),
                'TABLE_TYPE' => $row['table_type'],
                'VIEW_DEFINITION' => $row['view_definition'],
                'CHECK_OPTION' => $row['check_option'],
                'IS_UPDATABLE' => $row['is_updatable'],
            ];
        }

        return $tables;
    }

    public function listColumnsQuery(string $table, ?string $schema): Query
    {
        return $this->createQuery()
            ->select(
                [
                    'c.COLUMN_NAME',
                    'c.COLUMN_ID',
                    'c.DATA_DEFAULT',
                    'c.NULLABLE',
                    'c.DATA_TYPE',
                    'c.DATA_LENGTH',
                    'c.CHAR_LENGTH',
                    'c.CHAR_USED',
                    'c.DATA_PRECISION',
                    'c.DATA_SCALE',
                    'c.IDENTITY_COLUMN',
                    'c.VIRTUAL_COLUMN',
                    'cc.COMMENTS',
                ]
            )
            ->from('ALL_TAB_COLS', 'c')
            ->leftJoin(
                'ALL_COL_COMMENTS',
                'cc',
                [
                    ['cc.OWNER', '=', 'c.OWNER'],
                    ['cc.TABLE_NAME', '=', 'c.TABLE_NAME'],
                    ['cc.COLUMN_NAME', '=', 'c.COLUMN_NAME'],
                ]
            )
            ->where('c.TABLE_NAME', $this->normalizeTableName($table))
            ->where('c.HIDDEN_COLUMN', 'NO')
            ->tap(fn(Query $query) => $this->whereOwner($query, 'c.OWNER', $schema))
            ->order('c.COLUMN_ID');
    }

    public function listColumns(string $table, ?string $schema = null): array
    {
        $columns = [];

        foreach ($this->loadColumnsStatement($table, $schema) as $row) {
            $isIdentity = $row['identity_column'] === 'YES';
            [$type, $length, $precision, $scale] = $this->resolveColumnType($row);

            $default = $row['data_default'];

            if ($default !== null && !$isIdentity) {
                $default = trim((string) $default);
                $default = preg_replace('/^\'(.*)\'$/s', '$1', $default);
                $default = preg_replace('/^(?:TIMESTAMP|DATE)\s*\'(.*)\'$/is', '$1', $default);
            } else {
                $default = null;
            }

            $name = strtolower($row['column_name']);

            $columns[$name] = [
                'column_name' => $name,
                'ordinal_position' => $row['column_id'],
                'column_default' => $default,
                'is_nullable' => $row['nullable'] === 'Y',
                'data_type' => $type,
                'character_maximum_length' => $length,
                'character_octet_length' => $length === null ? null : $row['data_length'],
                'numeric_precision' => $precision,
                'numeric_scale' => $scale,
                'numeric_unsigned' => false,
                'comment' => (string) $row['comments'],
                'auto_increment' => $isIdentity,
                'erratas' => [],
            ];
        }

        return $columns;
    }

    /**
     * Convert Oracle native types back to the generic type names.
     *
     * @param  \ArrayAccess|array  $row
     *
     * @return  array{0: string, 1: int|null, 2: int|null, 3: int|null}
     */
    protected function resolveColumnType(\ArrayAccess|array $row): array
    {
        $type = strtolower(preg_replace('/\(\d+\).*$/', '', (string) $row['data_type']));
        $length = null;
        $precision = $row['data_precision'] !== null ? (int) $row['data_precision'] : null;
        $scale = $row['data_scale'] !== null ? (int) $row['data_scale'] : null;

        if (str_starts_with($type, 'timestamp')) {
            return ['timestamp', $scale, null, null];
        }

        switch ($type) {
            case 'varchar2':
                $type = 'varchar';
                $length = (int) $row['char_length'];
                break;

            case 'nvarchar2':
                $type = 'nvarchar';
                $length = (int) $row['char_length'];
                break;

            case 'char':
            case 'nchar':
                $length = (int) $row['char_length'];
                break;

            case 'clob':
            case 'nclob':
            case 'long':
                $type = 'text';
                break;

            case 'binary_double':
                $type = 'double';
                break;

            case 'binary_float':
                $type = 'float';
                break;

            case 'number':
                if ($scale === 0 && $precision !== null) {
                    $type = match ($precision) {
                        1 => 'boolean',
                        3 => 'tinyint',
                        5 => 'smallint',
                        10 => 'int',
                        19 => 'bigint',
                        default => 'number',
                    };
                } elseif ($scale !== null && $scale > 0) {
                    $type = 'decimal';
                } elseif ($precision === null && $scale === null) {
                    $type = 'number';
                }
                break;
        }

        return [$type, $length, $precision, $scale];
    }

    public function listConstraintsQuery(string $table, ?string $schema): Query
    {
        return $this->createQuery()
            ->select(
                [
                    'c.TABLE_NAME',
                    'c.CONSTRAINT_NAME',
                    raw(
                        "CASE c.CONSTRAINT_TYPE WHEN 'P' THEN 'PRIMARY KEY' WHEN 'U' THEN 'UNIQUE'"
                        . " WHEN 'R' THEN 'FOREIGN KEY' ELSE 'CHECK' END AS CONSTRAINT_TYPE"
                    ),
                    'cc.COLUMN_NAME',
                    'c.SEARCH_CONDITION_VC AS CHECK_CLAUSE',
                    raw("'NONE' AS MATCH_OPTION"),
                    raw("'NO ACTION' AS UPDATE_RULE"),
                    'c.DELETE_RULE',
                    'rc.OWNER AS REFERENCED_TABLE_SCHEMA',
                    'rc.TABLE_NAME AS REFERENCED_TABLE_NAME',
                    'rcc.COLUMN_NAME AS REFERENCED_COLUMN_NAME',
                ]
            )
            ->from('ALL_CONSTRAINTS', 'c')
            ->leftJoin(
                'ALL_CONS_COLUMNS',
                'cc',
                [
                    ['cc.OWNER', '=', 'c.OWNER'],
                    ['cc.CONSTRAINT_NAME', '=', 'c.CONSTRAINT_NAME'],
                ]
            )
            ->leftJoin(
                'ALL_CONSTRAINTS',
                'rc',
                [
                    ['rc.OWNER', '=', 'c.R_OWNER'],
                    ['rc.CONSTRAINT_NAME', '=', 'c.R_CONSTRAINT_NAME'],
                ]
            )
            ->leftJoin(
                'ALL_CONS_COLUMNS',
                'rcc',
                [
                    ['rcc.OWNER', '=', 'rc.OWNER'],
                    ['rcc.CONSTRAINT_NAME', '=', 'rc.CONSTRAINT_NAME'],
                    ['rcc.POSITION', '=', 'cc.POSITION'],
                ]
            )
            ->where('c.TABLE_NAME', $this->normalizeTableName($table))
            ->where('c.CONSTRAINT_TYPE', 'IN', ['P', 'U', 'R', 'C'])
            ->whereRaw("NOT (c.CONSTRAINT_TYPE = 'C' AND c.SEARCH_CONDITION_VC LIKE '%IS NOT NULL')")
            ->tap(fn(Query $query) => $this->whereOwner($query, 'c.OWNER', $schema))
            ->order(
                raw(
                    "CASE c.CONSTRAINT_TYPE WHEN 'P' THEN 1 WHEN 'U' THEN 2 WHEN 'R' THEN 3 WHEN 'C' THEN 4"
                    . ' ELSE 5 END, c.CONSTRAINT_NAME, cc.POSITION'
                )
            );
    }

    public function listConstraints(string $table, ?string $schema = null): array
    {
        $constraints = [];

        foreach ($this->loadConstraintsStatement($table, $schema) as $row) {
            $name = strtolower($row['constraint_name']);
            $type = $row['constraint_type'];

            if (!isset($constraints[$name])) {
                $constraints[$name] = [
                    'constraint_name' => $name,
                    'constraint_type' => $type,
                    'table_name' => strtolower($row['table_name']),
                    'columns' => [],
                ];

                if ($type === 'CHECK') {
                    $constraints[$name]['check_clause'] = $row['check_clause'];
                    continue;
                }

                if ($type === 'FOREIGN KEY') {
                    $constraints[$name]['referenced_table_schema'] = strtolower($row['referenced_table_schema']);
                    $constraints[$name]['referenced_table_name'] = strtolower($row['referenced_table_name']);
                    $constraints[$name]['referenced_columns'] = [];
                    $constraints[$name]['match_option'] = $row['match_option'];
                    $constraints[$name]['update_rule'] = $row['update_rule'];
                    $constraints[$name]['delete_rule'] = $row['delete_rule'];
                }
            }

            if ($type === 'CHECK') {
                continue;
            }

            $constraints[$name]['columns'][] = strtolower($row['column_name']);

            if ($type === 'FOREIGN KEY') {
                $constraints[$name]['referenced_columns'][] = strtolower($row['referenced_column_name']);
            }
        }

        return $constraints;
    }

    public function listIndexesQuery(string $table, ?string $schema): Query
    {
        return $this->createQuery()
            ->select(
                [
                    'i.TABLE_OWNER AS SCHEMA_NAME',
                    'i.TABLE_NAME',
                    'i.INDEX_NAME',
                    'i.UNIQUENESS',
                    'ic.COLUMN_NAME',
                    'ic.COLUMN_POSITION',
                    raw(
                        '(SELECT COUNT(*) FROM ALL_CONSTRAINTS pc WHERE pc.OWNER = i.OWNER'
                        . " AND pc.INDEX_NAME = i.INDEX_NAME AND pc.CONSTRAINT_TYPE = 'P') AS IS_PRIMARY"
                    ),
                ]
            )
            ->from('ALL_INDEXES', 'i')
            ->innerJoin(
                'ALL_IND_COLUMNS',
                'ic',
                [
                    ['ic.INDEX_OWNER', '=', 'i.OWNER'],
                    ['ic.INDEX_NAME', '=', 'i.INDEX_NAME'],
                ]
            )
            ->where('i.TABLE_NAME', $this->normalizeTableName($table))
            ->where('i.INDEX_TYPE', '!=', 'LOB')
            ->tap(fn(Query $query) => $this->whereOwner($query, 'i.TABLE_OWNER', $schema))
            ->order('i.INDEX_NAME')
            ->order('ic.COLUMN_POSITION');
    }

    public function listIndexes(string $table, ?string $schema = null): array
    {
        $indexes = [];

        foreach ($this->loadIndexesStatement($table, $schema) as $row) {
            $name = strtolower($row['index_name']);

            if (!isset($indexes[$name])) {
                $indexes[$name] = [
                    'table_schema' => strtolower($row['schema_name']),
                    'table_name' => strtolower($row['table_name']),
                    'is_unique' => $row['uniqueness'] === 'UNIQUE',
                    'is_primary' => (bool) $row['is_primary'],
                    'index_name' => $name,
                    'index_comment' => '',
                    'columns' => [],
                ];
            }

            $column = strtolower($row['column_name']);

            $indexes[$name]['columns'][$column] = [
                'column_name' => $column,
                'sub_part' => null,
            ];
        }

        return $indexes;
    }

    public function transactionStart(): static
    {
        if (!$this->depth) {
            parent::transactionStart();
        } else {
            $this->db->execute('SAVEPOINT ' . $this->db->quoteName('SP_' . $this->depth));

            $this->depth++;
        }

        return $this;
    }

    public function transactionCommit(bool $releaseConnection = true): static
    {
        if ($this->depth <= 1) {
            parent::transactionCommit($releaseConnection);
        } else {
            $this->depth--;
        }

        return $this;
    }

    public function releaseKeptConnection(): ?ConnectionInterface
    {
        if ($this->depth <= 1) {
            return parent::releaseKeptConnection();
        }

        return null;
    }

    public function transactionRollback(bool $releaseConnection = true): static
    {
        if ($this->depth <= 1) {
            parent::transactionRollback($releaseConnection);
        } else {
            $this->db->execute('ROLLBACK TO SAVEPOINT ' . $this->db->quoteName('SP_' . ($this->depth - 1)));

            $this->depth--;
        }

        return $this;
    }

    public function getCurrentDatabase(): ?string
    {
        $schema = $this->db->prepare('SELECT ' . static::CURRENT_SCHEMA . ' FROM DUAL')->result();

        return $schema === null ? null : strtolower((string) $schema);
    }

    /**
     * Create a schema (Oracle user). Use the "password" option to set the password.
     */
    public function createDatabase(string $name, array $options = []): StatementInterface
    {
        return $this->createSchema($name, $options);
    }

    public function dropDatabase(string $name, array $options = []): StatementInterface
    {
        return $this->dropSchema($name, $options);
    }

    public function createSchema(string $name, array $options = []): StatementInterface
    {
        return $this->db->execute(
            $this->getGrammar()::build(
                'CREATE USER',
                $this->db->quoteName($name),
                'IDENTIFIED BY',
                $this->db->quoteName($options['password'] ?? $name),
                'QUOTA UNLIMITED ON USERS'
            )
        );
    }

    public function dropSchema(string $name, array $options = []): StatementInterface
    {
        return $this->db->execute(
            $this->getGrammar()::build(
                'DROP USER',
                $this->db->quoteName($name),
                'CASCADE'
            )
        );
    }

    public function createTable(Schema $schema, bool $ifNotExists = false, array $options = []): StatementInterface
    {
        $columns = [];
        $table = $schema->getTable();
        $tableName = $this->db->quoteName($table->schemaName . '.' . $table->getName());
        $comments = [];
        $primaries = [];

        foreach ($schema->getColumns() as $column) {
            $column = $this->prepareColumn(clone $column);

            if ($column->isPrimary()) {
                $primaries[] = $column;
            }

            $columns[$column->getColumnName()] = $this->getColumnExpression($column)
                ->setName($this->db->quoteName($column->getColumnName()));

            if ($column->getComment()) {
                $comments[$column->columnName] = $column->getComment();
            }
        }

        $sql = $this->getGrammar()::build(
            'CREATE TABLE',
            $tableName,
            "(\n" . implode(",\n", $columns) . "\n)",
            $this->getGrammar()::buildConfig([])
        );

        if ($ifNotExists) {
            $statement = $this->executeIgnoringError($sql, 955);
        } else {
            $statement = $this->db->execute($sql);
        }

        $constraints = $schema->getConstraints();

        if ($primaries) {
            array_unshift(
                $constraints,
                new Constraint(
                    Constraint::TYPE_PRIMARY_KEY,
                    'pk_' . $table->getName(),
                    $table->getName()
                )
                    ->columns($primaries)
            );
        }

        $this->postTableModify($table, $schema->getIndexes(), $constraints, $comments);

        return $statement;
    }

    /**
     * Execute a statement inside a PL/SQL block and ignore the given ORA error code.
     */
    protected function executeIgnoringError(string|\Stringable $sql, int $oraCode): StatementInterface
    {
        $literal = str_replace("'", "''", (string) $sql);

        return $this->db->execute(
            "BEGIN EXECUTE IMMEDIATE '$literal'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -$oraCode THEN RAISE; END IF; END;"
        );
    }

    public function dropTable(string $table, ?string $schema = null, $suffix = null): StatementInterface
    {
        return $this->executeIgnoringError(
            $this->getGrammar()::build(
                'DROP TABLE',
                $this->db->quoteName($schema . '.' . $table),
                'CASCADE CONSTRAINTS PURGE',
                $suffix
            ),
            942
        );
    }

    public function renameTable(string $from, string $to, ?string $schema = null): StatementInterface
    {
        return $this->db->execute(
            $this->getGrammar()::build(
                'ALTER TABLE',
                $this->db->quoteName($schema . '.' . $from),
                'RENAME TO',
                $this->db->quoteName($to),
            )
        );
    }

    public function addColumn(string $table, Column $column, ?string $schema = null): StatementInterface
    {
        return $this->db->execute(
            $this->getGrammar()::build(
                'ALTER TABLE',
                $this->db->quoteName($schema . '.' . $table),
                'ADD',
                $this->db->quoteName($column->getColumnName()),
                (string) $this->getColumnExpression($column)
            )
        );
    }

    public function dropColumn(string $table, string $name, ?string $schema = null): StatementInterface
    {
        return $this->db->execute(
            $this->getGrammar()::build(
                'ALTER TABLE',
                $this->db->quoteName($schema . '.' . $table),
                'DROP COLUMN',
                $this->db->quoteName($name),
                'CASCADE CONSTRAINTS',
            )
        );
    }

    public function modifyColumn(string $table, Column $column, ?string $schema = null): StatementInterface
    {
        $current = $this->listColumns($table, $schema)[strtolower($column->getColumnName())] ?? null;
        $nullable = $column->getIsNullable();

        $expression = $this->getGrammar()::build(
            $column->getTypeExpression($this->getDataType()),
            $column->canHasDefaultValue() ? $this->getDefaultExpression($column) : null,
            $current === null || $current['is_nullable'] !== $nullable
                ? ($nullable ? 'NULL' : 'NOT NULL')
                : null,
        );

        $stmt = $this->db->execute(
            $this->getGrammar()::build(
                'ALTER TABLE',
                $this->db->quoteName($schema . '.' . $table),
                'MODIFY',
                $this->db->quoteName($column->getColumnName()),
                (string) $expression
            )
        );

        if ($column->getComment()) {
            $this->setColumnComment($table, $column->getColumnName(), $column->getComment(), $schema);
        }

        return $stmt;
    }

    public function renameColumn(string $table, string $from, string $to, ?string $schema = null): StatementInterface
    {
        return $this->db->execute(
            $this->getGrammar()::build(
                'ALTER TABLE',
                $this->db->quoteName($schema . '.' . $table),
                'RENAME COLUMN',
                $this->db->quoteName($from),
                'TO',
                $this->db->quoteName($to),
            )
        );
    }

    public function setColumnComment(
        string $table,
        string $column,
        string $comment,
        ?string $schema = null
    ): ?StatementInterface {
        return $this->db->execute(
            $this->getGrammar()::build(
                'COMMENT ON COLUMN',
                $this->db->quoteName($schema . '.' . $table . '.' . $column),
                'IS',
                $this->db->quote($comment)
            )
        );
    }

    public function getColumnExpression(Column $column): Clause
    {
        if ($column->isGenerated()) {
            return $this->getGrammar()::build(
                $column->getTypeExpression($this->getDataType()),
                'GENERATED ALWAYS AS (' . $column->getGenerationExpression() . ') VIRTUAL'
            );
        }

        if ($column->isAutoIncrement()) {
            return $this->getGrammar()::build(
                $column->getTypeExpression($this->getDataType()),
                'GENERATED BY DEFAULT AS IDENTITY',
                $column->getIsNullable() ? '' : 'NOT NULL',
                $column->getOption('suffix')
            );
        }

        return $this->getGrammar()::build(
            $column->getTypeExpression($this->getDataType()),
            $column->canHasDefaultValue() ? $this->getDefaultExpression($column) : '',
            $this->getCollateExpression($column),
            $column->getIsNullable() ? '' : 'NOT NULL',
            $column->getOption('suffix')
        );
    }

    /**
     * Oracle treats an empty string as NULL, so an empty default is omitted.
     */
    protected function getDefaultExpression(Column $column): ?string
    {
        $default = $column->getColumnDefault();

        if ($default === '' || $default === false) {
            return null;
        }

        $type = $column->getDataType();

        // Raw SQL expressions (e.g. defaultCurrent()) must not get a type literal prefix.
        if ($default instanceof RawWrapper) {
            return 'DEFAULT ' . $default();
        }

        if ($default !== null && $type === 'timestamp') {
            return 'DEFAULT TIMESTAMP ' . $this->db->quote($default);
        }

        if ($default !== null && $type === 'date') {
            return 'DEFAULT DATE ' . $this->db->quote($default);
        }

        return 'DEFAULT ' . $this->db->quote($default);
    }

    public function addConstraint(string $table, Constraint $constraint, ?string $schema = null): StatementInterface
    {
        if ($constraint->constraintType !== Constraint::TYPE_FOREIGN_KEY) {
            return parent::addConstraint($table, $constraint, $schema);
        }

        // Oracle only supports ON DELETE CASCADE | SET NULL
        $deleteRule = strtoupper((string) $constraint->deleteRule);

        $alter = $this->db->createQuery()
            ->alter('TABLE', $schema . '.' . $table)
            ->addForeignKey(
                $constraint->constraintName,
                $this->prepareKeyColumns($constraint->getColumns()),
                $this->prepareKeyColumns($constraint->getReferencedColumns()),
                null,
                in_array($deleteRule, ['CASCADE', 'SET NULL'], true) ? $deleteRule : null,
            );

        return $this->db->execute((string) $alter);
    }

    public function dropIndex(string $table, string $name, ?string $schema = null): StatementInterface
    {
        return $this->db->execute(
            $this->getGrammar()::build(
                'DROP INDEX',
                $this->db->quoteName($schema . '.' . $name),
            )
        );
    }
}
