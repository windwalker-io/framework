<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Session\Handler;

/**
 * Class AbstractHandler
 *
 * @since 2.0
 */
abstract class AbstractHandler implements HandlerInterface
{
    /**
     * Property prefix.
     *
     * @var  string
     */
    protected $prefix = null;

    /**
     * Class init.
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->prefix = $options['prefix'] ?? 'wws_';
    }

    /**
     * register
     *
     * @return  mixed
     */
    public function register()
    {
        if (!headers_sent()) {
            session_set_save_handler($this, true);
        }
    }
}
