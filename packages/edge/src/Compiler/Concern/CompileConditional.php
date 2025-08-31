<?php

declare(strict_types=1);

namespace Windwalker\Edge\Compiler\Concern;

use Illuminate\Support\Str;

use Windwalker\Edge\Edge;

use function Windwalker\uid;

/**
 * The CompileConditional class.
 *
 * @since  3.5.4
 */
trait CompileConditional
{
    /**
     * Identifier for the first case in switch statement.
     *
     * @var bool
     */
    protected bool $firstCaseInSwitch = true;

    /**
     * Compile the if statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileIf(string $expression): string
    {
        return "<?php if{$expression}: ?>";
    }

    /**
     * Compile the else-if statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileElseif(string $expression): string
    {
        return "<?php elseif{$expression}: ?>";
    }

    /**
     * Compile the unless statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileUnless(string $expression): string
    {
        return "<?php if ( ! $expression): ?>";
    }

    /**
     * Compile the end unless statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileEndunless(string $expression): string
    {
        return '<?php endif ?>';
    }

    /**
     * Compile the else statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileElse(string $expression): string
    {
        return '<?php else: ?>';
    }

    /**
     * Compile the end-if statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileEndif(string $expression): string
    {
        return '<?php endif ?>';
    }

    /**
     * Compile the switch statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileSwitch(string $expression): string
    {
        $this->firstCaseInSwitch = true;

        return "<?php switch{$expression}:";
    }

    /**
     * Compile the case statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileCase(string $expression): string
    {
        if ($this->firstCaseInSwitch) {
            $this->firstCaseInSwitch = false;

            return "case {$expression}: ?>";
        }

        return "<?php case {$expression}: ?>";
    }

    /**
     * Compile the default statements in switch case into valid PHP.
     *
     * @return string
     */
    protected function compileDefault(): string
    {
        return '<?php default: ?>';
    }

    /**
     * Compile the end switch statements into valid PHP.
     *
     * @return string
     */
    protected function compileEndSwitch(): string
    {
        return '<?php endswitch ?>';
    }

    protected function compileOnce(?string $id = null): string
    {
        $id = $id ? $this->stripParentheses($id) : "'" . uid() . "'";

        return "<?php if (!isset(\$__edge->renderOnce[$id])): \$__edge->renderOnce[$id] = true; ?>";
    }

    protected function compileEndOnce(): string
    {
        return "<?php endif; ?>";
    }

    protected function compilePushOnce(string $expression): string
    {
        $parts = explode(',', $this->stripParentheses($expression), 2);

        [$stack, $id] = [$parts[0], $parts[1] ?? ''];

        $id = trim($id) ?: "'" . uid() . "'";

        return "<?php if (!isset(\$__edge->renderOnce[$id])): \$__edge->renderOnce[$id] = true; " .
            "\$__edge->startPush($stack); ?>";
    }

    protected function compileEndPushOnce(): string
    {
        return '<?php $__edge->stopPush(); endif; ?>';
    }

    protected function compileBool($conditions): string
    {
        return "<?php echo ($conditions ? 'true' : 'false'); ?>";
    }

    protected function compileChecked($conditions): string
    {
        return "<?php echo ($conditions ? 'checked' : ''); ?>";
    }

    protected function compileDisabled($conditions): string
    {
        return "<?php echo ($conditions ? 'disabled' : ''); ?>";
    }

    protected function compileRequired($conditions): string
    {
        return "<?php echo ($conditions ? 'required' : ''); ?>";
    }

    protected function compileReadonly($conditions): string
    {
        return "<?php echo ($conditions ? 'readonly' : ''); ?>";
    }

    protected function compileSelected($conditions): string
    {
        return "<?php echo ($conditions ? 'selected' : ''); ?>";
    }

    protected function compilePushIf($expression): string
    {
        $parts = explode(',', $this->stripParentheses($expression), 2);

        return "<?php if({$parts[0]}): \$__edge->startPush({$parts[1]}); ?>";
    }

    protected function compileElsePushIf($expression): string
    {
        $parts = explode(',', $this->stripParentheses($expression), 2);

        return "<?php \$__edge->stopPush(); elseif({$parts[0]}): \$__edge->startPush({$parts[1]}); ?>";
    }

    protected function compileElsePush($expression): string
    {
        return "<?php \$__edge->stopPush(); else: \$__edge->startPush{$expression}; ?>";
    }

    protected function compileEndPushIf(): string
    {
        return "<?php \$__edge->stopPush(); endif; ?>";
    }
}
