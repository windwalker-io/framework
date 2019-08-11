<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Http\Response;

use Psr\Http\Message\StreamInterface;
use Windwalker\Http\Stream\NullStream;
use Windwalker\Http\Stream\Stream;

/**
 * The EmptyResponse class.
 *
 * Always return empty data and is only readable. THe headers will still send.
 *
 * @since  3.0
 */
class EmptyResponse extends Response
{
    /**
     * Constructor.
     *
     * @param  int   $status  The status code.
     * @param  array $headers The custom headers.
     */
    public function __construct($status = 204, array $headers = [])
    {
        parent::__construct(new NullStream(), $status, $headers);
    }
}
