<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Edge\Compiler\Concern;

/**
 * The CompileIncludeTrait class.
 */
trait CompileJsonTrait
{
    /**
     * The default JSON encoding options.
     *
     * @var int
     */
    public int $jsonOptions = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;

    /**
     * Compile the JSON statement into valid PHP.
     *
     * @param  string  $expression
     * @return string
     */
    protected function compileJson(string $expression): string
    {
        $parts = explode(',', $this->stripParentheses($expression));

        $options = isset($parts[1]) ? trim($parts[1]) : $this->jsonOptions;

        $depth = isset($parts[2]) ? trim($parts[2]) : 512;

        return "<?php echo e(json_encode($parts[0], $options, $depth)); ?>";
    }

    /**
     * Compile the JSON statement into valid PHP.
     *
     * @param  string  $expression
     * @return string
     */
    protected function compileJs(string $expression): string
    {
        $expression = $this->stripParentheses($expression);

        return "<?php echo \\Windwalker\\Edge\\EdgeHelper::toJS({$expression}, true); ?>";
    }
}
