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

namespace Sulu\SyliusProducerPlugin\Command;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\SyliusProducerPlugin\Producer\ProductMessageProducerInterface;
use Sulu\SyliusProducerPlugin\Producer\ProductVariantMessageProducerInterface;
use Sulu\SyliusProducerPlugin\Producer\TaxonMessageProducerInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Core\Repository\ProductVariantRepositoryInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SynchronizeTaxonCommand extends BaseSynchronizeCommand
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TaxonMessageProducerInterface
     */
    private $taxonMessageProducer;

    /**
     * @var TaxonRepositoryInterface
     */
    private $taxonRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        TaxonMessageProducerInterface $taxonMessageProducer,
        TaxonRepositoryInterface $taxonRepository
    ) {
        parent::__construct($entityManager);

        $this->entityManager = $entityManager;
        $this->taxonMessageProducer = $taxonMessageProducer;
        $this->taxonRepository = $taxonRepository;
    }

    protected function configure()
    {
        $this->setName('sulu-sylius:synchronize:taxon')
            ->setDescription('Synchronize taxon tree to Sulu');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // disable logger because of memory issues
        $this->entityManager->getConfiguration()->setSQLLogger(null);
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
        gc_enable();

        $this->syncTaxonTree($output);
    }

    private function syncTaxonTree(OutputInterface $output): void
    {
        $output->writeln('<info>Sync taxon tree</info>');

        foreach ($this->taxonRepository->findRootNodes() as $rootTaxon) {
            if (!$rootTaxon instanceof TaxonInterface) {
                continue;
            }

            $this->taxonMessageProducer->synchronize($rootTaxon);
        }
    }
}
