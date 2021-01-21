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
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SynchronizeProductsCommand extends BaseSynchronizeCommand
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ProductMessageProducerInterface
     */
    private $productMessageProducer;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ProductMessageProducerInterface $productMessageProducer,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($entityManager);

        $this->entityManager = $entityManager;
        $this->productMessageProducer = $productMessageProducer;
        $this->productRepository = $productRepository;
    }

    protected function configure():void
    {
        $this->setName('sulu-sylius:synchronize:products')
            ->setDescription('Synchronize all products to Sulu');
    }

    protected function execute(InputInterface $input, OutputInterface $output):int
    {
        parent::execute($input, $output);

        $this->syncProducts($output);

        return 0;
    }

    private function syncProducts(OutputInterface $output): void
    {
        $output->writeln('<info>Sync products</info>');

        $count = $this->entityManager->createQueryBuilder()
            ->select('count(product.id)')
            ->from($this->productRepository->getClassName(), 'product')
            ->getQuery()
            ->getSingleScalarResult();

        $query = $this->entityManager->createQueryBuilder()
            ->select('product')
            ->from($this->productRepository->getClassName(), 'product')
            ->getQuery();
        $iterableResult = $query->iterate();

        $progressBar = new ProgressBar($output, intval($count));
        $progressBar->start();

        $processedItems = 0;
        while (false !== ($row = $iterableResult->next())) {
            $product = $row[0];
            if (!$product instanceof ProductInterface) {
                continue;
            }

            $this->productMessageProducer->synchronize($product);

            $this->entityManager->detach($product);
            ++$processedItems;
            if (0 === $processedItems % self::BULK_SIZE) {
                $this->entityManager->clear();
                gc_collect_cycles();
            }

            $progressBar->advance();
        }

        $progressBar->finish();
    }
}
