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

namespace spec\Sulu\SyliusProducerPlugin\Producer;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sulu\Bundle\SyliusConsumerBundle\Model\Product\Message\RemoveTaxonMessage;
use Sulu\Bundle\SyliusConsumerBundle\Model\Product\Message\SynchronizeTaxonMessage;
use Sulu\SyliusProducerPlugin\Producer\ProductVariantMessageProducerInterface;
use Sulu\SyliusProducerPlugin\Producer\TaxonMessageProducer;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class TaxonMessageProducerSpec extends ObjectBehavior
{
    public function let(
        SerializerInterface $serializer,
        MessageBusInterface $messageBus
    ): void {
        $this->beConstructedWith($serializer, $messageBus);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(TaxonMessageProducer::class);
    }

    public function it_should_dispatch_synchronize_message(
        SerializerInterface $serializer,
        MessageBusInterface $messageBus,
        TaxonInterface $taxon
    ): void {
        $taxon->getId()->willReturn(1);
        $taxon->getParent()->willReturn(null);
        $serializer->serialize($taxon, 'json', Argument::type(SerializationContext::class))->shouldBeCalled()->willReturn('{"id": 1}');

        $this->synchronize($taxon);

        $messageBus->dispatch(
            Argument::that(
                function (SynchronizeTaxonMessage $message) {
                    return 1 === $message->getId()
                        && ['id' => 1] === $message->getPayload();
                }
            )
        )->shouldBeCalled();
    }

    public function it_should_dispatch_synchronize_message_with_root(
        SerializerInterface $serializer,
        MessageBusInterface $messageBus,
        TaxonInterface $taxon,
        TaxonInterface $parent
    ): void {
        $taxon->getId()->willReturn(1);
        $taxon->getParent()->willReturn($parent);

        $parent->getId()->willReturn(9);
        $parent->getParent()->willReturn(null);

        $serializer->serialize($parent, 'json', Argument::type(SerializationContext::class))->shouldBeCalled()->willReturn('{"id": 9}');

        $this->synchronize($taxon);

        $messageBus->dispatch(
            Argument::that(
                function (SynchronizeTaxonMessage $message) {
                    return 9 === $message->getId()
                        && ['id' => 9] === $message->getPayload();
                }
            )
        )->shouldBeCalled();
    }

    public function it_should_dispatch_remove_message(
        MessageBusInterface $messageBus,
        TaxonInterface $taxon
    ): void {
        $taxon->getId()->willReturn(79);

        $this->remove($taxon);

        $messageBus->dispatch(
            Argument::that(
                function (RemoveTaxonMessage $message) {
                    return 79 === $message->getId();
                }
            )
        )->shouldBeCalled();
    }
}
