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
            'sylius.taxon.pre_delete' => 'preRemove',
            'sylius.taxon.post_delete' => 'postRemove',
        ];
    }

    /**
     * @var array
     */
    private $idsCache = [];

    /**
     * @var TaxonMessageProducerInterface
     */
    private $messageProducer;

    public function __construct(TaxonMessageProducerInterface $messageProducer)
    {
        $this->messageProducer = $messageProducer;
    }

    public function synchronize(GenericEvent $event): void
    {
        $taxon = $event->getSubject();
        if (!$taxon instanceof TaxonInterface) {
            return;
        }

        $this->messageProducer->synchronize($taxon);
    }

    public function preRemove(GenericEvent $event): void
    {
        $taxon = $event->getSubject();
        if (!$taxon instanceof TaxonInterface) {
            return;
        }

        $this->idsCache[spl_object_hash($taxon)] = $taxon->getId();
    }

    public function postRemove(GenericEvent $event): void
    {
        $taxon = $event->getSubject();
        $hash = spl_object_hash($taxon);
        if (!$taxon instanceof TaxonInterface || !array_key_exists($hash, $this->idsCache)) {
            return;
        }

        $this->messageProducer->remove($this->idsCache[$hash]);
    }
}
