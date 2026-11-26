<?php

declare(strict_types=1);

namespace Windwalker\Database\Platform;

use LogicException;
use RuntimeException;
use Windwalker\Database\Driver\ConnectionInterface;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Database\Manager\TableManager;
use Windwalker\Database\Platform\Type\DataType;
use Windwalker\Database\Schema\Ddl\Column;
use Windwalker\Database\Schema\Ddl\Constraint;
use Windwalker\Database\Schema\Schema;
use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Escaper;
use Windwalker\Query\Query;
use Windwalker\Utilities\TypeCast;

use function Windwalker\raw;

/**
 * The SqlitePlatform class.
 */
class SQLitePlatform extends AbstractPlatform
{
    protected string $name = self::SQLITE;

    protected static ?string $defaultSchema = 'main';

    public function listDatabasesQuery(): Query
    {
        return $this->pragma('database_list');
    }

    public function listSchemaQuery(): Query
    {
        return $this->listDatabasesQuery();
    }

    public function listTablesQuery(?string $schema): Query
    {
        $schema = $schema ?? static::getDefaultSchema();

        return $this->createQuery()
            ->select(
                [
                    'name AS TABLE_NAME',
                    raw('\'BASE TABLE\' AS TABLE_TYPE'),
                    raw(sprintf('\'%s\' AS TABLE_SCHEMA', $schema)),
                    raw('NULL AS VIEW_DEFINITION'),
                    raw('NULL AS CHECK_OPTION'),
                    raw('NULL AS IS_UPDATABLE'),
                    'sql',
                ]
            )
            ->from($schema . '.sqlite_master')
            ->where('type', 'table')
            ->where('name', 'not like', 'sqlite_%')
            ->order('name');
    }

    public function listViewsQuery(?string $schema): Query
    {
        return $this->createQuery()
            ->select(
                [
                    'name AS TABLE_NAME',
                    raw('\'VIEW\' AS TABLE_TYPE'),
                    raw(sprintf('\'%s\' AS TABLE_SCHEMA', $schema)),
                    raw('NULL AS VIEW_DEFINITION'),
                    raw('\'NONE\' AS CHECK_OPTION'),
                    raw('NULL AS IS_UPDATABLE'),
                    'sql',
                ]
            )
            ->from(trim($schema . '.sqlite_master', '.'))
            ->where('type', 'view')
            ->where('name', 'not like', 'sqlite_%')
            ->order('name');
    }

    /**
     * @inheritDoc
     */
    public function listSchemas(): array
    {
        return $this->db->prepare(
            $this->listSchemaQuery()
        )
            ->loadColumn(1)
            ->dump();
    }

    public function listViews(?string $schema = null): array
    {
        $views = parent::listViews($schema);

        foreach ($views as $view) {
            preg_match('/create\s+view\s+.+AS\s([.\w\W]+)/mi', $view['sql'], $matches);

            $view['VIEW_DEFINITION'] = $matches[1] ?? null;
        }

        return $views;
    }

    public function listColumnsQuery(string $table, ?string $schema): Query
    {
        return $this->pragma('table_xinfo', $this->db->replacePrefix($table), $schema);
    }

    public function listConstraintsQuery(string $table, ?string $schema): Query
    {
        return $this->listIndexesQuery($table, $schema);
    }

    public function listIndexesQuery(string $table, ?string $schema): Query
    {
        return $this->pragma('index_list', $this->db->replacePrefix($table), $schema);
    }

    /**
     * @inheritDoc
     */
    public function listDatabases(): array
    {
        return $this->db->prepare(
            $this->listDatabasesQuery()
        )
            ->loadColumn(2)
            ->dump();
    }

    /**
     * @inheritDoc
     */
    public function listColumns(string $table, ?string $schema = null): array
    {
        $columns = [];

        $generated = null;

        foreach ($this->loadColumnsStatement($table, $schema) as $row) {
            // table_xinfo `hidden`: 1 = virtual table hidden column, 2 = VIRTUAL generated, 3 = STORED generated
            $hidden = (int) ($row['hidden'] ?? 0);

            if ($hidden === 1) {
                continue;
            }

            [$type, $precision, $scale] = DataType::extract($row['type']);

            $isString = in_array(
                $type = strtolower($type),
                [
                    'char',
                    'varchar',
                    'text',
                    'mediumtext',
                    'longtext',
                ]
            );

            $columns[$row['name']] = [
                'column_name' => $row['name'],
                // cid appears to be zero-based, ordinal position needs to be one-based
                'ordinal_position' => $row['cid'] + 1,
                'column_default' => Escaper::stripQuoteIfExists($row['dflt_value']),
                'is_nullable' => !$row['notnull'],
                'data_type' => $type,
                'character_maximum_length' => $isString ? TypeCast::tryInteger($precision, true) : null,
                'character_octet_length' => null,
                'numeric_precision' => $isString ? null : TypeCast::tryInteger($precision, true),
                'numeric_scale' => $isString ? null : TypeCast::tryInteger($scale, true),
                'numeric_unsigned' => false,
                'comment' => null,
                'auto_increment' => (bool) $row['pk'],
                ...($hidden >= 2 ? [
                    'generation_expression' => ($generated ??= $this->listGeneratedExpressions($table, $schema))[$row['name']]
                        ?? null,
                    'generated_stored' => $hidden === 3,
                ] : []),
                'erratas' => [
                    'pk' => (bool) $row['pk'],
                ],
            ];
        }

        return $columns;
    }

    /**
     * @inheritDoc
     */
    public function listConstraints(string $table, ?string $schema = null): array
    {
        $constraints = [];

        $columns = $this->listColumns($table, $schema);

        $primaryKey = [];

        foreach ($columns as $name => $column) {
            if ($column['erratas']['pk']) {
                $primaryKey[] = $name;
            }
        }

        foreach ($this->loadConstraintsStatement($table, $schema) as $row) {
            if (!$row['unique']) {
                continue;
            }

            $constraint = [
                'constraint_name' => $row['name'],
                'constraint_type' => 'UNIQUE',
                'table_name' => $this->db->replacePrefix($table),
                'columns' => [],
            ];

            $info = $this->db->prepare(
                $this->pragma('index_info', $row['name'], $schema)
            );

            foreach ($info as $column) {
                $constraint['columns'][] = $column['name'];
            }

            if ($primaryKey === $constraint['columns']) {
                $constraint['constraint_type'] = 'PRIMARY KEY';
                $primaryKey = null;
            }

            $constraints[$constraint['constraint_name']] = $constraint;
        }

        return $constraints;
    }

    /**
     * @inheritDoc
     */
    public function listIndexes(string $table, ?string $schema = null): array
    {
        $indexes = [];

        $columns = $this->listColumns($table, $schema);

        $primaryKey = [];

        foreach ($columns as $name => $column) {
            if ($column['erratas']['pk']) {
                $primaryKey[] = $name;
            }
        }

        foreach ($this->loadConstraintsStatement($table, $schema) as $row) {
            $index['table_schema'] = $schema;
            $index['table_name'] = $this->db->replacePrefix($table);
            $index['is_unique'] = (bool) $row['unique'];
            $index['index_name'] = $row['name'];
            $index['index_comment'] = '';

            $index['columns'] = [];

            $info = $this->db->prepare(
                $this->pragma('index_info', $row['name'], $schema)
            );

            $expressions = null;

            foreach ($info as $column) {
                $isExpression = $column['name'] === null;
                $colName = $column['name'];

                if ($isExpression) {
                    // Functional key parts have no column name, so the real expression is
                    // parsed back from the `CREATE INDEX` statement stored in `sqlite_master`.
                    $expressions ??= $this->listIndexExpressions($row['name'], $schema);
                    $colName = $expressions[$column['seqno']] ?? '(expression ' . $column['seqno'] . ')';
                }

                $index['columns'][$colName] = [
                    'column_name' => $colName,
                    'subpart' => null,
                    ...($isExpression ? ['erratas' => ['expression' => $colName]] : []),
                ];
            }

            if ($primaryKey === $index['columns']) {
                $index['is_primary'] = true;
                $primaryKey = null;
            }

            $indexes[$row['name']] = $index;
        }

        return $indexes;
    }

    /**
     * `PRAGMA table_xinfo` does not report generation expressions, parse them from the stored `CREATE TABLE`.
     *
     * @return  array<string, string>  Expression text keyed by column name.
     */
    protected function listGeneratedExpressions(string $table, ?string $schema): array
    {
        $schema ??= static::getDefaultSchema();

        $sql = (string) $this->db->prepare(
            $this->createQuery()
                ->select('sql')
                ->from(trim($schema . '.sqlite_master', '.'))
                ->where('type', 'table')
                ->where('name', $this->db->replacePrefix($table))
        )->result();

        $expressions = [];

        preg_match_all(
            '/(?:^|[\s,(])["`\[]?(\w+)["`\]]?\s[^(),]*?\bAS\s*\(/i',
            $sql,
            $matches,
            PREG_OFFSET_CAPTURE
        );

        foreach ($matches[1] as $i => [$name]) {
            $start = $matches[0][$i][1] + strlen($matches[0][$i][0]) - 1;
            $depth = 0;

            for ($j = $start, $len = strlen($sql); $j < $len; $j++) {
                if ($sql[$j] === '(') {
                    $depth++;
                } elseif ($sql[$j] === ')' && --$depth === 0) {
                    $expressions[$name] = trim(substr($sql, $start + 1, $j - $start - 1));
                    break;
                }
            }
        }

        return $expressions;
    }

    /**
     * Parse the real expression text of each key part from the `CREATE INDEX` statement stored
     * in `sqlite_master`, since `PRAGMA index_info` reports no column name for functional key parts.
     *
     * @return  string[]  Expression text keyed by the zero-based key part position (`seqno`).
     */
    protected function listIndexExpressions(string $indexName, ?string $schema): array
    {
        $schema ??= static::getDefaultSchema();

        $sql = (string) $this->db->prepare(
            $this->createQuery()
                ->select('sql')
                ->from(trim($schema . '.sqlite_master', '.'))
                ->where('type', 'index')
                ->where('name', $indexName)
        )->result();

        if ($sql === '' || !preg_match('/\((.*)\)\s*(?:WHERE\s+.*)?$/is', $sql, $matches)) {
            return [];
        }

        return array_map(
            fn(string $part) => $this->stripOuterParens(trim($part)),
            $this->splitIndexKeyParts($matches[1])
        );
    }

    /**
     * Split index key parts by top-level commas, expressions may contain commas in parentheses.
     *
     * @return  string[]
     */
    protected function splitIndexKeyParts(string $keys): array
    {
        $parts = [];
        $depth = 0;
        $current = '';

        foreach (str_split($keys) as $char) {
            if ($char === '(') {
                $depth++;
            } elseif ($char === ')') {
                $depth--;
            }

            if ($char === ',' && $depth === 0) {
                $parts[] = trim($current);
                $current = '';
                continue;
            }

            $current .= $char;
        }

        $parts[] = trim($current);

        return $parts;
    }

    /**
     * Remove one matching outer pair of parentheses added by `addIndex()` when wrapping an
     * expression key part, e.g. `(LOWER(email))` becomes `LOWER(email)`.
     */
    protected function stripOuterParens(string $expression): string
    {
        if (!str_starts_with($expression, '(') || !str_ends_with($expression, ')')) {
            return $expression;
        }

        $depth = 0;
        $length = strlen($expression);

        for ($i = 0; $i < $length; $i++) {
            if ($expression[$i] === '(') {
                $depth++;
            } elseif ($expression[$i] === ')') {
                $depth--;

                if ($depth === 0 && $i !== $length - 1) {
                    return $expression;
                }
            }
        }

        return substr($expression, 1, -1);
    }

    public function pragma(string $name, ?string $value = null, ?string $schema = null): Query
    {
        $query = $this->createQuery();

        $sql = 'PRAGMA ';

        if (null !== $schema) {
            $sql .= $query->quoteName($schema) . '.';
        }

        $sql .= $name;

        if (null !== $value) {
            $sql .= '(' . $query->quote($value) . ')';
        }

        return $query->sql($sql);
    }

    /**
     * start
     *
     * @return  static
     */
    public function transactionStart(): static
    {
        if (!$this->depth) {
            parent::transactionStart();
        } else {
            $savepoint = 'SP_' . $this->depth;
            $this->db->execute('SAVEPOINT ' . $this->db->quoteName($savepoint));

            $this->depth++;
        }

        return $this;
    }

    /**
     * commit
     *
     * @param  bool  $releaseConnection  *
     *
     * @return  static
     */
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

    /**
     * rollback
     *
     * @param  bool  $releaseConnection  *
     *
     * @return  static
     */
    public function transactionRollback(bool $releaseConnection = true): static
    {
        if ($this->depth <= 1) {
            parent::transactionRollback($releaseConnection);
        } else {
            $savepoint = 'SP_' . ($this->depth - 1);
            $this->db->execute('ROLLBACK TO SAVEPOINT ' . $this->db->quoteName($savepoint));

            $this->depth--;
        }

        return $this;
    }

    public function createDatabase(string $name, array $options = []): StatementInterface
    {
        $as = $options['as'] ?? pathinfo($name, PATHINFO_FILENAME);

        return $this->db->execute(
            $this->getGrammar()::build(
                'ATTACH DATABASE',
                $this->db->quote($name),
                'AS',
                $this->db->quoteName($as)
            )
        );
    }

    public function createSchema(string $name, array $options = []): StatementInterface
    {
        return $this->createDatabase($name, $options);
    }

    /**
     * getCurrentDatabase
     *
     * @return  string|null
     */
    public function getCurrentDatabase(): ?string
    {
        $databases = $this->db->prepare($this->pragma('database_list'))
            ->all()
            ->keyBy('name');

        return $databases[static::getDefaultSchema()]->file ?? null;
    }

    public function dropDatabase(string $name, array $options = []): StatementInterface
    {
        $databases = $this->db->prepare($this->pragma('database_list'))
            ->all()
            ->keyBy('file');

        if ($databases[$name]) {
            $dbname = $databases[$name]->name;

            return $this->db->execute(
                $this->getGrammar()::build(
                    'DETACH DATABASE',
                    $this->db->quoteName($dbname)
                )
            );
        }

        return $this->dropSchema($name, $options);
    }

    /**
     * dropSchema
     *
     * @param  string  $name
     *
     * @param  array  $options
     *
     * @return  StatementInterface
     */
    public function dropSchema(string $name, array $options = []): StatementInterface
    {
        return $this->db->execute(
            $this->getGrammar()::build(
                'DETACH DATABASE',
                $this->db->quoteName($name)
            )
        );
    }

    /**
     * createTable
     *
     * @param  Schema  $schema
     * @param  bool    $ifNotExists
     * @param  array   $options
     *
     * @return  StatementInterface
     */
    public function createTable(Schema $schema, bool $ifNotExists = false, array $options = []): StatementInterface
    {
        $defaultOptions = [];

        $options = array_merge($defaultOptions, $options);
        $columns = [];
        $table = $schema->getTable();
        $tableName = $this->db->quoteName($table->schemaName . '.' . $table->getName());
        $primaries = [];

        foreach ($schema->getColumns() as $column) {
            $column = $this->prepareColumn(clone $column);

            if ($column->isPrimary()) {
                $primaries[] = $column;
            }

            $columns[$column->getColumnName()] = $this->getColumnExpression($column)
                ->setName($this->db->quoteName($column->getColumnName()));
        }

        $constraints = $schema->getConstraints();

        if ($primaries) {
            $constraints[] = $this->createPrimaryKeyConstraint($table->getName(), $primaries);
        }

        foreach ($constraints as $constraint) {
            $columns[] = $this->getTableConstraintClause($constraint);
        }

        $sql = $this->getGrammar()::build(
            'CREATE TABLE',
            $ifNotExists ? 'IF NOT EXISTS' : null,
            $tableName,
            "(\n" . implode(",\n", $columns) . "\n)",
            $this->getGrammar()::buildConfig([])
        );

        $statement = $this->db->execute($sql);

        $this->postTableModify($table, $schema->getIndexes());

        return $statement;
    }

    protected function createPrimaryKeyConstraint(string $tableName, array $primaries): Constraint
    {
        return new Constraint(Constraint::TYPE_PRIMARY_KEY, 'pk_' . $tableName, $tableName)
            ->columns($primaries);
    }

    protected function getTableConstraintClause(Constraint $constraint): Clause
    {
        $alter = $this->createQuery()->alter('', '');
        $alter->getClause()->setName('');

        if ($constraint->isCheck()) {
            return $alter->addCheck($constraint->constraintName, (string) $constraint->checkClause)
                ->setName('CONSTRAINT');
        }

        return $alter->addConstraint(
            $constraint->constraintName,
            $constraint->constraintType,
            $this->prepareKeyColumns($constraint->getColumns())
        )
            ->setName('CONSTRAINT');
    }

    /**
     * SQLite can not alter columns, so the table is always rebuilt: create a new table, copy the data,
     * drop the old one and rename the new one. Untouched column / constraint definitions are kept verbatim
     * from the stored `CREATE TABLE` statement, so CHECK, FOREIGN KEY, collations... are not lost.
     */
    public function updateTable(TableManager $table, Schema $schema): void
    {
        $name = $table->getName();
        $schemaName = $table->schemaName;
        $realName = $this->db->replacePrefix($name);

        $tableSql = null;
        $indexes = [];
        $triggers = [];

        $master = $this->createQuery()
            ->select('type', 'name', 'sql')
            ->from(trim(($schemaName ?? static::getDefaultSchema()) . '.sqlite_master', '.'))
            ->where('tbl_name', $realName);

        foreach ($this->db->prepare($master) as $row) {
            if ($row['sql'] === null) {
                continue;
            }

            match ($row['type']) {
                'table' => $tableSql = $row['sql'],
                'index' => $indexes[$row['name']] = $row['sql'],
                'trigger' => $triggers[] = $row['sql'],
                default => null,
            };
        }

        if ($tableSql === null) {
            throw new RuntimeException(sprintf('Table "%s" not exists, unable to update.', $name));
        }

        $start = strpos($tableSql, '(');
        $end = strrpos($tableSql, ')');

        $tableOptions = substr($tableSql, $end + 1);
        $dropColumns = $schema->dropColumns;
        $dropConstraints = array_flip($schema->dropConstraints);
        $dropIndexes = array_flip($schema->dropIndexes);
        $columnDefs = [];
        $constraintDefs = [];

        foreach ($this->splitDefinitions(substr($tableSql, $start + 1, $end - $start - 1)) as $definition) {
            $identifier = '"[^"]+"|`[^`]+`|\[[^\]]+\]|\w+';

            if (
                preg_match(
                    '/^(?:CONSTRAINT\s+(?<name>' . $identifier . ')\s+)?(?:PRIMARY\s+KEY|UNIQUE|CHECK|FOREIGN\s+KEY)\b/i',
                    $definition,
                    $matches
                )
            ) {
                $constraintDefs[] = [
                    'name' => ($matches['name'] ?? '') !== '' ? $this->unquoteIdentifier($matches['name']) : null,
                    'sql' => $definition,
                ];
            } elseif (preg_match('/^(' . $identifier . ')/', $definition, $matches)) {
                $columnDefs[$this->unquoteIdentifier($matches[1])] = $definition;
            }
        }

        $newColumns = $columnDefs;
        $primaries = [];

        foreach ($schema->getColumns() as $column) {
            $column = $this->prepareColumn(clone $column);
            $columnName = $column->getColumnName();

            $definition = (string) $this->getColumnExpression($column)
                ->setName($this->db->quoteName($columnName));

            // Keep the inline primary key of the original column definition
            if (
                isset($columnDefs[$columnName])
                && preg_match(
                    '/\bPRIMARY\s+KEY(?:\s+(?:ASC|DESC))?(?:\s+AUTOINCREMENT)?/i',
                    $columnDefs[$columnName],
                    $matches
                )
            ) {
                $definition .= ' ' . $matches[0];
            }

            $newColumns[$columnName] = $definition;

            if ($column->isPrimary()) {
                $primaries[] = $column;
            }
        }

        foreach ($dropColumns as $dropColumn) {
            unset($newColumns[$dropColumn]);
        }

        $replacedKeyNames = [];

        foreach ($schema->getConstraints() as $constraint) {
            $replacedKeyNames[$constraint->constraintName] = true;
        }

        foreach ($schema->getIndexes() as $index) {
            $replacedKeyNames[$index->indexName] = true;
        }

        $newConstraints = [];

        foreach ($constraintDefs as $constraint) {
            if (
                $constraint['name'] !== null
                && (isset($dropConstraints[$constraint['name']]) || isset($replacedKeyNames[$constraint['name']]))
            ) {
                continue;
            }

            if ($this->referencesColumns($constraint['sql'], $dropColumns)) {
                continue;
            }

            $newConstraints[] = $constraint['sql'];
        }

        foreach ($schema->getConstraints() as $constraint) {
            $newConstraints[] = (string) $this->getTableConstraintClause($constraint);
        }

        $hasPrimaryKey = array_any(
            $newConstraints,
            static fn(string $sql) => (bool) preg_match('/^(?:CONSTRAINT\s+\S+\s+)?PRIMARY\s+KEY\b/i', $sql)
        ) || array_any(
            $newColumns,
            static fn(string $sql) => (bool) preg_match('/\bPRIMARY\s+KEY\b/i', $sql)
        );

        if ($primaries && !$hasPrimaryKey) {
            $newConstraints[] = (string) $this->getTableConstraintClause(
                $this->createPrimaryKeyConstraint($name, $primaries)
            );
        }

        $copyColumns = [];

        foreach (array_keys($columnDefs) as $columnName) {
            if (
                isset($newColumns[$columnName])
                && !preg_match('/\b(?:GENERATED\s+ALWAYS\s+)?AS\s*\(/i', $newColumns[$columnName])
            ) {
                $copyColumns[] = $this->db->quoteName($columnName);
            }
        }

        $oldTable = $this->db->quoteName($schemaName . '.' . $name);
        $tmpTable = $this->db->quoteName($schemaName . '.' . $name . '_ww_rebuild');
        $createSql = "CREATE TABLE $tmpTable (\n"
            . implode(",\n", [...array_values($newColumns), ...$newConstraints])
            . "\n)" . $tableOptions;

        $sequence = $this->loadAutoIncrementSequence($realName, $schemaName);

        $foreignKeys = (bool) $this->db->prepare($this->pragma('foreign_keys'))->result();
        $legacyAlter = (bool) $this->db->prepare($this->pragma('legacy_alter_table'))->result();

        // PRAGMA foreign_keys is a no-op inside a transaction
        $disableForeignKeys = $foreignKeys && !$this->isInTransaction();

        if ($disableForeignKeys) {
            $this->db->execute('PRAGMA foreign_keys = OFF');
        }

        try {
            $this->transaction(
                function () use (
                    $name,
                    $oldTable,
                    $tmpTable,
                    $createSql,
                    $copyColumns,
                    $sequence,
                    $legacyAlter,
                    $indexes,
                    $triggers,
                    $dropColumns,
                    $dropIndexes,
                    $replacedKeyNames,
                    $schema,
                    $table
                ) {
                    $this->db->execute('PRAGMA defer_foreign_keys = ON');
                    $this->db->execute("DROP TABLE IF EXISTS $tmpTable");
                    $this->db->execute($createSql);

                    if ($copyColumns) {
                        $cols = implode(', ', $copyColumns);

                        $this->db->execute("INSERT INTO $tmpTable ($cols) SELECT $cols FROM $oldTable");
                    }

                    // Rows may have been deleted from the end, keep AUTOINCREMENT counter as is
                    if ($sequence !== null && stripos($createSql, 'AUTOINCREMENT') !== false) {
                        $tmpName = $name . '_ww_rebuild';

                        $this->db->execute(
                            'DELETE FROM sqlite_sequence WHERE name = ' . $this->db->quote($tmpName)
                        );
                        $this->db->execute(
                            'INSERT INTO sqlite_sequence (name, seq) VALUES ('
                            . $this->db->quote($tmpName) . ', ' . $sequence . ')'
                        );
                    }

                    $this->db->execute("DROP TABLE $oldTable");

                    // Views / triggers referencing the table would make a standard rename fail
                    $this->db->execute('PRAGMA legacy_alter_table = ON');
                    $this->db->execute(
                        "ALTER TABLE $tmpTable RENAME TO " . $this->db->quoteName($name)
                    );
                    $this->db->execute('PRAGMA legacy_alter_table = ' . ($legacyAlter ? 'ON' : 'OFF'));

                    foreach ($indexes as $indexName => $indexSql) {
                        if (isset($dropIndexes[$indexName]) || isset($replacedKeyNames[$indexName])) {
                            continue;
                        }

                        $onClause = preg_split('/\bON\b/i', $indexSql, 2)[1] ?? $indexSql;

                        if ($this->referencesColumns($onClause, $dropColumns)) {
                            continue;
                        }

                        $this->db->execute($indexSql);
                    }

                    foreach ($triggers as $triggerSql) {
                        $this->db->execute($triggerSql);
                    }

                    $this->postTableModify($table, $schema->getIndexes());
                }
            );
        } finally {
            if ($disableForeignKeys) {
                $this->db->execute('PRAGMA foreign_keys = ON');
            }
        }
    }

    protected function loadAutoIncrementSequence(string $table, ?string $schema): ?int
    {
        $master = trim(($schema ?? static::getDefaultSchema()) . '.sqlite_master', '.');

        $exists = $this->db->prepare(
            $this->createQuery()
                ->select('name')
                ->from($master)
                ->where('type', 'table')
                ->where('name', 'sqlite_sequence')
        )->result();

        if (!$exists) {
            return null;
        }

        $seq = $this->db->prepare(
            $this->createQuery()
                ->select('seq')
                ->from(trim(($schema ?? static::getDefaultSchema()) . '.sqlite_sequence', '.'))
                ->where('name', $table)
        )->result();

        return $seq === null || $seq === false ? null : (int) $seq;
    }

    /**
     * Split a `CREATE TABLE` body by top-level commas, ignoring commas in parentheses and quotes.
     *
     * @return  string[]
     */
    protected function splitDefinitions(string $body): array
    {
        $parts = [];
        $depth = 0;
        $quote = null;
        $current = '';

        foreach (str_split($body) as $char) {
            if ($quote !== null) {
                if ($char === $quote) {
                    $quote = null;
                }
            } elseif ($char === "'" || $char === '"' || $char === '`') {
                $quote = $char;
            } elseif ($char === '[') {
                $quote = ']';
            } elseif ($char === '(') {
                $depth++;
            } elseif ($char === ')') {
                $depth--;
            } elseif ($char === ',' && $depth === 0) {
                $parts[] = trim($current);
                $current = '';
                continue;
            }

            $current .= $char;
        }

        if (trim($current) !== '') {
            $parts[] = trim($current);
        }

        return $parts;
    }

    protected function unquoteIdentifier(string $identifier): string
    {
        if (preg_match('/^(["`])(.*)\1$/s', $identifier, $matches)) {
            return $matches[2];
        }

        if (str_starts_with($identifier, '[') && str_ends_with($identifier, ']')) {
            return substr($identifier, 1, -1);
        }

        return $identifier;
    }

    /**
     * Check if a key definition uses any of the columns. Referenced columns of a FOREIGN KEY are ignored.
     *
     * @param  string[]  $columns
     */
    protected function referencesColumns(string $sql, array $columns): bool
    {
        $sql = preg_split('/\bREFERENCES\b/i', $sql, 2)[0];

        foreach ($columns as $column) {
            if (preg_match('/(?<!\w)["`\[]?' . preg_quote($column, '/') . '["`\]]?(?!\w)/i', $sql)) {
                return true;
            }
        }

        return false;
    }

    protected function getKeyColumnExpression(Column $column): Clause
    {
        $expr = parent::getKeyColumnExpression($column);

        if ($column->isAutoIncrement()) {
            $expr->append('AUTOINCREMENT');
        }

        return $expr;
    }

    /**
     * modifyColumn
     *
     * @param  string       $table
     * @param  Column       $column
     * @param  string|null  $schema
     *
     * @return  StatementInterface
     */
    public function modifyColumn(string $table, Column $column, ?string $schema = null): StatementInterface
    {
        throw new LogicException('Current SQLitePlatform not support: ' . __FUNCTION__ . '()');
    }

    /**
     * renameColumn
     *
     * @param  string       $table
     * @param  string       $from
     * @param  string       $to
     * @param  string|null  $schema
     *
     * @return  StatementInterface
     */
    public function renameColumn(string $table, string $from, string $to, ?string $schema = null): StatementInterface
    {
        throw new LogicException('Current SQLitePlatform not support: ' . __FUNCTION__ . '()');
    }

    /**
     * addConstraint
     *
     * @param  string       $table
     * @param  Constraint   $constraint
     * @param  string|null  $schema
     *
     * @return  StatementInterface
     */
    public function addConstraint(string $table, Constraint $constraint, ?string $schema = null): StatementInterface
    {
        throw new LogicException('Current SQLitePlatform not support: ' . __FUNCTION__ . '()');
    }

    /**
     * dropColumn
     *
     * @param  string       $table
     * @param  string       $name
     * @param  string|null  $schema
     *
     * @return  StatementInterface
     */
    public function dropColumn(string $table, string $name, ?string $schema = null): StatementInterface
    {
        throw new LogicException('Current SQLitePlatform not support: ' . __FUNCTION__ . '()');
    }

    /**
     * truncateTable
     *
     * @param  string       $table
     * @param  string|null  $schema
     *
     * @return  StatementInterface
     */
    public function truncateTable(string $table, ?string $schema = null): StatementInterface
    {
        $statement = $this->db->execute(
            $this->getGrammar()::build(
                'DELETE FROM',
                $this->db->quoteName($schema . '.' . $table)
            )
        );

        // DELETE keeps AUTOINCREMENT counters; reset them so ids restart like MySQL TRUNCATE.
        $hasSequence = $this->db->prepare(
            "SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'sqlite_sequence'"
        )->get();

        if ($hasSequence) {
            $statement = $this->db->execute(
                'DELETE FROM sqlite_sequence WHERE name = ' . $this->db->quote($table)
            );
        }

        return $statement;
    }

    public function dropIndex(string $table, string $name, ?string $schema = null): StatementInterface
    {
        return $this->db->execute(
            $this->getGrammar()::build(
                'DROP INDEX',
                $this->db->quoteName($schema ? $schema . '.' . $name : $name),
            )
        );
    }
}
