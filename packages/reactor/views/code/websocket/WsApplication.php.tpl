{% $phpOpen %}

declare(strict_types=1);

namespace {% $ns %};

use Windwalker\Reactor\WebSocket\WebSocketRequestInterface;
use Windwalker\WebSocket\Application\WsRootApplication;

class WsApplication extends WsRootApplication
{
    protected function init(): void
    {
        //
    }

    protected function started(): void
    {
        //
    }

    protected function opening(WebSocketRequestInterface $request): void
    {
        //
    }

    protected function closing(WebSocketRequestInterface $request): void
    {
        //
    }
}
