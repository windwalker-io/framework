<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Http\Output;

use Psr\Http\Message\ResponseInterface;

/**
 * OutputInterface
 *
 * @since  3.0
 */
interface OutputInterface
{
    /**
     * Method to send the application response to the client.  All headers will be sent prior to the main
     * application output data.
     *
     * @param   ResponseInterface $response   Respond body output.
     * @param   boolean           $returnBody Return body as string.
     *
     * @return  ResponseInterface
     *
     * @since   3.0
     */
    public function respond(ResponseInterface $response, $returnBody = false);
}
