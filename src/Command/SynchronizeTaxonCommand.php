<?php

declare(strict_types=1);

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\SyliusProducerPlugin\Command;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\SyliusProducerPlugin\Message\TriggerTaxonProducerMessage;
use Sylius\Component\Taxonomy\Model\TaxonInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class SynchronizeTaxonCommand extends BaseSynchronizeCommand
{
    /** @param TaxonRepositoryInterface<TaxonInterface> $taxonRepository */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TaxonRepositoryInterface $taxonRepository,
        private MessageBusInterface $messageBus,
    ) {
        parent::__construct($entityManager);
    }

    protected function configure(): void
    {
        $this->setName('sulu-sylius:synchronize:taxon')
            ->setDescription('Synchronize taxon tree to Sulu');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // disable logger because of memory issues
        $this->entityManager->getConfiguration()->setSQLLogger(null);
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
        \gc_enable();

        $this->syncTaxonTree($output);

        return 0;
    }

    private function syncTaxonTree(OutputInterface $output): void
    {
        $output->writeln('<info>Sync taxon tree</info>');

        $taxons = [];
        foreach ($this->taxonRepository->findRootNodes() as $rootTaxon) {
            if (!$rootTaxon instanceof TaxonInterface) {
                continue;
            }

            $taxons = \array_merge($taxons, $this->extractChildrenFlat($rootTaxon));
        }

        $taxonIds = \array_map(
            fn (TaxonInterface $taxon) => $taxon->getId(),
            $taxons,
        );
        $this->messageBus->dispatch(new TriggerTaxonProducerMessage($taxonIds));
    }

    private function extractChildrenFlat(TaxonInterface $rootTaxon): array
    {
        $taxons = [$rootTaxon];
        foreach ($rootTaxon->getChildren() as $childTaxon) {
            $taxons = \array_merge($taxons, $this->extractChildrenFlat($childTaxon));
        }

        return $taxons;
    }
}
