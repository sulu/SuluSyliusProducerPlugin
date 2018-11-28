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
use Sulu\SyliusProducerPlugin\Producer\ProductVariantMessageProducerInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Repository\ProductVariantRepositoryInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SynchronizeProductVariantsCommand extends BaseSynchronizeCommand
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ProductVariantMessageProducerInterface
     */
    private $productVariantMessageProducer;

    /**
     * @var ProductVariantRepositoryInterface
     */
    private $productVariantRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ProductVariantMessageProducerInterface $productVariantMessageProducer,
        ProductVariantRepositoryInterface $productVariantRepository
    ) {
        parent::__construct($entityManager);

        $this->entityManager = $entityManager;
        $this->productVariantMessageProducer = $productVariantMessageProducer;
        $this->productVariantRepository = $productVariantRepository;
    }

    protected function configure()
    {
        $this->setName('sulu-sylius:synchronize:product-variants')
            ->setDescription('Synchronize all product variants to Sulu');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $this->syncProductVariants($output);
    }

    private function syncProductVariants(OutputInterface $output): void
    {
        $output->writeln('<info>Sync product variants</info>');

        $count = $this->entityManager->createQueryBuilder()
            ->select('count(productVariant.id)')
            ->from($this->productVariantRepository->getClassName(), 'productVariant')
            ->getQuery()
            ->getSingleScalarResult();

        $query = $this->entityManager->createQueryBuilder()
            ->select('productVariant')
            ->from($this->productVariantRepository->getClassName(), 'productVariant')
            ->getQuery();
        $iterableResult = $query->iterate();

        $progressBar = new ProgressBar($output, intval($count));
        $progressBar->start();

        $processedItems = 0;
        while (($row = $iterableResult->next()) !== false) {
            $productVariant = $row[0];
            if (!$productVariant instanceof ProductVariantInterface) {
                continue;
            }

            $this->productVariantMessageProducer->synchronize($productVariant);

            $this->entityManager->detach($productVariant);
            $processedItems++;
            if ($processedItems % self::BULK_SIZE === 0) {
                $this->entityManager->clear();
                gc_collect_cycles();
            }

            $progressBar->advance();
        }

        $progressBar->finish();
    }
}
