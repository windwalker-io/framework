<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Application\Test\Mock;

use Windwalker\Http\Response\Response;

/**
 * The MockOutput class.
 *
 * @since  2.0
 */
class MockResponse extends Response
{
    /**
     * Property sentHeaders.
     *
     * @var  string[]
     */
    public $sentHeaders = [];

    /**
     * Property headers.
     *
     * @var  array
     */
    public $headers = [];

    /**
     * Method to send a header to the client.  We are wrapping this to isolate the header() function
     * from our code base for testing reasons.
     *
     * @param   string  $string    The header string.
     * @param   boolean $replace   The optional replace parameter indicates whether the header should
     *                             replace a previous similar header, or add a second header of the same type.
     * @param   integer $code      Forces the HTTP response code to the specified value. Note that
     *                             this parameter only has an effect if the string is not empty.
     *
     * @return  static
     *
     * @since   2.0
     */
    public function header($string, $replace = true, $code = null)
    {
        $this->sentHeaders[] = $string;

        return $this;
    }

    /**
     * Method to check to see if headers have already been sent.  We are wrapping this to isolate the
     * headers_sent() function from our code base for testing reasons.
     *
     * @return  boolean  True if the headers have already been sent.
     *
     * @since   2.0
     */
    public function checkHeadersSent()
    {
        return false;
    }

    /**
     * Method to check the current client connection status to ensure that it is alive.  We are
     * wrapping this to isolate the connection_status() function from our code base for testing reasons.
     *
     * @return  boolean  True if the connection is valid and normal.
     *
     * @since   2.0
     */
    public function checkConnectionAlive()
    {
        return (connection_status() === CONNECTION_NORMAL);
    }
}
