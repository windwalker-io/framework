<?php

declare(strict_types=1);

namespace Windwalker\ORM;

use Windwalker\Database\DatabaseAdapter;

use function Windwalker\unwrap_enum;

/**
 * The BaseCaster class.
 */
class BaseCaster
{
    protected ?\DateTimeZone $dbTimezone = null;

    public function __construct(protected DatabaseAdapter $db)
    {
        //
    }

    public function castDateTime(\DateTimeInterface $value): ?string
    {
        $tz = $this->dbTimezone;

        if ($tz && $tz->getName() !== $value->getTimezone()->getName()) {
            $value = \DateTimeImmutable::createFromInterface($value)->setTimezone($tz);
        }

        return $value->format($this->db->getDateFormat());
    }

    public function castJsonSerializable(\JsonSerializable $value): ?string
    {
        return json_encode($value);
    }

    public function castValue(mixed $value): mixed
    {
        if (is_object($value) && method_exists($value, '__toString')) {
            $value = (string) $value;
        }

        // Start prepare default value
        if (is_array($value) || is_object($value)) {
            $value = null;
        }

        return $value;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDbTimezone(): ?\DateTimeZone
    {
        return $this->dbTimezone;
    }

    /**
     * @param  \DateTimeZone|string  $dbTimezone
     *
     * @return  static      Return self to support chaining.
     * @throws  \Exception
     */
    public function setDbTimezone(\DateTimeZone|string|null $dbTimezone): static
    {
        if (is_string($dbTimezone)) {
            $dbTimezone = new \DateTimeZone($dbTimezone);
        }

        $this->dbTimezone = $dbTimezone;

        return $this;
    }

    public function castEnum(\UnitEnum $value): string|int
    {
        return unwrap_enum($value);
    }
}
