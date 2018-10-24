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

use JMS\Serializer\SerializerInterface;
use Sulu\Bundle\SyliusConsumerBundle\Model\Product\Message\RemoveProductMessage;
use Sulu\Bundle\SyliusConsumerBundle\Model\Product\Message\SynchronizeProductMessage;
use Sylius\Component\Core\Model\ProductInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ProductMessageProducer extends BaseMessageProducer implements ProductMessageProducerInterface
{
    /**
     * @var ProductVariantMessageProducerInterface
     */
    private $productVariantMessageProducer;

    public function __construct(
        SerializerInterface $serializer,
        MessageBusInterface $messageBus,
        ProductVariantMessageProducerInterface $productVariantMessageProducer
    ) {
        parent::__construct($serializer, $messageBus);
        $this->productVariantMessageProducer = $productVariantMessageProducer;
    }

    public function synchronize(ProductInterface $product): void
    {
        $payload = $this->serialize($product);
        $message = new SynchronizeProductMessage($product->getCode(), $payload);
        $this->getMessageBus()->dispatch($message);

        if ($product->isSimple()) {
            foreach ($product->getVariants() as $variant) {
                $this->productVariantMessageProducer->synchronize($variant);
            }
        }
    }

    public function remove(ProductInterface $product): void
    {
        $message = new RemoveProductMessage($product->getCode());

        $this->getMessageBus()->dispatch($message);
    }
}
