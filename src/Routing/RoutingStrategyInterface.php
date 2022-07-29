<?php

namespace AgDevelop\RabbitMqProducerConsumer\Routing;

interface RoutingStrategyInterface
{
    public function getRoutingKey(object $object): string;
}
