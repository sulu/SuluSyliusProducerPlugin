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

use Sulu\Bundle\SyliusConsumerBundle\Model\Product\Message\RemoveTaxonMessage;
use Sulu\Bundle\SyliusConsumerBundle\Model\Product\Message\SynchronizeTaxonMessage;
use Sylius\Component\Core\Model\TaxonInterface;

class TaxonMessageProducer extends BaseMessageProducer implements TaxonMessageProducerInterface
{
    public function synchronize(TaxonInterface $taxon): void
    {
        $root = null;
        while (null === $root) {
            $parent = $taxon->getParent();
            if (null !== $parent) {
                $taxon = $parent;

                continue;
            }

            $root = $taxon;
        }

        if (null === $root) {
            return;
        }

        $payload = $this->serialize($root);
        $message = new SynchronizeTaxonMessage($root->getId(), $payload);
        $this->getMessageBus()->dispatch($message);
    }

    public function remove(int $id): void
    {
        $message = new RemoveTaxonMessage($id);

        $this->getMessageBus()->dispatch($message);
    }
}
