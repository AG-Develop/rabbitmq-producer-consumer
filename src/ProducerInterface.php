<?php

namespace AgDevelop\RabbitMqProducerConsumer;

use AgDevelop\Interface\Json\SerializableInterface;

interface ProducerInterface
{
    public function produce(SerializableInterface $message): void;
}