<?php

declare(strict_types=1);

namespace Windwalker\Http\Response;

use Windwalker\Http\Output\OutputInterface;
use Windwalker\Http\Response\Response;

class CallbackResponse extends Response
{
    /**
     * @var callable
     */
    public protected(set) mixed $callback = null;

    public function __construct(?callable $callback = null, int $status = 200, array $headers = [])
    {
        $this->callback = $callback;

        parent::__construct(status: $status, headers: $headers);
    }

    public function respond(OutputInterface $output): void
    {
        if ($this->callback) {
            ($this->callback)($output);
        }
    }

    public function withCallback(callable $callback): static
    {
        $new = clone $this;
        $new->callback = $callback;

        return $new;
    }
}
