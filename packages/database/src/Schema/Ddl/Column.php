<?php

declare(strict_types=1);

namespace Windwalker\Database\Schema\Ddl;

use Windwalker\Database\Platform\Type\DataType;
use Windwalker\Query\Grammar\MySQLGrammar;
use Windwalker\Query\Query;
use Windwalker\Utilities\Options\OptionAccessTrait;
use Windwalker\Utilities\TypeCast;
use Windwalker\Utilities\Wrapper\RawWrapper;

use function Windwalker\raw;

/**
 * The Column class.
 */
class Column
{
    use WrappableTrait;
    use OptionAccessTrait;

    public string $columnName = '';

    protected ?int $ordinalPosition = 1;

    /**
     * @var  mixed
     */
    protected mixed $columnDefault = null;

    protected bool $isNullable = false;

    protected ?string $dataType = null;

    protected int|string|null $characterMaximumLength = null;

    protected ?int $characterOctetLength = null;

    protected ?int $numericPrecision = null;

    protected ?int $numericScale = null;

    protected bool $numericUnsigned = false;

    protected ?string $comment = null;

    protected ?string $characterSetName = null;

    protected ?string $collationName = null;

    protected bool $autoIncrement = false;

    protected array $erratas = [];

    protected ?string $generationExpression = null;

    protected bool $generatedStored = false;

    /**
     * @var  array<Constraint>
     */
    public array $constraints = [];

    /**
     * @var  array<Index>
     */
    public array $indexes = [];

    public function __construct(
        string $name = '',
        ?string $dataType = null,
        bool $isNullable = false,
        mixed $columnDefault = null,
        array $options = []
    ) {
        $this->columnName = $name;
        $this->columnDefault = $columnDefault;
        $this->isNullable = $isNullable;
        $this->dataType((string) $dataType);

        $this->fill($options);
    }

    // public function fill(array $data): static
    // {
    //     $this->columnName = $data['column_name'] ?? '';
    //     $this->ordinalPosition = (int) ($data['ordinal_position'] ?? 0);
    //     $this->columnDefault = $data['column_default'] ?? '';
    //     $this->isNullable = (bool) ($data['is_nullable'] ?? false);
    //     $this->dataType = $data['data_type'] ?? '';
    //     $this->characterOctetLength = (int) ($data['character_octet_length'] ?? 0);
    //     $this->numericPrecision = (int) ($data['numeric_precision'] ?? 0);
    //     $this->numericScale = (int) ($data['numeric_scale'] ?? 0);
    //     $this->numericUnsigned = (bool) ($data['numeric_unsigned'] ?? false);
    //     $this->comment = $data['comment'] ?? '';
    //     $this->characterSetName = $data['character_set_name'] ?? '';
    //     $this->collationName = $data['collation_name'] ?? '';
    //     $this->autoIncrement = (bool) ($data['auto_increment'] ?? false);
    //
    //     return $this;
    // }

    /**
     * @return  string
     */
    public function getColumnName(): string
    {
        return $this->columnName;
    }

    /**
     * @param  string  $name
     *
     * @return  static  Return self to support chaining.
     */
    public function columnName(string $name): static
    {
        $this->columnName = $name;

        return $this;
    }

    /**
     * @return  int
     *
     * @codeCoverageIgnore
     */
    public function getOrdinalPosition(): ?int
    {
        return $this->ordinalPosition;
    }

    /**
     * @param  int|null  $ordinalPosition
     *
     * @return  static  Return self to support chaining.
     */
    public function ordinalPosition(int|string|null $ordinalPosition): static
    {
        $this->ordinalPosition = TypeCast::tryInteger($ordinalPosition, true);

        return $this;
    }

    /**
     * @return  mixed
     */
    public function getColumnDefault(): mixed
    {
        return $this->columnDefault;
    }

    /**
     * @param  mixed  $value
     *
     * @return  static  Return self to support chaining.
     */
    public function columnDefault(mixed $value): static
    {
        $this->columnDefault = $value;

        return $this;
    }

    public function canHasDefaultValue(): bool
    {
        if ($this->isGenerated()) {
            return false;
        }

        return $this->getColumnDefault() !== false && !($this->getColumnDefault() === null && !$this->getIsNullable());
    }

    /**
     * Alias of setColumnDefault().
     *
     * @param  mixed  $value
     *
     * @return  static  Return self to support chaining.
     */
    public function defaultValue(mixed $value): static
    {
        return $this->columnDefault($value);
    }

    public function defaultCurrent(): static
    {
        return $this->defaultValue(raw('CURRENT_TIMESTAMP'));
    }

    public function onUpdateCurrent(bool $bool = true): static
    {
        return $this->setOption('on_update', $bool);
    }

    /**
     * @return  bool
     */
    public function getIsNullable(): bool
    {
        return $this->isNullable;
    }

    /**
     * @param  bool  $isNullable
     *
     * @return  static  Return self to support chaining.
     */
    public function isNullable(bool $isNullable): static
    {
        $this->isNullable = $isNullable;

        return $this;
    }

    public function nullable(bool $isNullable): static
    {
        return $this->isNullable($isNullable);
    }

    /**
     * @return  string
     */
    public function getDataType(): string
    {
        return $this->dataType;
    }

    /**
     * @param  string  $dataType
     *
     * @return  static  Return self to support chaining.
     */
    public function dataType(string $dataType): static
    {
        if (str_contains($dataType, '(')) {
            [$dataType, $precision, $scale] = DataType::extract($dataType);

            $this->dataType = $dataType;

            $this->setLengthByType(
                TypeCast::tryInteger($precision, true),
                TypeCast::tryInteger($scale, true)
            );
        } else {
            $this->dataType = $dataType;
        }

        return $this;
    }

    public function position(string $delta, string $pos): static
    {
        $this->setOption('position', [$delta, $pos]);

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function before(string $column): static
    {
        return $this->position('BEFORE', $column);
    }

    public function after(string $column): static
    {
        return $this->position('AFTER', $column);
    }

    /**
     * length
     *
     * @param  int|string|null  $value
     *
     * @return  static
     */
    public function length(string|int|null $value): static
    {
        if ($value === null) {
            $this->setLengthByType(null, null);
        }

        [$dataType, $precision, $scale] = DataType::extract("t($value)");

        $this->setLengthByType(
            $precision,
            TypeCast::tryInteger($scale, true)
        );

        return $this;
    }

    private function setLengthByType(int|string|null $precision, ?int $scale): void
    {
        if ($this->isNumeric()) {
            $this->numericPrecision($precision);
            $this->numericScale($scale);

            return;
        }

        $this->characterMaximumLength($precision);
    }

    public function getLengthExpression(): ?string
    {
        if ((string) $this->characterMaximumLength !== '') {
            return (string) $this->characterMaximumLength;
        }

        if ((string) $this->numericPrecision !== '' || $this->numericScale !== null) {
            return implode(',', array_filter([$this->numericPrecision, $this->numericScale]));
        }

        return null;
    }

    public function isNumeric(): bool
    {
        $type = $this->dataType;

        return in_array(
            strtolower($type),
            [
                'int',
                'integer',
                'tinyint',
                'tinyinteger',
                'bigint',
                'biginteger',
                'smallint',
                'smallinteger',
                'float',
                'double',
                'real',
                'decimal',
                'numeric',
            ],
            true
        );
    }

    /**
     * @return  int|string|null
     */
    public function getCharacterMaximumLength(): int|string|null
    {
        return $this->characterMaximumLength;
    }

    /**
     * @param  int|string  $characterMaximumLength
     *
     * @return  static  Return self to support chaining.
     */
    public function characterMaximumLength(int|string|null $characterMaximumLength): static
    {
        $this->characterMaximumLength = $characterMaximumLength;

        return $this;
    }

    /**
     * @return  int
     *
     * @codeCoverageIgnore
     */
    public function getCharacterOctetLength(): ?int
    {
        return $this->characterOctetLength;
    }

    /**
     * @param  int|null  $characterOctetLength
     *
     * @return  static  Return self to support chaining.
     */
    public function characterOctetLength(int|string|null $characterOctetLength): static
    {
        $this->characterOctetLength = TypeCast::tryInteger($characterOctetLength, true);

        return $this;
    }

    /**
     * @return  int
     */
    public function getNumericPrecision(): ?int
    {
        return $this->numericPrecision;
    }

    public function numericPrecision(int|string|null $precision): static
    {
        $this->numericPrecision = TypeCast::tryInteger($precision, true);

        return $this;
    }

    /**
     * @return  int
     */
    public function getNumericScale(): ?int
    {
        return $this->numericScale;
    }

    /**
     * @param  int  $scale
     *
     * @return  static  Return self to support chaining.
     */
    public function numericScale(int|string|null $scale): static
    {
        $this->numericScale = TypeCast::tryInteger($scale, true);

        return $this;
    }

    /**
     * @return  bool
     */
    public function getNumericUnsigned(): bool
    {
        return $this->numericUnsigned;
    }

    /**
     * @param  bool  $unsigned
     *
     * @return  static  Return self to support chaining.
     *
     * @codeCoverageIgnore
     */
    public function unsigned(bool $unsigned = true): static
    {
        $this->numericUnsigned = $unsigned;

        return $this;
    }

    /**
     * @return  string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param  string|null  $comment
     *
     * @return  static  Return self to support chaining.
     */
    public function comment(?string $comment = null): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function primary(bool $primary = true): static
    {
        $this->setOption('primary', $primary);

        return $this;
    }

    public function isPrimary(): bool
    {
        return (bool) $this->getOption('primary');
    }

    /**
     * @return  bool
     */
    public function isAutoIncrement(): bool
    {
        return $this->autoIncrement;
    }

    /**
     * @param  bool  $autoIncrement
     *
     * @return  static  Return self to support chaining.
     */
    public function autoIncrement(bool $autoIncrement = true): static
    {
        $this->autoIncrement = $autoIncrement;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function charset(?string $charset = null): static
    {
        $this->characterSetName = $charset;

        return $this;
    }

    /**
     * Define as generated column.
     *
     * @param  string|RawWrapper  $expression  SQL expression, must be trusted code.
     * @param  bool               $stored      STORED if true, otherwise VIRTUAL.
     */
    public function generated(string|RawWrapper $expression, bool $stored = false): static
    {
        $this->generationExpression = (string) ($expression instanceof RawWrapper ? $expression->get() : $expression);
        $this->generatedStored = $stored;

        return $this;
    }

    public function virtual(string|RawWrapper $expression): static
    {
        return $this->generated($expression, false);
    }

    public function stored(string|RawWrapper $expression): static
    {
        return $this->generated($expression, true);
    }

    public function generationExpression(?string $expression): static
    {
        $this->generationExpression = $expression ?: null;

        return $this;
    }

    public function generatedStored(bool|string|int $stored): static
    {
        $this->generatedStored = (bool) $stored;

        return $this;
    }

    public function isGenerated(): bool
    {
        return $this->generationExpression !== null && $this->generationExpression !== '';
    }

    public function getGenerationExpression(): ?string
    {
        return $this->generationExpression;
    }

    public function isGeneratedStored(): bool
    {
        return $this->generatedStored;
    }

    /**
     * Use as an expression key part in index (functional index).
     */
    public function indexExpression(?string $expression): static
    {
        $this->erratas['expression'] = $expression;

        return $this;
    }

    public function getIndexExpression(): ?string
    {
        return $this->erratas['expression'] ?? null;
    }

    /**
     * @codeCoverageIgnore
     */
    public function collation(?string $collation): static
    {
        $this->collationName = $collation;

        return $this;
    }

    /**
     * @return  string|null
     *
     * @codeCoverageIgnore
     */
    public function getCharacterSetName(): ?string
    {
        return $this->characterSetName;
    }

    /**
     * @param  string|null  $characterSetName
     *
     * @return  static  Return self to support chaining.
     *
     * @codeCoverageIgnore
     */
    public function setCharacterSetName(?string $characterSetName): static
    {
        $this->characterSetName = $characterSetName;

        return $this;
    }

    /**
     * @return  string|null
     */
    public function getCollationName(): ?string
    {
        return $this->collationName;
    }

    /**
     * @param  string|null  $collationName
     *
     * @return  static  Return self to support chaining.
     *
     * @codeCoverageIgnore
     */
    public function setCollationName(?string $collationName): static
    {
        $this->collationName = $collationName;

        return $this;
    }

    /**
     * @return  array
     */
    public function getErratas(): array
    {
        return $this->erratas;
    }

    /**
     * @param  array  $erratas
     *
     * @return  static  Return self to support chaining.
     */
    public function erratas(array $erratas): static
    {
        $this->erratas = $erratas;

        return $this;
    }

    /**
     * Compare this column (desired) with another column (actual).
     *
     * Both columns should be normalized by `AbstractPlatform::prepareColumn()` first.
     * Optional attributes (comment, charset, collation) are only compared when this column defines them.
     *
     * @param  Column  $actual
     *
     * @return  array<string, array{from: mixed, to: mixed}>  Changed properties, `from` is the actual value
     *                                                        and `to` is the desired value.
     */
    private const array INTEGER_TYPES = ['tinyint', 'smallint', 'mediumint', 'int', 'bigint'];

    public function diff(Column $actual): array
    {
        $desired = [
            'dataType' => static::normalizeType($this->getDataType()),
            'length' => $this->getLengthExpression(),
            'nullable' => $this->getIsNullable(),
            'default' => static::normalizeDefault($this->canHasDefaultValue() ? $this->getColumnDefault() : null),
            'unsigned' => $this->getNumericUnsigned(),
            'autoIncrement' => $this->isAutoIncrement(),
            'generation' => $this->getGenerationExpression(),
            'generatedStored' => $this->isGeneratedStored(),
            'comment' => $this->getComment(),
            'charset' => $this->getCharacterSetName(),
            'collation' => $this->getCollationName(),
        ];

        $current = [
            'dataType' => static::normalizeType($actual->getDataType()),
            'length' => $actual->getLengthExpression(),
            'nullable' => $actual->getIsNullable(),
            'default' => static::normalizeDefault($actual->canHasDefaultValue() ? $actual->getColumnDefault() : null),
            'unsigned' => $actual->getNumericUnsigned(),
            'autoIncrement' => $actual->isAutoIncrement(),
            'generation' => $actual->getGenerationExpression(),
            'generatedStored' => $actual->isGeneratedStored(),
            'comment' => $actual->getComment(),
            'charset' => $actual->getCharacterSetName(),
            'collation' => $actual->getCollationName(),
        ];

        $optional = ['length', 'comment', 'charset', 'collation'];
        $changes = [];

        foreach ($desired as $name => $value) {
            if (in_array($name, $optional, true) && ($value === null || $value === '')) {
                continue;
            }

            // Integer display width is cosmetic and differs between drivers (e.g. int(10) vs int(11))
            if ($name === 'length' && in_array($desired['dataType'], self::INTEGER_TYPES, true)) {
                continue;
            }

            if ($value !== $current[$name]) {
                $changes[$name] = ['from' => $current[$name], 'to' => $value];
            }
        }

        return $changes;
    }

    private static function normalizeDefault(mixed $value): ?string
    {
        if ($value === null || $value === false) {
            return null;
        }

        if ($value instanceof RawWrapper) {
            return strtoupper(trim((string) $value));
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (strtoupper(trim((string) $value)) === 'NULL') {
            return null;
        }

        $value = trim((string) $value, " '\"");

        if (is_numeric($value) && str_contains($value, '.')) {
            $value = rtrim(rtrim($value, '0'), '.');
        }

        return $value;
    }

    private static function normalizeType(string $type): string
    {
        $type = strtolower($type);

        return match ($type) {
            'numeric' => 'decimal',
            'integer' => 'int',
            'bool' => 'boolean',
            'character varying' => 'varchar',
            'character' => 'char',
            'timestamp without time zone' => 'timestamp',
            'timestamp with time zone' => 'timestamptz',
            default => $type,
        };
    }

    public function getTypeExpression(?DataType $dt = null): string
    {
        $expr = $this->dataType;

        if ($dt && $dt::isNoLength($expr)) {
            return $expr;
        }

        $length = $this->getLengthExpression();

        if ($length !== null) {
            $expr .= '(' . $length . ')';
        }

        return $expr;
    }

    /**
     * @param  Query  $query
     *
     * @return  string
     *
     * @deprecated  Currently unused, will be removed in the future.
     * @codeCoverageIgnore
     */
    public function getCreateExpression(Query $query): string
    {
        $expr = $this->getTypeExpression($this->getDataType());

        if (!$this->isNullable) {
            $expr .= ' NOT NULL';
        }

        if ($this->columnDefault !== null || $this->isNullable) {
            $expr .= ' DEFAULT ' . $query->quote($this->columnDefault);
        }

        if ($query->getGrammar() instanceof MySQLGrammar) {
            if ($this->comment !== null) {
                $expr .= ' COMMENT ' . $query->quote($this->columnDefault);
            }

            if ($this->getOption('position') !== null) {
                [$delta, $pos] = $this->getOption('position');
                $expr .= ' POSITION ' . $delta . ' ' . $query->quoteName($pos);
            }
        }

        return $expr;
    }
}
