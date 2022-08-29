<?php

namespace AgDevelop\RabbitMqProducerConsumer\Test;

use AgDevelop\Interface\Json\SerializableInterface;
use AgDevelop\Interface\Json\SerializerInterface;
use AgDevelop\RabbitMqProducerConsumer\RabbitMqProducer;
use AgDevelop\RabbitMqProducerConsumer\Routing\RoutingStrategyInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RabbitMqProducerTest extends TestCase
{
    public function testProduce()
    {
        $testKey = 'test.routing-key';
        $serializedMsg = '{"test": 1}';

        $routingStrategyMock = $this->createMock(RoutingStrategyInterface::class);
        $routingStrategyMock->expects($this->once())->method('getRoutingKey')->willReturn($testKey);

        $loggerMock = $this->createMock( LoggerInterface::class);
        $loggerMock->expects($this->once())->method('info')->with('Publishing message with routing key '.$testKey);

        $serializerMock = $this->createMock(SerializerInterface::class);
        $serializerMock->expects($this->once())->method('serialize')->willReturn($serializedMsg);

        $messageMock = $this->createMock(SerializableInterface::class);
       // $messageMock->expects($this->once())->method('jsonSerialize')->willReturn(['test' => 1]);

        $channelMock = $this->createMock( AMQPChannel::class);
        $channelMock->expects($this->once())->method('basic_publish')->with(
            new AMQPMessage($serializedMsg),
            '',
            $testKey,
        );

        $producer = new RabbitMqProducer(
          routingStrategy: $routingStrategyMock,
          channel:  $channelMock,
          logger: $loggerMock,
          serializer: $serializerMock,
        );

        $producer->produce($messageMock);
    }
}
