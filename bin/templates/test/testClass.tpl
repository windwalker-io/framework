<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2017 Asikart.
 * @license    __LICENSE__
 */

namespace {{ test.class.namespace }};

use PHPUnit\Framework\TestCase;

/**
 * Test class of \{{ origin.class.name }}
 *
 * @since __DEPLOY_VERSION__
 */
class {{ test.class.shortname }} extends TestCase
{
    /**
     * Test instance.
     *
     * @var \{{ origin.class.name }}
     */
    protected $instance;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->instance = new \{{ origin.class.name }};
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown()
    {
    }
    {{ test.methods }}}
