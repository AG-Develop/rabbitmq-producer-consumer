<?php

namespace AgDevelop\RabbitMqProducerConsumer\Test;

use AgDevelop\Interface\Json\DeserializerBuilderInterface;
use AgDevelop\RabbitMqProducerConsumer\MessageHandlerResolverInterface;
use AgDevelop\RabbitMqProducerConsumer\RabbitMqConsumer;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RabbitMqConsumerTest extends TestCase
{
    private RabbitMqConsumer $consumer;

    public function setUp(): void
    {
        $queueName = 'some-queue';
        $consumerTag = 'some-tag';
        $noLocal = true;
        $noAck = true;
        $exclusive = true;
        $noWait = true;
        $nonBlocking = false;
        $timeout = 1;

        $deserializerBuilderMock = $this->createMock(DeserializerBuilderInterface::class);

        $loggerMock = $this->createMock(LoggerInterface::class);

        $handlerResolverMock  = $this->createMock(MessageHandlerResolverInterface::class);

        $channelMock = $this->createMock( AMQPChannel::class);
        $channelMock->expects($this->once())->method('basic_consume')->with(
            queue: $queueName,
            consumer_tag: $consumerTag,
            no_local: $noLocal,
            no_ack: $noAck,
            exclusive: $exclusive,
            no_wait: $noWait,
        );

        $consumer = new RabbitMqConsumer(
            channel: $channelMock,
            deserializerBuilder: $deserializerBuilderMock,
            handlerResolver: $handlerResolverMock,
            logger: $loggerMock,
            queueName: $queueName,
            consumerTag: $consumerTag,
            noLocal: $noLocal,
            noAck: $noAck,
            exclusive: $exclusive,
            noWait: $noWait,
            nonBlocking: $nonBlocking,
            timeout: 1,
        );

        $this->consumer = $consumer;
    }

    public function testConsume()
    {
        $this->consumer->init();
    }
}
