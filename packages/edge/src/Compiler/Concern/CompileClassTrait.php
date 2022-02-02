<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Edge\Compiler\Concern;

use Windwalker\Utilities\TypeCast;

/**
 * The CompileIncludeTrait class.
 */
trait CompileClassTrait
{
    /**
     * Compile the conditional class statement into valid PHP.
     *
     * @param  string|null  $expression
     *
     * @return string
     */
    protected function compileClass(?string $expression): string
    {
        $expression = $expression ?? '([])';

        return "class=\"<?php echo \\Windwalker\\Edge\\EdgeHelper::toCssClasses{$expression} ?>\"";
    }
}
