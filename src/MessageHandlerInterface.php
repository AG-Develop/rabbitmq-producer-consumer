<?php

namespace AgDevelop\RabbitMqProducerConsumer;

interface MessageHandlerInterface
{
    public function handle(object $object);
}
