<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Event;

use Windwalker\Event\AbstractEvent;

/**
 * The PacketEvent class.
 */
class PacketEvent extends AbstractEvent
{
    use ServerEventTrait;

    protected string $data;

    protected array $clientInfo;

    public function getData(): string
    {
        return $this->data;
    }

    public function setData(string $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function getClientInfo(): array
    {
        return $this->clientInfo;
    }

    public function setClientInfo(array $clientInfo): static
    {
        $this->clientInfo = $clientInfo;

        return $this;
    }
}
