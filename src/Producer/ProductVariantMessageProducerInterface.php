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

namespace Sulu\SyliusProducerPlugin\Producer;

use Sylius\Component\Core\Model\ProductVariantInterface;

interface ProductVariantMessageProducerInterface
{
    public function synchronize(ProductVariantInterface $productVariant): void;

    public function remove(ProductVariantInterface $productVariant): void;
}
