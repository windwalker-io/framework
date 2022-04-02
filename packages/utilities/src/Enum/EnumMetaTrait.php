<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2022 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Enum;

use Windwalker\Attributes\AttributesAccessor;
use Windwalker\Utilities\Attributes\Enum\Color;
use Windwalker\Utilities\Attributes\Enum\Icon;
use Windwalker\Utilities\Attributes\Enum\Meta;
use Windwalker\Utilities\Attributes\Enum\Title;
use Windwalker\Utilities\Contract\LanguageInterface;

/**
 * Trait EnumMetaTrait
 */
trait EnumMetaTrait
{
    use EnumPhpAdapterTrait;

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

        foreach (static::values() as $item) {
            $values[$item->getValue()] = $item->getTitle();
        }

        return $values;
    }

    public function getIcon(): string
    {
        return $this->getAttr(Icon::class)?->getIcon() ?? '';
    }

    public static function getIcons(): array
    {
        $values = [];

        foreach (static::values() as $item) {
            $values[$item->getValue()] = $item->getIcon();
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

        foreach (static::values() as $item) {
            $values[$item->getValue()] = $item->getcolor();
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

        foreach (static::values() as $item) {
            $values[$item->getValue()] = $item->getMeta();
        }

        return $values;
    }

    /**
     * @template T
     *
     * @param  string|T  $attr
     *
     * @return  object|null|T
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
}
