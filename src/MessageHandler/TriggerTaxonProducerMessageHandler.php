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

use Sulu\SyliusProducerPlugin\Message\TriggerTaxonProducerMessage;
use Sulu\SyliusProducerPlugin\Producer\TaxonMessageProducerInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class TriggerTaxonProducerMessageHandler
{
    /**
     * @param TaxonRepositoryInterface<TaxonInterface> $taxonRepository
     */
    public function __construct(
        private TaxonRepositoryInterface $taxonRepository,
        private TaxonMessageProducerInterface $producer,
    ) {
    }

    public function __invoke(TriggerTaxonProducerMessage $message): void
    {
        /** @var TaxonInterface[] $taxon */
        $taxon = $this->taxonRepository->findBy(['id' => $message->taxonIds]);
        if (0 === \count($taxon)) {
            return;
        }

        $this->producer->synchronize($taxon);
    }
}
