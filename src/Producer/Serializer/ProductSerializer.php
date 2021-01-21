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

namespace Sulu\SyliusProducerPlugin\Producer\Serializer;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Sulu\SyliusProducerPlugin\Model\CustomDataInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTranslationInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProductSerializer implements ProductSerializerInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function serialize(ProductInterface $product): array
    {
        $mainTaxon = $product->getMainTaxon();

        return [
            'id' => $product->getId(),
            'code' => $product->getCode(),
            'enabled' => $product->isEnabled(),
            'mainTaxonId' => $mainTaxon ? $mainTaxon->getId() : null,
            'productTaxons' => $this->getProductTaxons($product),
            'translations' => $this->getTranslations($product),
            'attributes' => $this->getAttributes($product),
            'images' => $this->getImages($product),
            'customData' => $this->getCustomData($product),
            'variants' => $this->getVariants($product),
        ];
    }

    protected function getAttributes(ProductInterface $product): array
    {
        $attributes = [];
        foreach ($product->getAttributes() as $attribute) {
            $attributes[] = [
                'id' => $attribute->getId(),
                'code' => $attribute->getCode(),
                'type' => $attribute->getType(),
                'localeCode' => $attribute->getLocaleCode(),
                'value' => $attribute->getValue(),
                'customData' => $this->getCustomData($attribute),
            ];
        }

        return $attributes;
    }

    protected function getProductTaxons(ProductInterface $product): array
    {
        $productTaxons = [];
        foreach ($product->getProductTaxons() as $productTaxon) {
            $taxon = $productTaxon->getTaxon();
            if (!$taxon) {
                continue;
            }

            $productTaxons[] = [
                'id' => $productTaxon->getId(),
                'taxonId' => $taxon->getId(),
                'position' => $taxon->getPosition(),
                'customData' => $this->getCustomData($productTaxon),
            ];
        }

        return $productTaxons;
    }

    protected function getTranslations(ProductInterface $product): array
    {
        $translations = [];
        /** @var ProductTranslationInterface $translation */
        foreach ($product->getTranslations() as $translation) {
            $translations[] = [
                'locale' => $translation->getLocale(),
                'name' => $translation->getName(),
                'slug' => $translation->getSlug(),
                'description' => $translation->getDescription(),
                'shortDescription' => $translation->getShortDescription(),
                'metaKeywords' => $translation->getMetaKeywords(),
                'metaDescription' => $translation->getMetaDescription(),
                'customData' => $this->getCustomData($translation),
            ];
        }

        return $translations;
    }

    protected function getImages(ProductInterface $product): array
    {
        $images = [];
        foreach ($product->getImages() as $image) {
            $filename = null;
            $file = $image->getFile();
            if ($file && $file instanceof UploadedFile) {
                $filename = $file->getClientOriginalName();
            }

            $images[] = [
                'id' => $image->getId(),
                'type' => $image->getType(),
                'path' => $image->getPath(),
                'filename' => $filename,
                'customData' => $this->getCustomData($image),
            ];
        }

        return $images;
    }

    protected function getVariants(ProductInterface $product): array
    {
        if (!$product->hasVariants()) {
            return [];
        }

        $serializationContext = new SerializationContext();
        $serializationContext->setGroups(['Default', 'Detailed', 'CustomData']);

        return json_decode(
            $this->serializer->serialize($product->getVariants()->getValues(), 'json', $serializationContext),
            true
        );
    }

    private function getCustomData(object $object): array
    {
        if (!$object instanceof CustomDataInterface) {
            return [];
        }

        return $object->getCustomData();
    }
}
