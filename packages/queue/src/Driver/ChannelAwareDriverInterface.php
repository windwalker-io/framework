<?php

declare(strict_types=1);

namespace Windwalker\Queue\Driver;

interface ChannelAwareDriverInterface
{
    /**
     * @return  iterable<string>
     */
    public function getChannels(): iterable;
}
