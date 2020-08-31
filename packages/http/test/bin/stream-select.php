<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Windwalker\Http\Event\ErrorEvent;
use Windwalker\Http\Event\RequestEvent;
use Windwalker\Http\HttpClient;
use Windwalker\Http\Request\ServerRequestFactory;
use Windwalker\Http\Server\HttpServer;
use Windwalker\Http\Server\PhpServer;
use Windwalker\Http\Transport\StreamTransport;
use Windwalker\Promise\Scheduler\ScheduleRunner;

$autoload = __DIR__ . '/../../vendor/autoload.php';

if (!is_file($autoload)) {
    $autoload = __DIR__ . '/../../../../vendor/autoload.php';
}

include $autoload;

$t1 = new StreamTransport();
$fp1 = $t1->createConnection(
    (new \Windwalker\Http\Request\Request())
        ->withRequestTarget('https://google.com')
);

$t2 = new StreamTransport();
$fp2 = $t1->createConnection(
    (new \Windwalker\Http\Request\Request())
        ->withRequestTarget('https://github.com')
);



$master = array();

$master[] = fopen($fp1);
$master[] = fopen();
$read = $master;
show("Stat: ", fstat($socket));
while (1) {
    $read = $master;
    $_w=$_e=NULL;
    $mod_fd = stream_select($read, $_w , $_e, 5);
    show("Stat: ", fstat($socket));
    foreach ($read as $stream) {
        if ($stream === $socket) {
            $conn = stream_socket_accept($socket);
            fwrite($conn, "Hello! The time is ".date("n/j/Y g:i a")."\n");
            $master[] = $conn;
        } else {
            $sock_data = fread($stream, 1024);
            // var_dump($sock_data);
            if (strlen($sock_data) === 0) { // connection closed
                $key_to_del = array_search($stream, $master, TRUE);
                fclose($stream);
                unset($master[$key_to_del]);
            } else if ($sock_data === FALSE) {
                echo "Something bad happened";
                $key_to_del = array_search($stream, $master, TRUE);
                unset($master[$key_to_del]);
            } else {
                echo "The client has sent :"; var_dump($sock_data);
                fwrite($stream, "You have sent :[".$sock_data."]\n");
                fclose($stream);
                unset($master[array_search($stream, $master)]);
            }
        }
    }
}
