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

use Sulu\SyliusProducerPlugin\Producer\ProductMessageProducerInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ProductEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'sylius.product.post_create' => 'synchronize',
            'sylius.product.post_update' => 'synchronize',
            'sylius.product.post_delete' => 'remove',
        ];
    }

    /**
     * @var ProductMessageProducerInterface
     */
    private $messageProducer;

    public function __construct(ProductMessageProducerInterface $messageProducer)
    {
        $this->messageProducer = $messageProducer;
    }

    public function synchronize(GenericEvent $event):void
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }

        $this->messageProducer->synchronize($product);
    }

    public function remove(GenericEvent $event):void
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }

        $this->messageProducer->remove($product);
    }
}
