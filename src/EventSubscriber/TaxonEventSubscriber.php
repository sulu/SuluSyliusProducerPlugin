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

namespace Sulu\SyliusProducerPlugin\EventSubscriber;

use Sulu\SyliusProducerPlugin\Producer\TaxonMessageProducerInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class TaxonEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'sylius.taxon.post_create' => 'synchronize',
            'sylius.taxon.post_update' => 'synchronize',
            'sylius.taxon.post_delete' => 'remove',
        ];
    }

    /**
     * @var TaxonMessageProducerInterface
     */
    private $messageProducer;

    public function __construct(TaxonMessageProducerInterface $messageProducer)
    {
        $this->messageProducer = $messageProducer;
    }

    public function synchronize(GenericEvent $event)
    {
        $taxon = $event->getSubject();
        if (!$taxon instanceof TaxonInterface) {
            return;
        }

        $this->messageProducer->synchronize($taxon);
    }

    public function remove(GenericEvent $event)
    {
        $taxon = $event->getSubject();
        if (!$taxon instanceof TaxonInterface) {
            return;
        }

        $this->messageProducer->remove($taxon);
    }
}
