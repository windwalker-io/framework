<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Form\Test\Mock;

use Windwalker\Filter\FilterInterface;

/**
 * The MockFilter class.
 *
 * @since  2.0
 */
class MockFilter implements FilterInterface
{
    /**
     * clean
     *
     * @param string $text
     *
     * @return  mixed
     */
    public function filter($text)
    {
        return 'abc';
    }
}
