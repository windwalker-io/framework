<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Edge\Concern;

/**
 * The ComponentConcernTrait class.
 *
 * @since  3.3.1
 */
trait ManageComponentTrait
{
    /**
     * The components being rendered.
     *
     * @var array
     */
    protected $componentStack = [];

    /**
     * The original data passed to the component.
     *
     * @var array
     */
    protected $componentData = [];

    /**
     * The slot contents for the component.
     *
     * @var array
     */
    protected $slots = [];

    /**
     * The names of the slots being rendered.
     *
     * @var array
     */
    protected $slotStack = [];

    /**
     * Start a component rendering process.
     *
     * @param  string $name
     * @param  array  $data
     * @param  array  $more
     *
     * @return void
     */
    public function startComponent($name, array $data = [], array $more = [])
    {
        if (ob_start()) {
            $this->componentStack[] = $name;
            $this->componentData[$this->currentComponent()] = array_merge($more, $data);
            $this->slots[$this->currentComponent()] = [];
        }
    }

    /**
     * Render the current component.
     *
     * @return string
     */
    public function renderComponent()
    {
        $name = array_pop($this->componentStack);

        return $this->render($name, $this->componentData($name));
    }

    /**
     * Get the data for the given component.
     *
     * @param  string $name
     *
     * @return array
     */
    protected function componentData($name)
    {
        return array_merge(
            $this->componentData[count($this->componentStack)],
            ['slot' => trim(ob_get_clean())],
            $this->slots[count($this->componentStack)]
        );
    }

    /**
     * Start the slot rendering process.
     *
     * @param  string      $name
     * @param  string|null $content
     *
     * @return void
     */
    public function slot($name, $content = null)
    {
        if (count(\func_get_args()) === 2) {
            $this->slots[$this->currentComponent()][$name] = $content;
        } else {
            if (ob_start()) {
                $this->slots[$this->currentComponent()][$name] = '';
                $this->slotStack[$this->currentComponent()][] = $name;
            }
        }
    }

    /**
     * Save the slot content for rendering.
     *
     * @return void
     */
    public function endSlot()
    {
        end($this->componentStack);
        $currentSlot = array_pop(
            $this->slotStack[$this->currentComponent()]
        );
        $this->slots[$this->currentComponent()]
        [$currentSlot] = trim(ob_get_clean());
    }

    /**
     * Get the index for the current component.
     *
     * @return int
     */
    protected function currentComponent()
    {
        return count($this->componentStack) - 1;
    }
}
