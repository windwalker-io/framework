<?php

declare(strict_types=1);

namespace Windwalker\Database\Schema\Ddl;

use Windwalker\Database\Platform\Type\DataType;
use Windwalker\Utilities\Options\OptionAccessTrait;

/**
 * The Index class.
 */
class Index
{
    use WrappableTrait;
    use OptionAccessTrait;

    public ?string $tableName = null;

    public ?string $indexName = null;

    public ?string $indexComment = null;

    public bool $isUnique;

    public bool $isPrimary;

    /**
     * @var Column[]
     */
    public array $columns = [];

    /**
     * Index constructor.
     *
     * @param  string|null  $tableName
     * @param  string       $indexName
     */
    public function __construct(?string $indexName = null, ?string $tableName = null)
    {
        $this->tableName = $tableName;
        $this->indexName = $indexName;
    }

    public function tableName(string $tableName): static
    {
        $this->tableName = $tableName;

        return $this;
    }

    public function name(string $indexName): static
    {
        $this->indexName = $indexName;

        return $this;
    }

    public function comment(?string $indexComment): static
    {
        $this->indexComment = $indexComment;

        return $this;
    }

    /**
     * columns
     *
     * @param  Column[]|string[]  $columns
     *
     * @return  $this
     */
    public function columns(array $columns): static
    {
        $cols = [];

        foreach ($columns as $key => $column) {
            if (!$column instanceof Column) {
                $colName = $column;

                if (is_array($column)) {
                    $colName = $key;
                }

                [$colName, $subParts] = DataType::extract($colName);

                $erratas = $column['erratas'] ?? [];

                $column = new Column($colName);

                if ($subParts) {
                    $erratas['sub_parts'] = $subParts;
                }

                $column->erratas($erratas);
            }

            $cols[$column->getColumnName()] = $column;
        }

        $this->columns = $cols;

        return $this;
    }

    /**
     * @return Column[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getFirstColumn(): Column
    {
        return array_values($this->columns)[0];
    }

    /**
     * @return  string[]
     */
    public function getColumnNames(): array
    {
        return array_column($this->columns, 'columnName');
    }

    public function columnsCount(): int
    {
        return count($this->columns);
    }
}
