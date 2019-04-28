<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Http\Response;

/**
 * The HtmlResponse class.
 *
 * @since  3.0
 */
class HtmlResponse extends TextResponse
{
    /**
     * Content type.
     *
     * @var  string
     */
    protected $type = 'text/html';
}
