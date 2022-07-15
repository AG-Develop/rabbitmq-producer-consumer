<?php

namespace AgDevelop\RabbitMqProducerConsumer;

use AgDevelop\RabbitMqProducerConsumer\Exception\UnableToDetermineHandlerException;

interface MessageHandlerResolverInterface
{
    /**
     * @throws UnableToDetermineHandlerException
     */
    public function getHandlerFor(object $object): MessageHandlerInterface;
}