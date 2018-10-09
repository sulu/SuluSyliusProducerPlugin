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

use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTranslationInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Product\Model\ProductVariantTranslationInterface;

class ProductSerializer implements ProductSerializerInterface
{
    public function serialize(ProductInterface $product): array
    {
        $translations = [];
        /** @var ProductTranslationInterface $translation */
        foreach ($product->getTranslations() as $translation) {
            $translations[] = [
                'locale' => $translation->getLocale(),
                'name' => $translation->getName(),
            ];
        }

        $variants = [];
        /** @var ProductVariantInterface $variant */
        foreach ($product->getVariants() as $variant) {
            $variants[] = $this->serializeVariant($variant);
        }

        return [
            'code' => $product->getCode(),
            'translations' => $translations,
            'variants' => $variants,
        ];
    }

    private function serializeVariant(ProductVariantInterface $variant): array
    {
        $translations = [];
        /** @var ProductVariantTranslationInterface $translation */
        foreach ($variant->getTranslations() as $translation) {
            $translations[] = [
                'locale' => $translation->getLocale(),
                'name' => $translation->getName(),
            ];
        }

        $optionValues = [];
        foreach ($variant->getOptionValues() as $optionValue) {
            $option = $optionValue->getOption();
            if (!$option) {
                continue;
            }

            $optionValues[] = [
                'code' => $option->getCode(),
                'value' => $optionValue->getValue(),
            ];
        }

        return [
            'code' => $variant->getCode(),
            'translations' => $translations,
            'optionValues' => $optionValues,
        ];
    }
}
