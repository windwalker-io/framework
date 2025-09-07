<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Windwalker\ORM\Cast\CasterInfo;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class AutoTruncate implements CastForSaveInterface
{
    public function __construct(protected ?int $maxLength = null)
    {
    }

    public function getCaster(): mixed
    {
        return function ($value, CasterInfo $info) {
            if ($value === null) {
                return null;
            }

            if (!is_string($value)) {
                return $value;
            }

            $tb = $info->orm->db->getTableManager($info->metadata->getTableName());
            $dbColumn = $tb->getColumn($info->column->getName());

            if (!$dbColumn) {
                return $value;
            }

            $max = $this->maxLength ??= $dbColumn?->getCharacterMaximumLength();

            if ($max === -1 || $max === null) {
                return $value;
            }

            if (mb_strlen($value) > $max) {
                $value = mb_substr($value, 0, (int) $max);
            }

            return $value;
        };
    }
}
