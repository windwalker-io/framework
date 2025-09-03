<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Enum;

use Windwalker\Utilities\Contract\LanguageInterface;
use Windwalker\Utilities\StrNormalize;

trait EnumRichTrait
{
    use EnumExtendedTrait;
    use EnumMetaTrait;

    public static function getTransItems(LanguageInterface $lang, ...$args): array
    {
        $items = [];

        foreach (self::cases() as $item) {
            $items[$item->value ?? $item->name] = $item->getTitle($lang, ...$args);
        }

        return $items;
    }

    public function trans(LanguageInterface $lang, ...$args): string
    {
        return $lang->trans($this->translateKey($this->name), ...$args);
    }

    protected function translateKey(string $name): string
    {
        $ref = new \ReflectionEnum($this);
        $id = strtolower(StrNormalize::toDashSeparated($ref->getName()));

        return 'app.enum.' . $id . '.' . $name;
    }
}
