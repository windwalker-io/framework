<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Windwalker\Edge\Compiler\Concern;

/**
 * The CompileComponentTrait class.
 *
 * @since  __DEPLOY_VERSION__
 */
trait CompileComponentTrait
{
    /**
     * Compile the component statements into valid PHP.
     *
     * @param  string  $expression
     * @return string
     */
    protected function compileComponent($expression)
    {
        return "<?php \$this->startComponent{$expression}; ?>";
    }
    
    /**
     * Compile the end-component statements into valid PHP.
     *
     * @return string
     */
    protected function compileEndComponent()
    {
        return '<?php echo $this->renderComponent(); ?>';
    }
    
    /**
     * Compile the slot statements into valid PHP.
     *
     * @param  string  $expression
     * @return string
     */
    protected function compileSlot($expression)
    {
        return "<?php \$this->slot{$expression}; ?>";
    }
    
    /**
     * Compile the end-slot statements into valid PHP.
     *
     * @return string
     */
    protected function compileEndSlot()
    {
        return '<?php $this->endSlot(); ?>';
    }
}
