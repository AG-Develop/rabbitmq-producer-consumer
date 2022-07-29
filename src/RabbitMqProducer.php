<?php

namespace AgDevelop\MessageProducerConsumer;

use AgDevelop\Interface\Json\SerializableInterface;
use AgDevelop\Interface\Json\SerializerInterface;
use AgDevelop\RabbitMqProducerConsumer\ProducerInterface;
use AgDevelop\RabbitMqProducerConsumer\Routing\RoutingStrategyInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class RabbitMqProducer implements ProducerInterface
{
    public function __construct(
        private SerializerInterface $serializer,
        private RoutingStrategyInterface $routingStrategy,
        private AMQPChannel $channel,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function produce(SerializableInterface $message): void
    {
        $amqpMessage = new AMQPMessage($this->serializer->serialize($message));
        $routingKey = $this->routingStrategy->getRoutingKey($message);

        $this->logger?->info('Publishing message with routing key '.$routingKey);
        $this->channel->basic_publish(
            msg: $amqpMessage,
            routing_key: $routingKey,
        );
    }
}
