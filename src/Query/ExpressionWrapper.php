<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Windwalker\Query;

/**
 * The ExpressionWrapper class.
 *
 * @since  __DEPLOY_VERSION__
 */
class ExpressionWrapper
{
    /**
     * Property content.
     *
     * @var  mixed
     * @since  __DEPLOY_VERSION__
     */
    protected $content;

    /**
     * ExpressionWrapper constructor.
     *
     * @param mixed $content
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($content)
    {
        if (!is_string($content) && (!is_object($content) || !method_exists($content, '__toString'))) {
            throw new \InvalidArgumentException('Content must be string or stringable object.');
        }

        $this->content = $content;
    }

    /**
     * __toString
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    public function __toString()
    {
        try {
            return (string) $this->content;
        } catch (\Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
        } catch (\Throwable $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
        }

        return (string) $e;
    }

    /**
     * Method to get property Content
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Method to set property content
     *
     * @param   string $content
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }
}
