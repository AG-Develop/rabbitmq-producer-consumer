<?php

namespace AgDevelop\RabbitMqProducerConsumer;

interface ConsumerInterface
{
    public function consume(): void;
}