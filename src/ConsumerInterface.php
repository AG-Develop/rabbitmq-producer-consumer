<?php

namespace AgDevelop\RabbitMqProducerConsumer;

interface ConsumerInterface
{
    public function init(): void;

    public function consume(): void;
}
