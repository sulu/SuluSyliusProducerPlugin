<?php

declare(strict_types=1);

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\SyliusProducerPlugin\Producer;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

abstract class BaseMessageProducer
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    public function __construct(SerializerInterface $serializer, MessageBusInterface $messageBus)
    {
        $this->serializer = $serializer;
        $this->messageBus = $messageBus;
    }

    protected function serialize(Object $object): array
    {
        $serializationContext = new SerializationContext();
        $serializationContext->setGroups(['Default', 'Detailed']);

        return json_decode(
            $this->serializer->serialize($object, 'json', $serializationContext),
            true
        );
    }

    protected function getMessageBus(): MessageBusInterface
    {
        return $this->messageBus;
    }
}
