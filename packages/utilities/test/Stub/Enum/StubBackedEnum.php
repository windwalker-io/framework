<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Stub\Enum;

use Windwalker\Utilities\Attributes\Enum\Color;
use Windwalker\Utilities\Attributes\Enum\Icon;
use Windwalker\Utilities\Attributes\Enum\Meta;
use Windwalker\Utilities\Attributes\Enum\Title;
use Windwalker\Utilities\Contract\LanguageInterface;
use Windwalker\Utilities\Enum\EnumTranslatableInterface;
use Windwalker\Utilities\Enum\EnumTranslatableTrait;

/**
 * The FooEnum class.
 */
enum StubBackedEnum: string implements EnumTranslatableInterface
{
    use EnumTranslatableTrait;

    #[Title('八八八')]
    #[Icon('fa fa-bars')]
    #[Color('primary')]
    #[Meta([
        'start' => true,
        'end' => false,
    ])]
    case BAR = 'bar';

    #[Title('又又又')]
    #[Icon('fa fa-file')]
    #[Color('danger')]
    #[Meta([
        'start' => false,
        'end' => true,
    ])]
    case YOO = 'yoo';

    public function trans(LanguageInterface $lang, ...$args): string
    {
        return $lang->trans('app.foo.' . $this->name);
    }
}
