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
use Sulu\SyliusProducerPlugin\EventSubscriber\TaxonEventSubscriber;
use Sulu\SyliusProducerPlugin\Producer\TaxonMessageProducerInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class TaxonEventSubscriberSpec extends ObjectBehavior
{
    public function let(TaxonMessageProducerInterface $messageProducer): void
    {
        $this->beConstructedWith($messageProducer);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(TaxonEventSubscriber::class);
    }

    public function it_should_call_synchronize(
        TaxonMessageProducerInterface $messageProducer,
        GenericEvent $event,
        TaxonInterface $taxon
    ): void {
        $event->getSubject()->willReturn($taxon);

        $this->synchronize($event);

        $messageProducer->synchronize($taxon)->shouldBeCalled();
    }

    public function it_should_not_call_synchronize_on_other_subject(
        TaxonMessageProducerInterface $messageProducer,
        GenericEvent $event,
        \stdClass $taxon
    ): void {
        $event->getSubject()->willReturn($taxon);

        $this->synchronize($event);

        $messageProducer->synchronize($taxon)->shouldNotBeCalled();
    }

    public function it_should_call_remove(
        TaxonMessageProducerInterface $messageProducer,
        GenericEvent $event,
        TaxonInterface $taxon
    ): void {
        $event->getSubject()->willReturn($taxon);

        $this->remove($event);

        $messageProducer->remove($taxon)->shouldBeCalled();
    }

    public function it_should_not_call_remove_on_other_subject(
        TaxonMessageProducerInterface $messageProducer,
        GenericEvent $event,
        \stdClass $taxon
    ): void {
        $event->getSubject()->willReturn($taxon);

        $this->remove($event);

        $messageProducer->remove($taxon)->shouldNotBeCalled();
    }
}
