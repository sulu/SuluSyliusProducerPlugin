<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\SyliusProducerPlugin\MessageHandler;

use Sulu\SyliusProducerPlugin\Message\TriggerProductVariantProducerMessage;
use Sulu\SyliusProducerPlugin\Producer\ProductVariantMessageProducerInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Repository\ProductVariantRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class TriggerProductVariantProducerMessageHandler
{
    /**
     * @param ProductVariantRepositoryInterface<ProductVariantInterface> $productVariantRepository
     */
    public function __construct(
        private ProductVariantRepositoryInterface $productVariantRepository,
        private ProductVariantMessageProducerInterface $producer,
    ) {
    }

    public function __invoke(TriggerProductVariantProducerMessage $message): void
    {
        /** @var ProductVariantInterface|null $product */
        $product = $this->productVariantRepository->find($message->productVariantId);
        if (!$product) {
            return;
        }

        $this->producer->synchronize($product);
    }
}
