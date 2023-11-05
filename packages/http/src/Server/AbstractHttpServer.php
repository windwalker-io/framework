<?php

declare(strict_types=1);

namespace Windwalker\Http\Server;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Windwalker\Http\Event\ErrorEvent;
use Windwalker\Http\Event\RequestEvent;
use Windwalker\Http\Event\ResponseEvent;
use Windwalker\Http\Helper\ResponseHelper;
use Windwalker\Http\HttpFactory;
use Windwalker\Http\Middleware\RequestRunner;
use Windwalker\Http\Output\OutputInterface;
use Windwalker\Http\Output\StreamOutput;
use Windwalker\Http\Response\Response;

/**
 * The AbstractHttpServer class.
 *
 * @deprecated  Use HttpServerTrait
 */
abstract class AbstractHttpServer extends AbstractServer implements HttpServerInterface
{
    //
}
