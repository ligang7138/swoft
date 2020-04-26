<?php declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Listener;

use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Consul\Agent;
use Swoft\Event\Annotation\Mapping\Listener;
use Swoft\Event\EventHandlerInterface;
use Swoft\Event\EventInterface;
use Swoft\Http\Server\HttpServer;
use Swoft\Server\SwooleEvent;

/**
 * Class RegisterServiceListener
 *
 * @since 2.0
 *
 * @Listener(event=SwooleEvent::START)
 */
class RegisterServiceListener implements EventHandlerInterface
{
    /**
     * @Inject()
     *
     * @var Agent
     */
    private $agent;

    /**
     * @param EventInterface $event
     */
    public function handle(EventInterface $event): void
    {

        /** @var HttpServer $httpServer */
        $httpServer = $event->getTarget();

        // 判断有没有rpc端口的监听
        $rpcListener = $httpServer->getListener();

        if(!isset($rpcListener['rpc']) || empty($rpcListener['rpc'])){
            return;
        }

        $port = $rpcListener['rpc']->getPort();

        $service = [
            'ID'                => 'swoft',
            'Name'              => 'swoft',
            'Tags'              => [
                'http'
            ],
            'Address'           => '127.0.0.1',
            'Port'              => $port,
            'Check'             => [
                'tcp'      => '127.0.0.1:'.$port,
                'interval' => '10s',
                'timeout'  => '2s',
            ],
            'Meta'              => [
                'version' => '1.0'
            ],
            'EnableTagOverride' => false,
            'Weights'           => [
                'Passing' => 10,
                'Warning' => 1
            ]
        ];

        // Register
        $this->agent->registerService($service);
        CLog::info('Swoft http register service success by consul!');

    }
}
