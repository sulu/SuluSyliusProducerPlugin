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
use Sulu\SyliusProducerPlugin\Message\TriggerProductVariantProducerMessage;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Product\Repository\ProductVariantRepositoryInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class SynchronizeProductVariantsCommand extends BaseSynchronizeCommand
{
    /** @param ProductVariantRepositoryInterface<ProductVariantInterface> $productVariantRepository */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProductVariantRepositoryInterface $productVariantRepository,
        private MessageBusInterface $messageBus,
    ) {
        parent::__construct($entityManager);
    }

    protected function configure(): void
    {
        $this->setName('sulu-sylius:synchronize:product-variants')
            ->setDescription('Synchronize all product variants to Sulu');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $this->syncProductVariants($output);

        return 0;
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

        $progressBar = new ProgressBar($output, \intval($count));
        $progressBar->start();

        $processedItems = 0;
        while (false !== ($row = $iterableResult->next())) {
            $productVariant = $row[0];
            if (!$productVariant instanceof ProductVariantInterface) {
                continue;
            }

            $this->messageBus->dispatch(new TriggerProductVariantProducerMessage($productVariant->getId()));

            $this->entityManager->detach($productVariant);
            ++$processedItems;
            if (0 === $processedItems % self::BULK_SIZE) {
                $this->entityManager->clear();
                \gc_collect_cycles();
            }

            $progressBar->advance();
        }

        $progressBar->finish();
    }
}
