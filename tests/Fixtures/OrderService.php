<?php

namespace Tivins\DI\Tests\Fixtures;

class OrderService
{
    public function __construct(
        private LoggerInterface $logger,
        private Config $config,
    ) {
    }

    public function placeOrder(string $orderId): string
    {
        $this->logger->log("Order placed: {$orderId} (env={$this->config->getEnv()})");
        return "order:{$orderId}";
    }
}
