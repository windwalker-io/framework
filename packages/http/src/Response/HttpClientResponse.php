<?php

declare(strict_types=1);

namespace Windwalker\Http\Response;

/**
 * The HttpClientResponse class.
 */
class HttpClientResponse extends Response
{
    /**
     * Same details info from connection handler like CURL or socket.
     *
     * @var mixed
     */
    protected mixed $info = null;

    public function withInfo(mixed $info): static
    {
        $new = clone $this;
        $new->info = $info;

        return $new;
    }

    public function getInfo(): mixed
    {
        return $this->info;
    }
}
