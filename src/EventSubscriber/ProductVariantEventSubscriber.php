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

use Sulu\SyliusProducerPlugin\Producer\ProductVariantMessageProducerInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ProductVariantEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'sylius.product_variant.post_create' => 'synchronize',
            'sylius.product_variant.post_update' => 'synchronize',
            'sylius.product_variant.post_delete' => 'remove',
        ];
    }

    /**
     * @var ProductVariantMessageProducerInterface
     */
    private $messageProducer;

    public function __construct(ProductVariantMessageProducerInterface $messageProducer)
    {
        $this->messageProducer = $messageProducer;
    }

    public function synchronize(GenericEvent $event)
    {
        $productVariant = $event->getSubject();
        if (!$productVariant instanceof ProductVariantInterface) {
            return;
        }

        $this->messageProducer->synchronize($productVariant);
    }

    public function remove(GenericEvent $event)
    {
        $productVariant = $event->getSubject();
        if (!$productVariant instanceof ProductVariantInterface) {
            return;
        }

        $this->messageProducer->remove($productVariant);
    }
}
