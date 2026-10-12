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

namespace Sulu\SyliusProducerPlugin\Doctrine;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Sulu\SyliusProducerPlugin\Producer\ProductMessageProducerInterface;
use Sulu\SyliusProducerPlugin\Producer\ProductVariantMessageProducerInterface;
use Sulu\SyliusProducerPlugin\Producer\TaxonMessageProducerInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;
use Sylius\Component\Product\Model\ProductTranslationInterface;
use Sylius\Component\Product\Model\ProductVariantTranslationInterface;
use Sylius\Component\Core\Model\ProductImageInterface;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Taxonomy\Model\TaxonTranslationInterface;

/**
 * Sends synchronization messages for every change of products, variants and taxons that
 * reaches the database, whatever its source: the admin, the API, imports or custom code.
 *
 * The messages leave in postFlush, so after the transaction of the flush is committed.
 * Several changes of one flush to the same entity result in one message.
 */
class ProductChangeListener
{
    /**
     * @var array<int, ProductInterface>
     */
    private array $products = [];

    /**
     * @var array<int, ProductVariantInterface>
     */
    private array $variants = [];

    /**
     * @var array<int, TaxonInterface>
     */
    private array $taxons = [];

    /**
     * @var array<int, ProductInterface>
     */
    private array $removedProducts = [];

    /**
     * @var array<int, ProductVariantInterface>
     */
    private array $removedVariants = [];

    /**
     * @var array<int, int>
     */
    private array $removedTaxonIds = [];

    public function __construct(
        private readonly ProductMessageProducerInterface $productProducer,
        private readonly ProductVariantMessageProducerInterface $variantProducer,
        private readonly TaxonMessageProducerInterface $taxonProducer,
    ) {
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $unitOfWork = $args->getObjectManager()->getUnitOfWork();

        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            $this->collect($entity, false);
        }

        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            $this->collect($entity, false);
        }

        foreach ($unitOfWork->getScheduledEntityDeletions() as $entity) {
            $this->collect($entity, true);
        }

        $collections = \array_merge(
            $unitOfWork->getScheduledCollectionUpdates(),
            $unitOfWork->getScheduledCollectionDeletions(),
        );
        foreach ($collections as $collection) {
            $owner = $collection->getOwner();
            if (null !== $owner) {
                $this->collect($owner, false);
            }
        }
    }

    public function postFlush(): void
    {
        $products = \array_diff_key($this->products, $this->removedProducts);
        $variants = \array_diff_key($this->variants, $this->removedVariants);
        $taxons = $this->taxons;
        $removedProducts = $this->removedProducts;
        $removedVariants = $this->removedVariants;
        $removedTaxonIds = $this->removedTaxonIds;
        $this->onClear();

        foreach ($products as $product) {
            $this->productProducer->synchronize($product);
        }

        foreach ($variants as $variant) {
            $this->variantProducer->synchronize($variant);
        }

        foreach ($taxons as $taxon) {
            $this->taxonProducer->synchronize([$taxon]);
        }

        foreach ($removedVariants as $variant) {
            $this->variantProducer->remove($variant);
        }

        foreach ($removedProducts as $product) {
            $this->productProducer->remove($product);
        }

        foreach ($removedTaxonIds as $taxonId) {
            $this->taxonProducer->remove($taxonId);
        }
    }

    public function onClear(): void
    {
        $this->products = [];
        $this->variants = [];
        $this->taxons = [];
        $this->removedProducts = [];
        $this->removedVariants = [];
        $this->removedTaxonIds = [];
    }

    private function collect(object $entity, bool $deleted): void
    {
        if ($entity instanceof ProductInterface) {
            if ($deleted) {
                $this->removedProducts[\spl_object_id($entity)] = $entity;
            } else {
                $this->addProduct($entity);
            }
        } elseif ($entity instanceof ProductVariantInterface) {
            if ($deleted) {
                $this->removedVariants[\spl_object_id($entity)] = $entity;
            } else {
                $this->variants[\spl_object_id($entity)] = $entity;
            }
            $this->addProduct($entity->getProduct());
        } elseif ($entity instanceof ProductTranslationInterface) {
            $this->addProduct($entity->getTranslatable());
        } elseif ($entity instanceof ProductVariantTranslationInterface) {
            $variant = $entity->getTranslatable();
            if ($variant instanceof ProductVariantInterface) {
                $this->variants[\spl_object_id($variant)] = $variant;
            }
        } elseif ($entity instanceof ProductImageInterface) {
            $this->addProduct($entity->getOwner());
        } elseif ($entity instanceof ProductTaxonInterface) {
            $this->addProduct($entity->getProduct());
        } elseif ($entity instanceof ProductAttributeValueInterface) {
            $this->addProduct($entity->getProduct());
        } elseif ($entity instanceof TaxonInterface) {
            $id = $entity->getId();
            if ($deleted && null !== $id) {
                $this->removedTaxonIds[\spl_object_id($entity)] = $id;
            } elseif (!$deleted) {
                $this->taxons[\spl_object_id($entity)] = $entity;
            }
        } elseif ($entity instanceof TaxonTranslationInterface) {
            $taxon = $entity->getTranslatable();
            if ($taxon instanceof TaxonInterface) {
                $this->taxons[\spl_object_id($taxon)] = $taxon;
            }
        }
    }

    private function addProduct(?object $product): void
    {
        if ($product instanceof ProductInterface) {
            $this->products[\spl_object_id($product)] = $product;
        }
    }
}
