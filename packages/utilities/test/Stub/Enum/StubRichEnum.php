<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Stub\Enum;

use Windwalker\Utilities\Attributes\Enum\Hidden;
use Windwalker\Utilities\Attributes\Enum\Title;
use Windwalker\Utilities\Enum\EnumRichInterface;
use Windwalker\Utilities\Enum\EnumRichTrait;

/**
 * Native enum using EnumRichTrait (which provides translateKey()) with a hidden case,
 * a translatable title and a case without attributes. Used by EnumRichTraitTest.
 *
 * Can be removed together with EnumRichTraitTest.
 */
enum StubRichEnum: string implements EnumRichInterface
{
    use EnumRichTrait;

    #[Title('Alpha title')]
    #[Hidden]
    case ALPHA = 'alpha';

    #[Title(string: 'Beta fallback', lang: 'stub.beta')]
    case BETA = 'beta';

    case GAMMA = 'gamma_long';
}
