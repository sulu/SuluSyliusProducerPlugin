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

namespace spec\Sulu\SyliusProducerPlugin\EventSubscriber;

use PhpSpec\ObjectBehavior;
use Sulu\SyliusProducerPlugin\EventSubscriber\ProductEventSubscriber;
use Sulu\SyliusProducerPlugin\Producer\ProductMessageProducerInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ProductEventSubscriberSpec extends ObjectBehavior
{
    public function let(ProductMessageProducerInterface $messageProducer): void
    {
        $this->beConstructedWith($messageProducer);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ProductEventSubscriber::class);
    }

    public function it_should_call_synchronize(
        ProductMessageProducerInterface $messageProducer,
        GenericEvent $event,
        ProductInterface $product
    ): void {
        $event->getSubject()->willReturn($product);

        $this->synchronize($event);

        $messageProducer->synchronize($product)->shouldBeCalled();
    }

    public function it_should_not_call_synchronize_on_other_subject(
        ProductMessageProducerInterface $messageProducer,
        GenericEvent $event,
        \stdClass $product
    ): void {
        $event->getSubject()->willReturn($product);

        $this->synchronize($event);

        $messageProducer->synchronize($product)->shouldNotBeCalled();
    }

    public function it_should_call_remove(
        ProductMessageProducerInterface $messageProducer,
        GenericEvent $event,
        ProductInterface $product
    ): void {
        $event->getSubject()->willReturn($product);

        $this->remove($event);

        $messageProducer->remove($product)->shouldBeCalled();
    }

    public function it_should_not_call_remove_on_other_subject(
        ProductMessageProducerInterface $messageProducer,
        GenericEvent $event,
        \stdClass $product
    ): void {
        $event->getSubject()->willReturn($product);

        $this->remove($event);

        $messageProducer->remove($product)->shouldNotBeCalled();
    }
}
