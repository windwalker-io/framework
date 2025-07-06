<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Enum;

use Windwalker\Utilities\Attributes\AttributesAccessor;
use Windwalker\Utilities\Attributes\Enum\Color;
use Windwalker\Utilities\Attributes\Enum\Hidden;
use Windwalker\Utilities\Attributes\Enum\Icon;
use Windwalker\Utilities\Attributes\Enum\Meta;
use Windwalker\Utilities\Attributes\Enum\Title;
use Windwalker\Utilities\Contract\LanguageInterface;

/**
 * Trait EnumMetaTrait
 */
trait EnumMetaTrait
{
    public function getTitle(?LanguageInterface $lang = null, ...$args): string
    {
        $attr = $this->getAttr(Title::class);

        if ($attr) {
            return $attr->toReadableString($lang, ...$args);
        }

        if ($lang) {
            return $this->trans($lang, ...$args);
        }

        return '';
    }

    public static function getTitles(): array
    {
        $values = [];

        foreach (self::cases() as $item) {
            $values[$item->value] = $item->getTitle();
        }

        return $values;
    }

    public static function fromTitle(string $title): self
    {
        $titles = self::getTitles();

        $value = array_search($title, $titles, true);

        return self::wrap($value);
    }

    public static function tryFromTitle(string $title): ?self
    {
        $titles = self::getTitles();

        $value = array_search($title, $titles, true);

        if ($value === false) {
            return null;
        }

        return self::wrap($value);
    }

    public function getIcon(): string
    {
        return $this->getAttr(Icon::class)?->getIcon() ?? '';
    }

    public static function getIcons(): array
    {
        $values = [];

        foreach (static::cases() as $case) {
            $values[$case->value] = $case->getIcon();
        }

        return $values;
    }

    public function getColor(): string
    {
        return $this->getAttr(Color::class)?->getColor() ?? '';
    }

    public static function getColors(): array
    {
        $values = [];

        foreach (static::cases() as $case) {
            $values[$case->value] = $case->getColor();
        }

        return $values;
    }

    public function getMeta(): array
    {
        return $this->getAttr(Meta::class)?->getMeta() ?? [];
    }

    public static function getMetas(): array
    {
        $values = [];

        foreach (static::cases() as $case) {
            $values[$case->value] = $case->getMeta();
        }

        return $values;
    }

    public function isHidden(): bool
    {
        $hidden = $this->getAttr(Hidden::class);

        return $hidden instanceof Hidden;
    }

    /**
     * @template T
     *
     * @param  string|T  $attr
     *
     * @return  object|null|T
     * @throws \ReflectionException
     */
    protected function getAttr(string $attr): ?object
    {
        static $attrs = [];

        if ($this instanceof \UnitEnum) {
            $ref = new \ReflectionEnum($this);

            if (!$ref->hasCase($this->name)) {
                return null;
            }

            $case = $ref->getCase($this->name);

            return $attrs[$this->value][$attr]
                ??= AttributesAccessor::getFirstAttributeInstance($case, $attr);
        }

        $ref = new \ReflectionClass($this);
        $constant = $ref->getReflectionConstant($this->getKey());

        if (!$constant) {
            return null;
        }

        return $attrs[$this->getValue()][$attr]
            ??= AttributesAccessor::getFirstAttributeInstance($constant, $attr);
    }

    public static function maxLength(): int
    {
        static $max = null;

        if ($max !== null) {
            return $max;
        }

        $max = 0;

        foreach (self::values() as $value) {
            $max = max($max, strlen($value->value));
        }

        return $max;
    }
}
