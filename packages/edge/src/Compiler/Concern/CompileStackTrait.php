<?php

declare(strict_types=1);

namespace Windwalker\Edge\Compiler\Concern;

/**
 * Trait CompileStockTrait
 */
trait CompileStackTrait
{
    /**
     * Compile the stack statements into the content.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileStack(string $expression): string
    {
        return "<?php echo \$__edge->yieldPushContent{$expression}; ?>";
    }

    /**
     * Compile the push statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compilePush(string $expression): string
    {
        return "<?php \$__edge->startPush{$expression}; ?>";
    }

    /**
     * Compile the endpush statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileEndpush(string $expression): string
    {
        return '<?php $__edge->stopPush(); ?>';
    }
}
