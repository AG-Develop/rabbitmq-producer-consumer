<?php

namespace AgDevelop\RabbitMqProducerConsumer;

use AgDevelop\Interface\Json\DeserializerBuilderInterface;
use AgDevelop\Interface\Json\DeserializerInterface;
use AgDevelop\RabbitMqProducerConsumer\Exception\HandlerException;
use AgDevelop\RabbitMqProducerConsumer\Exception\UnableToDetermineHandlerException;
use DateTimeInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class RabbitMqConsumer implements ConsumerInterface
{
    public function __construct(
        private AMQPChannel $channel,
        private DeserializerBuilderInterface $deserializerBuilder,
        private MessageHandlerResolverInterface $handlerResolver,
        private ?LoggerInterface $logger,
        private string $queueName = '',
    ) {
    }

    /**
     * @throws HandlerException, \Exception
     */
    public function consume(): void
    {
        $this->channel->basic_consume(
            queue: $this->queueName,
            callback: function (AMQPMessage $msg) {
                try {
                    $deserializer = $this->deserializerBuilder->build($msg->body);
                    $object = $deserializer->deserialize()->getObject();
                    $envelope = $deserializer->getEnvelope();
                    $this->logger?->info(sprintf('Message %s@%s received, sent at %s ',
                        (new \ReflectionObject($object))->getShortName(),
                        $envelope->getVersion(),
                        $envelope->getSerializedAt()->format(DateTimeInterface::ATOM),
                    ));
                    $handler = $this->handlerResolver->getHandlerFor($object);
                    $handler->handle($object);
                    $this->logger?->info('Message handled - sending ACK to Queue Manager');
                    $msg->ack();
                } catch (UnableToDetermineHandlerException $exception) {
                    /* only unknown handler exceptions are to be handled here since we need to send ACK if no
                       matching handler is provided; all other exceptions should be passed out of this method */
                    $this->logger?->warning('Could not determine handler - sending ACK to Queue Manager');
                    $msg->ack();
                }
            },
        );
    }
}