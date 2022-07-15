<?php

namespace AgDevelop\RabbitMqProducerConsumer;

use AgDevelop\Interface\Json\DeserializerBuilderInterface;
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
        private string $consumerTag = '',
        private bool $noLocal = false,
        private bool $noAck = false,
        private bool $exclusive = false,
        private bool $noWait = false,
        private bool $nonBlocking = true,
        private int $timeout = 0,
    ) {
    }

    public function init(): void
    {
        /*
            queue: Queue from where to get the messages
            consumer_tag: Consumer identifier
            no_local: Don't receive messages published by this consumer.
            no_ack: If set to true, automatic acknowledgement mode will be used by this consumer. See https://www.rabbitmq.com/confirms.html for details.
            exclusive: Request exclusive consumer access, meaning only this consumer can access the queue
            nowait:
            callback: A PHP Callback
        */

        $this->channel->basic_consume(
            queue: $this->queueName,
            consumer_tag: $this->consumerTag,
            no_local: $this->noLocal,
            no_ack: $this->noAck,
            exclusive: $this->exclusive,
            nowait: $this->noWait,
            callback: function (AMQPMessage $message) {
                try {
                    $deserializer = $this->deserializerBuilder->build($message->body);
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
                    $message->ack();
                } catch (UnableToDetermineHandlerException $exception) {
                    /* only unknown handler exceptions are to be handled here since we need to send ACK if no
                       matching handler is provided; all other exceptions should be passed out of this method */
                    $this->logger?->warning('Could not determine handler - sending ACK to Queue Manager');
                    $message->ack();
                }
            },
        );
    }

    /**
     * @throws HandlerException, \Exception
     */
    public function consume(): void
    {
        $this->logger?->debug('Consuming...');
        $this->channel->wait(null, $this->nonBlocking, $this->timeout);
    }
}
