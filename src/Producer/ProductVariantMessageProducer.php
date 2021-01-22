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

use Sulu\Bundle\SyliusConsumerBundle\Message\RemoveProductVariantMessage;
use Sulu\Bundle\SyliusConsumerBundle\Message\SynchronizeProductVariantMessage;
use Sylius\Component\Core\Model\ProductVariantInterface;

class ProductVariantMessageProducer extends BaseMessageProducer implements ProductVariantMessageProducerInterface
{
    public function synchronize(ProductVariantInterface $productVariant): void
    {
        $payload = $this->serialize($productVariant);

        $product = $productVariant->getProduct();
        if (!$product) {
            return;
        }

        $code = $product->getCode();
        $variantCode = $productVariant->getCode();
        if (!$code || !$variantCode) {
            throw new \RuntimeException();
        }

        $message = new SynchronizeProductVariantMessage($code, $variantCode, $payload);
        $this->getMessageBus()->dispatch($message);
    }

    public function remove(ProductVariantInterface $productVariant): void
    {
        $variantCode = $productVariant->getCode();
        if (!$variantCode) {
            throw new \RuntimeException();
        }

        $message = new RemoveProductVariantMessage($variantCode);
        $this->getMessageBus()->dispatch($message);
    }
}
