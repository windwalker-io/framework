<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2022 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Attributes\Enum;

use Windwalker\Utilities\Contract\LanguageInterface;

/**
 * The EnumString class.
 */
#[\Attribute(\Attribute::TARGET_CLASS_CONSTANT)]
class Title
{
    public function __construct(public string $string = '', public string $lang = '')
    {
        if ($this->string === '' && $this->lang === '') {
            throw new \InvalidArgumentException(
                __CLASS__ . ' must provide either string or trans key.'
            );
        }
    }

    public function toReadableString(?LanguageInterface $lang = null, ...$args): string
    {
        if ($this->lang !== '' && $lang) {
            return $lang->trans($this->lang, ...$args);
        }

        return $this->string;
    }
}
