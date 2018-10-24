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

use Sulu\Bundle\SyliusConsumerBundle\Model\Product\Message\RemoveProductVariantMessage;
use Sulu\Bundle\SyliusConsumerBundle\Model\Product\Message\SynchronizeProductVariantMessage;
use Sylius\Component\Core\Model\ProductVariantInterface;

class ProductVariantMessageProducer extends BaseMessageProducer implements ProductVariantMessageProducerInterface
{
    public function synchronize(ProductVariantInterface $productVariant): void
    {
        $payload = $this->serialize($productVariant);

        $message = new SynchronizeProductVariantMessage(
            $productVariant->getProduct()->getCode(),
            $productVariant->getCode(),
            $payload
        );

        $this->getMessageBus()->dispatch($message);
    }

    public function remove(ProductVariantInterface $productVariant): void
    {
        $message = new RemoveProductVariantMessage($productVariant->getCode());

        $this->getMessageBus()->dispatch($message);
    }
}
