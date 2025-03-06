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

use Sulu\SyliusProducerPlugin\Message\TriggerProductProducerMessage;
use Sulu\SyliusProducerPlugin\Producer\ProductMessageProducerInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Product\Repository\ProductRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class TriggerProductProducerMessageHandler
{
    /**
     * @param ProductRepositoryInterface<ProductInterface> $productRepository
     */
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private ProductMessageProducerInterface $producer,
    ) {
    }

    public function __invoke(TriggerProductProducerMessage $message): void
    {
        /** @var ProductInterface|null $product */
        $product = $this->productRepository->find($message->productId);
        if (null === $product) {
            return;
        }

        $this->producer->synchronize($product);
    }
}
